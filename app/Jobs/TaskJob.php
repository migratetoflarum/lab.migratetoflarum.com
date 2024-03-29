<?php

namespace App\Jobs;

use App\Events\RequestUpdated;
use App\Events\TaskLog;
use App\Events\TaskUpdated;
use App\Exceptions\InvalidEncodingException;
use App\Exceptions\TaskManualFailException;
use App\Request;
use App\ScannerClient;
use App\Task;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Psr7\InflateStream;
use GuzzleHttp\Psr7\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Str;

abstract class TaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Task $task;

    protected array $data = [];

    const LOG_PUBLIC = 'public';
    const LOG_PRIVATE = 'private';

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    protected function log(string $visibility, string $message)
    {
        $time = now()->toIso8601String();
        $line = "\n[" . $time . '] ' . $message;

        $this->task->private_log .= $line;

        if ($visibility === self::LOG_PUBLIC) {
            $this->task->public_log .= $line;
        }

        $this->task->save();

        // Only broadcast once saved so we are sure that all messages the user sees are recorded
        if ($visibility === self::LOG_PUBLIC) {
            event(new TaskLog($this->task, $time, $message));
        }
    }

    /**
     * @param string $url
     * @param string $method
     * @param bool $sensitive
     * @return \Psr\Http\Message\ResponseInterface|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws InvalidEncodingException
     * @throws Exception
     */
    protected function request(string $url, string $method = 'GET', $sensitive = false)
    {
        // Because we don't store sensitive requests, we can't re-use an existing response
        if (!$sensitive) {
            /**
             * @var $existing Request
             */
            $existing = Request::query()
                ->where('scan_id', $this->task->scan_id)
                ->where('url', $url)
                ->where('method', $method)
                ->whereNull('exception')// For now, don't re-use a failed request. Perform it again to get the error
                ->where('response_body_truncated', false)// Only re-use a request if the full body was saved
                ->first();

            if ($existing) {
                return new Response(
                    $existing->response_status_code,
                    $existing->response_headers,
                    $existing->response_body
                );
            }
        }

        $request = new Request();
        $request->scan_id = $this->task->scan_id;
        $request->url = $url;
        $request->method = $method;
        $request->fetched_at = now();

        /**
         * @var $client ScannerClient
         */
        $client = app(ScannerClient::class);
        $request->request_headers = $client->getConfig('headers');

        $requestTime = microtime(true);

        $response = null;

        $debug = fopen('php://memory', 'rw+');

        try {
            $response = $client->request($method, $url, [
                'decode_content' => false,
                // The curl option is not officially listed in the documentation but it seems to be used by Guzzle itself for some situations
                'curl' => [
                    CURLOPT_CERTINFO => true,
                ],
                // Debug enables CURL verbose output required by CERTINFO option and also gives us a way to read the output
                'debug' => $debug,
            ]);

            $request->response_headers = $response->getHeaders();
            $request->response_status_code = $response->getStatusCode();
            $request->response_reason_phrase = $response->getReasonPhrase();

            $content = $response->getBody()->getContents();

            $response->getBody()->rewind(); // So the full response can be read again in the task

            $encoding = strtolower($response->getHeaderLine('Content-Encoding'));

            // We manually decode, otherwise there is no way of knowing the raw body size
            if ($encoding === 'gzip' && $content) {
                $request->response_body_compressed_size = mb_strlen($content, '8bit');

                $content = gzdecode($content);

                if ($content === false) {
                    throw new \Exception('Could not uncompress encoded response');
                }

                // Do what Guzzle would have done without decode_content=false so that the body can be read from tasks
                $response = $response->withBody(new InflateStream($response->getBody()))
                    // Include compressed size so tasks can get access to it
                    ->withAddedHeader('X-Body-Compressed-Size', $request->response_body_compressed_size);
            }

            $request->response_body_size = mb_strlen($content, '8bit');

            // Only store content if the request was non-sensitive
            if (!$sensitive) {
                $maxSize = config('scanner.keep_max_response_body_size');

                if ($request->response_body_size > $maxSize) {
                    $content = mb_substr($content, 0, $maxSize);
                    $request->response_body_truncated = true;
                } else {
                    $request->response_body_truncated = false;
                }

                // We need to throw an error before saving to the database, otherwise a mess happens
                // With both the original payload being impossible to write to DB, the error being too big for websocket
                // And the error message itself containing an excerpt of the incorrectly encoded data
                if (!mb_check_encoding($content)) {
                    throw new InvalidEncodingException("$url contains incorrectly encoded data");
                }

                $request->response_body = $content;
            }
        } catch (Exception $exception) {
            /**
             * Necessary to make phpStorm realize InvalidEncodingException might be thrown
             * @var $exception Exception|InvalidEncodingException
             */
            $request->exception = [
                'time' => round((microtime(true) - $requestTime) * 1000),
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
            ];

            throw $exception;
        } finally {
            rewind($debug);

            $meta = [];
            $readingCertificate = false;

            while ($line = fgets($debug)) {
                if ($readingCertificate) {
                    if (!Str::startsWith($line, '*  ')) {
                        $readingCertificate = false;
                        continue;
                    }

                    $parts = explode(':', $line, 2);

                    if (count($parts) !== 2) {
                        continue;
                    }

                    // Remove starting space and ending newline
                    $secondPart = trim($parts[1]);

                    switch (substr($parts[0], 3)) {
                        case 'subject':
                            $meta['subject'] = $secondPart;
                            break;
                        case 'start date':
                            try {
                                $meta['startDate'] = Carbon::parse($secondPart)->toIso8601String();
                            } catch (\Exception $exception) {
                                // We don't want this to break the scan, but still want to report the error
                                report($exception);
                            }
                            break;
                        case 'expire date':
                            try {
                                $meta['expireDate'] = Carbon::parse($secondPart)->toIso8601String();
                            } catch (\Exception $exception) {
                                // We don't want this to break the scan, but still want to report the error
                                report($exception);
                            }
                            break;
                        case 'subjectAltName':
                            $meta['subjectAltName'] = $secondPart;
                            break;
                        case 'issuer':
                            $meta['issuer'] = $secondPart;
                            break;
                    }

                    continue;
                }

                if ($line === "* Server certificate:\n") {
                    $readingCertificate = true;
                    continue;
                }

                if (Str::startsWith($line, '* SSL connection using')) {
                    // Remove start of line until space, and ending newline
                    $meta['sslVersion'] = substr($line, 23, -1);
                    continue;
                }

                if (preg_match('~^\* Connected to .+ \(([^(]+)\) port [0-9]+ \(#[0-9]+\)\s*$~', $line, $matches) === 1) {
                    $request->ip = $matches[1];
                }
            }

            fclose($debug);

            if (count($meta)) {
                $request->certificate = $meta;
            }

            $request->duration = round((microtime(true) - $requestTime) * 1000);
            $request->save();

            event(new RequestUpdated($request));
        }

        return $response;
    }

    protected function saveTaskAndBroadCast()
    {
        if ($this->data && json_encode($this->data) !== json_encode($this->task->data)) {
            $this->task->data = $this->data;
        }

        if ($this->task->isDirty()) {
            $this->task->save();
            event(new TaskUpdated($this->task));
        }
    }

    /**
     * @param string $classname
     * @return Task
     * @throws TaskManualFailException
     */
    protected function siblingTask(string $classname): Task
    {
        if (!$this->task->scan_id) {
            throw new TaskManualFailException("Task depends on $classname, but is not a scan task");
        }

        /**
         * @var Task $sibling
         */
        $sibling = Task::query()
            ->where('scan_id', $this->task->scan_id)
            ->where('job', $classname)
            ->first();

        if (is_null($sibling)) {
            throw new TaskManualFailException("Task depends on $classname, which does not exist");
        }

        if (!$sibling->completed_at) {
            throw new TaskManualFailException("Task depends on $classname, which did not complete");
        }

        return $sibling;
    }

    /**
     * @throws TaskManualFailException
     * @throws InvalidEncodingException
     */
    abstract protected function handleTask();

    public function handle()
    {
        $this->task->started_at = now();
        $this->saveTaskAndBroadcast();

        try {
            $this->handleTask();

            $this->task->completed_at = now();
        } catch (TaskManualFailException $exception) {
            // This won't be seen as a job failure in the queue, allowing the next jobs to run
            $this->task->failed_at = now();
            $this->log(self::LOG_PUBLIC, $exception->getMessage());
        } catch (InvalidEncodingException $exception) {
            $this->task->failed_at = now();
            $this->log(self::LOG_PUBLIC, 'Task aborted due to invalid encoding: ' . $exception->getMessage());
        }

        $this->saveTaskAndBroadcast();

        $manager = new ScanTaskManager($this->task->scan);
        $manager->next();

        if ($manager->dispatched) {
            $this->log(self::LOG_PRIVATE, 'Dispatched jobs ' . implode(', ', $manager->dispatched));
        } else {
            $this->log(self::LOG_PRIVATE, 'Dispatched no other jobs');
        }

    }

    public function failed(\Throwable $exception)
    {
        // Not designed for jobs retries for now
        $this->task->failed_at = now();

        // Will also save the attributes from above
        $this->log(self::LOG_PRIVATE, $exception->getMessage() . "\n" . $exception->getTraceAsString());

        event(new TaskUpdated($this->task));

        $manager = new ScanTaskManager($this->task->scan);
        $manager->next();
    }
}
