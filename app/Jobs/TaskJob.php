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
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

abstract class TaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Task $task
     */
    protected $task;

    protected $data = [];

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
     * @throws \Exception
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

        try {
            $response = $client->request($method, $url);

            $request->response_headers = $response->getHeaders();
            $request->response_status_code = $response->getStatusCode();
            $request->response_reason_phrase = $response->getReasonPhrase();

            $content = $response->getBody()->getContents();

            $request->response_body_size = strlen($content);

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

            $response->getBody()->rewind(); // So the full response can be read again in the task
        } catch (Exception $exception) {
            $request->exception = [
                'time' => round((microtime(true) - $requestTime) * 1000),
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
            ];

            throw $exception;
        } finally {
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

    public function failed(Exception $exception)
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
