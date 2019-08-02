<?php

namespace App\Jobs;

use App\Events\TaskLog;
use App\Events\TaskUpdated;
use App\Exceptions\TaskManualFailException;
use App\Request;
use App\ScannerClient;
use App\Task;
use Exception;
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
     * @return mixed|\Psr\Http\Message\ResponseInterface|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    protected function request(string $url, string $method = 'GET', $sensitive = false)
    {
        // TODO: re-use existing response

        $request = new Request();
        $request->task()->associate($this->task);
        $request->sensitive = $sensitive;
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

            if (!$sensitive) {
                $content = $response->getBody()->getContents();

                $bodySize = strlen($content);
                $maxSize = config('scanner.keep_max_response_body_size');

                if ($bodySize > $maxSize) {
                    $content = substr($content, 0, $maxSize) . "\n\n(response truncated. Original length $bodySize)";
                }

                $request->response_body = $content;

                $response->getBody()->rewind(); // So the full response can be read again in the task
            }
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
        }

        return $response;
    }

    protected function saveTaskAndBroadCast()
    {
        if ($this->data) {
            // TODO: way to only update if dirty
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
            ->firstOrFail();

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
            $this->task->data = $this->data;
            $this->task->fail_message = $exception->getMessage();
            $this->task->failed_at = now();
            // TODO: log exception ?
        }

        $this->saveTaskAndBroadcast();
    }

    public function failed(Exception $exception)
    {
        // TODO: what about new attempts
        $this->task->failed_at = now();

        // Will also save the attributes from above
        $this->log(self::LOG_PRIVATE, $exception->getTraceAsString());

        event(new TaskUpdated($this->task));
    }
}
