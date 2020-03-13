<?php

namespace App\Jobs;

use App\Exceptions\TaskManualFailException;
use App\Http\Controllers\Api\NormalizeUrls;
use Illuminate\Validation\ValidationException;

class ScanResolveCanonical extends TaskJob
{
    use NormalizeUrls;

    protected function handleTask()
    {
        try {
            $this->data['destinationUrl'] = $this->getDestinationUrl($this->task->scan->url);
        } catch (ValidationException $exception) {
            throw new TaskManualFailException($exception->getMessage());
        }

        $this->data['normalizedUrl'] = $this->getNormalizedUrl($this->data['destinationUrl']);
    }
}
