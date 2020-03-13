<?php

namespace App\Console\Commands;

use App\ExtensionVersion;
use App\JavascriptFileParser;
use App\JavascriptModule;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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

        $syncModules = [];

        for ($fileIndex = 0; $fileIndex < $zip->numFiles; $fileIndex++) {
            $file = $zip->statIndex($fileIndex);
            $filename = array_get($file, 'name');

            if (!$skipModules && preg_match('~/(admin|forum)/dist/extension\.js$~', $filename, $matches) === 1) {
                $stack = $matches[1];

                $parser = new JavascriptFileParser($zip->getFromIndex($fileIndex));

                foreach ($parser->modules() as $module) {
                    $this->info($stack . '::' . array_get($module, 'module'));
                    $moduleModel = JavascriptModule::firstOrCreate([
                        'stack' => $stack,
                        'module' => array_get($module, 'module'),
                    ]);

                    $syncModules[$moduleModel->id] = [
                        'checksum' => md5(array_get($module, 'code')),
                    ];
                }
            }
        }

        if (!$skipModules) {
            $version->modules()->sync($syncModules);

            $version->scanned_modules_at = Carbon::now();
        }

        if ($version->isDirty()) {
            $version->save();
        }
    }
}
