<?php

namespace App\Resources;

use App\ExtensionVersion;
use App\JavascriptModule;
use App\Report\RatingAgent;
use App\Report\ReportFormatter;
use Composer\Semver\Comparator;
use Illuminate\Http\Resources\Json\Resource;

class ScanResource extends Resource
{
    public function toArray($request)
    {
        $report = new ReportFormatter($this->resource->report);

        $agent = new RatingAgent($this->resource);
        $agent->rate();

        return [
            'type' => 'scans',
            'id' => $this->resource->uid,
            'attributes' => [
                'hidden' => $this->resource->hidden,
                'report' => $report->toArray(),
                'scanned_at' => optional($this->resource->scanned_at)->toW3cString(),
                'rating' => $agent->rating,
                'rating_rules' => $agent->importantRules,
            ],
            'relationships' => [
                'website' => [
                    'data' => new WebsiteResource($this->resource->website),
                ],
                'extensions' => [
                    'data' => $this->extensions($request),
                ],
            ],
        ];
    }

    protected function extensions($request): array
    {
        if (is_null($this->resource->report)) {
            return [];
        }

        $stacks = array_get($this->resource->report, 'javascript_modules');

        if (!is_array($stacks)) {
            return [];
        }

        $extensionsAfterMainModuleCheck = [];

        foreach ($stacks as $stack => $modules) {
            if (!is_array($modules)) {
                continue;
            }

            foreach ($modules as $module => $checksum) {
                if (!ends_with($module, '/main')) {
                    continue;
                }

                /**
                 * @var $moduleModel JavascriptModule
                 */
                $moduleModel = JavascriptModule::query()
                    ->where('stack', $stack)
                    ->where('module', $module)
                    ->first();

                if (!$moduleModel) {
                    continue;
                }

                $versionModels = $moduleModel->extensionVersions()
                    ->where('hidden', false)
                    ->wherePivot('checksum', $checksum)
                    ->get();

                foreach ($versionModels->groupBy('extension_id') as $extensionId => $possibleVersions) {
                    if (!array_has($extensionsAfterMainModuleCheck, $extensionId)) {
                        $extension = $possibleVersions->first()->extension;

                        if ($extension->hidden) {
                            continue;
                        }

                        $extensionsAfterMainModuleCheck[$extensionId] = [
                            'extension' => $extension,
                            'versions' => [],
                        ];
                    }

                    foreach ($possibleVersions as $possibleVersion) {
                        // Use ID as key to remove duplicates when inserting
                        $extensionsAfterMainModuleCheck[$extensionId]['versions'][$possibleVersion->id] = $possibleVersion;
                    }
                }
            }
        }

        $extensionsAfterAllModulesCheck = collect($extensionsAfterMainModuleCheck)->map(function (array $extensionData) use ($stacks): array {
            return [
                'extension' => array_get($extensionData, 'extension'),
                'versions' => collect(array_get($extensionData, 'versions'))->filter(function (ExtensionVersion $version) use ($stacks): bool {
                    foreach ($version->modules as $shouldFindModule) {
                        // If the stack does not exist, this probably means one of forum or admin wasn't scanned
                        // We simply skip the test in this case
                        if (!is_array($stacks[$shouldFindModule->stack])) {
                            continue;
                        }

                        if (
                            !array_key_exists($shouldFindModule->module, $stacks[$shouldFindModule->stack]) ||
                            $stacks[$shouldFindModule->stack][$shouldFindModule->module] !== $shouldFindModule->pivot->checksum
                        ) {
                            // If the module doesn't match the data in the scan, stop checking it
                            return false;
                        }
                    }

                    return true;
                })
            ];
        })->filter(function (array $extensionData): bool {
            return count(array_get($extensionData, 'versions')) > 0;
        })->sortBy('extension.package');

        return $extensionsAfterAllModulesCheck->map(function (array $extensionData) use ($request): ExtensionResource {
            $extension = array_get($extensionData, 'extension');

            $extension->possibleVersions = collect(array_get($extensionData, 'versions'))->sort(function (ExtensionVersion $a, ExtensionVersion $b): int {
                if (Comparator::equalTo($a->version, $b->version)) {
                    return 0;
                }

                return Comparator::greaterThan($a->version, $b->version) ? 1 : -1;
            })->values();

            return new ExtensionResource($extension);
        })->values()->toArray();
    }
}
