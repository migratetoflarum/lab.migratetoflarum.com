<?php

namespace App;

use Illuminate\Contracts\Support\Arrayable;

class ReportFormatter implements Arrayable
{
    protected $report;

    public function __construct(array $report = null)
    {
        $this->report = $report;
    }

    public function toArray(): array
    {
        if (is_null($this->report)) {
            return [];
        }

        if (array_has($this->report, 'failed')) {
            return array_only($this->report, 'failed');
        }

        return $this->report;
    }
}
