<?php

namespace App\Jobs;

use App\Extension;
use Composer\Semver\Semver;
use Illuminate\Support\Arr;

class ScanMapExtensions extends TaskJob
{
    protected function handleTask()
    {
        $javascript = $this->siblingTask(ScanJavascript::class);

        $stackedExtensions = [
            'forum' => [],
            'admin' => [],
        ];

        foreach ($javascript->getData('javascriptExtensions', []) as $stack => $extensions) {
            $stackedExtensions[$stack] = $this->extensionsAndPossibleVersionsForStack($stack, $extensions);
        }

        $extensionsAndPossibleVersions = $this->mergePossibleVersions($stackedExtensions['forum'], $stackedExtensions['admin']);

        $syncExtensions = [];

        foreach ($extensionsAndPossibleVersions as $info) {
            $extensionId = Arr::get($info, 'extension_id');
            $possibleVersions = Semver::sort(Arr::get($info, 'possible_versions'));

            $syncExtensions[$extensionId] = [
                'possible_versions' => count($possibleVersions) ? json_encode($possibleVersions) : null,
            ];
        }

        $this->task->scan->extensions()->sync($syncExtensions);
    }

    /**
     * @param string $stack forum or admin
     * @param array $extensions key-value array where key is the extension Flarum ID and value is the javascript checksum for this stack
     * @return array
     */
    protected function extensionsAndPossibleVersionsForStack(string $stack, array $extensions): array
    {
        $return = [];

        foreach ($extensions as $flarumId => $checksum) {
            // Order by abandoned so that if there is both an abandoned and non-abandoned extension with the same ID, the non-abandoned one will be returned
            // This happens in particular with Flarum core extensions whose packages were renamed while keeping the same ID
            $extension = Extension::query()
                ->where('flarumid', $flarumId)
                ->orderBy('abandoned')
                ->first();

            if (!$extension) {
                continue;
            }

            $return[] = [
                'extension_id' => $extension->id,
                'possible_versions' => $extension->versions()
                    ->where('javascript_' . $stack . '_checksum', $checksum)
                    ->pluck('version')
                    ->all(),
            ];
        }

        return $return;
    }

    protected function mergePossibleVersions(array $extensions1, array $extensions2): array
    {
        $extensionIds = array_unique(array_merge(Arr::pluck($extensions1, 'extension_id'), Arr::pluck($extensions2, 'extension_id')));

        return array_map(function ($extensionId) use ($extensions1, $extensions2) {
            $stackVersions = [];

            $extensionIn1 = Arr::first($extensions1, function ($e) use ($extensionId) {
                return Arr::get($e, 'extension_id') === $extensionId;
            });

            if ($extensionIn1) {
                $stackVersions[] = Arr::get($extensionIn1, 'possible_versions');
            }

            $extensionIn2 = Arr::first($extensions2, function ($e) use ($extensionId) {
                return Arr::get($e, 'extension_id') === $extensionId;
            });

            if ($extensionIn2) {
                $stackVersions[] = Arr::get($extensionIn2, 'possible_versions');
            }

            // If only one stack had the extension present, we will use the possible versions of that stack as the truth
            if (count($stackVersions) === 1) {
                return [
                    'extension_id' => $extensionId,
                    'possible_versions' => $stackVersions[0],
                ];
            }

            // If the extension is present in both stacks, then we will keep common possible versions between the two
            return [
                'extension_id' => $extensionId,
                'possible_versions' => array_values(array_intersect($stackVersions[0], $stackVersions[1])),
            ];
        }, $extensionIds);
    }
}
