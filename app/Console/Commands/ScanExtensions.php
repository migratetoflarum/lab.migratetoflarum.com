<?php

namespace App\Console\Commands;

use App\ExtensionVersion;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class ScanExtensions extends Command
{
    protected $signature = 'extensions:scan {--package=} {--force} {--skip-modules}';
    protected $description = 'Update the list of modules exported by each extension';

    public function handle()
    {
        /**
         * @var $versions Collection|ExtensionVersion[]
         */
        $query = ExtensionVersion::query();

        if ($this->option('package')) {
            $query->whereHas('extension', function (Builder $query) {
                $query->where('package', 'like', $this->option('package'));
            });
        }

        $versions = $query->get();
        $versions->load('extension');

        foreach ($versions as $version) {
            try {
                $this->readZip($version);
            } catch (Exception $exception) {
                $this->error($exception->getMessage());

                report($exception);
            }
        }
    }

    protected function readZip(ExtensionVersion $version)
    {
        $this->info('Reading version ' . $version->version . ' of package ' . $version->extension->package);

        $skipModules = $this->option('skip-modules') || ($version->scanned_modules_at && !$this->option('force'));

        if ($skipModules) {
            $this->info('Skipping modules scan ' . ($version->scanned_modules_at ? '(has scan date)' : '(no date scan)'));
            $this->info('Nothing left to scan, skipping zip');

            return;
        }

        $zipPath = $version->getFirstMediaPath('dist');

        if (!$zipPath) {
            $this->warn('No zip file, skipping');

            return;
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath);

        for ($fileIndex = 0; $fileIndex < $zip->numFiles; $fileIndex++) {
            $file = $zip->statIndex($fileIndex);
            $filename = Arr::get($file, 'name');

            if (!$skipModules && preg_match('~/js/dist/(admin|forum)\.js$~', $filename, $matches) === 1) {
                $content = $zip->getFromIndex($fileIndex);

                // Remove sourcemaps, like Flarum\Frontend\Compiler\JsCompiler::format does
                $content = preg_replace('~//# sourceMappingURL.*$~m', '', $content);

                // Remove whitespace at the start or end, we do the same before computing the checksum in the forum assets
                $content = trim($content);

                $version->{'javascript_' . $matches[1] . '_checksum'} = md5($content);
            }
        }

        if ($version->isDirty()) {
            $version->save();
        }
    }
}
