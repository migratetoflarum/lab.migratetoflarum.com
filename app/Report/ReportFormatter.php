<?php

namespace App\Report;

use Illuminate\Contracts\Support\Arrayable;

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

        if (array_has($this->report, 'failed')) {
            return array_only($this->report, [
                'failed',
                'requests',
            ]);
        }

        // We know Discuss will always run dev-master
        if (array_get($this->report, 'base_address') === 'discuss.flarum.org/' && array_has($this->report, 'homepage.versions')) {
            $this->report['homepage']['versions'] = ['dev-master'];
        }

        return $this->report;
    }
}
