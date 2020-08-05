<?php

namespace App\Report;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Used to format old reports
 * @deprecated
 */
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

        if (Arr::has($this->report, 'failed')) {
            return Arr::only($this->report, [
                'failed',
                'requests',
            ]);
        }

        // We know Discuss will always run dev-master
        if (Arr::get($this->report, 'base_address') === 'discuss.flarum.org/' && Arr::has($this->report, 'homepage.versions')) {
            $this->report['homepage']['versions'] = ['dev-master'];
        }

        return $this->report;
    }
}
