<?php

namespace App\Jobs;

use App\Exceptions\TaskManualFailException;
use App\FlarumVersion;

class ScanGuessVersion extends TaskJob
{
    public function handleTask()
    {
        $homeTask = $this->siblingTask(ScanHomePage::class);

        $versions = $homeTask->getData('versions', []);

        try {
            $javascriptTask = $this->siblingTask(ScanJavascript::class);

            $checksums = $javascriptTask->getData('coreChecksums', []);

            // If we failed to find the checksum in a frontend, the key will be missing and won't play a role in the intersect
            foreach ($checksums as $frontend => $checksum) {
                $jsVersions = FlarumVersion::versionsFromJavascriptHash($frontend, $checksum);

                // If the hash doesn't match a known Flarum version, we will ignore
                // Custom builds or bundle optimizations can make that happen
                if (!count($jsVersions)) {
                    continue;
                }

                $versions = array_intersect($versions, $jsVersions);
            }
        } catch (TaskManualFailException $exception) {
            // Ignore. The task does not need to complete
        }

        $this->data['versions'] = $versions;
    }
}
