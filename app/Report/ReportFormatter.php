<?php

namespace App\Report;

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

    /**
     * Get the list of extension ids from the modules in the report
     * @return array
     */
    public function flarumExtensionIds(): array
    {
        if (is_null($this->report)) {
            return [];
        }

        $modules = array_get($this->report, 'homepage.modules');

        if (!is_array($modules)) {
            return [];
        }

        $ids = [];

        foreach ($modules as $module) {
            $parts = explode('/', $module);

            if (count($parts) >= 2) {
                $ids[] = $parts[0] . '-' . $parts[1];
            }
        }

        return $ids;
    }
}
