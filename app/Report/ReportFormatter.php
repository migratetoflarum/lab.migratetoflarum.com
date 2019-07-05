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
            return array_only($this->report, [
                'failed',
                'requests',
            ]);
        }

        // We know Discuss will always run dev-master
        if (array_get($this->report, 'base_address') === 'discuss.flarum.org/' && array_has($this->report, 'homepage.versions')) {
            $this->report['homepage']['versions'] = ['dev-master'];
        }

        return $this->report + [
                'extension_ids' => $this->flarumExtensionIds(),
            ];
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

        $beta8Stacks = array_get($this->report, 'javascript_extensions');

        if (is_array($beta8Stacks)) {
            $beta8ExtensionsIds = [];

            foreach ($beta8Stacks as $stack => $extensions) {
                if (is_array($extensions)) {
                    $beta8ExtensionsIds = array_merge($beta8ExtensionsIds, array_keys($extensions));
                }
            }

            if (count($beta8ExtensionsIds)) {
                return array_values(array_unique($beta8ExtensionsIds));
            }
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
