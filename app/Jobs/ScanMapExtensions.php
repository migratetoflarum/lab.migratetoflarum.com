<?php

namespace App\Jobs;

use App\Extension;
use Illuminate\Database\Eloquent\Collection;

class ScanMapExtensions extends TaskJob
{
    protected function handleTask()
    {
        $javascript = $this->siblingTask(ScanJavascript::class);

        $extensionIds = [];

        foreach ($javascript->getData('javascriptExtensions', []) as $stack => $extensions) {
            $extensionIds = array_merge($extensionIds, array_keys($extensions));
        }

        /**
         * @var $matchingExtensions Collection
         */
        $matchingExtensions = Extension::whereIn('flarumid', $extensionIds)->orderBy('package')->get();

        $extensionsLikelyEnabled = $matchingExtensions->filter(function (Extension $extension) use ($matchingExtensions): bool {
            // Keep any extension that isn't abandoned
            if (!$extension->abandoned) {
                return true;
            }

            // If the extension is abandoned but another one matches the flarum id,
            // remove this extension. Occurs when an extension was renamed and therefore there are multiple matches
            // We assume the forum is already using the non-abandoned version
            $duplicateNotAbandoned = $matchingExtensions->first(function (Extension $duplicate) use ($extension) {
                return !$duplicate->abandoned && $duplicate->flarumid === $extension->flarumid;
            });

            return is_null($duplicateNotAbandoned);
        })->values();

        $this->task->scan->extensions()->sync($extensionsLikelyEnabled);
    }
}
