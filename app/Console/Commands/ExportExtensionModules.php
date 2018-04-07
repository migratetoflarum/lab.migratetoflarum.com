<?php

namespace App\Console\Commands;

use App\ExtensionVersion;
use App\JavascriptFileParser;
use App\JavascriptModule;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ExportExtensionModules extends Command
{
    protected $signature = 'extensions:export-modules {--package=}';
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

            if (preg_match('~/(admin|forum)/dist/extension.js$~', array_get($file, 'name'), $matches) !== 1) {
                continue;
            }

            $stack = $matches[1];

            $content = $zip->getFromIndex($fileIndex);
            $parser = new JavascriptFileParser($content);

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

        $version->modules()->sync($syncModules);
    }
}
