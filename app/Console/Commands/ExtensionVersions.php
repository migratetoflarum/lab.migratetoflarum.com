<?php

namespace App\Console\Commands;

use App\Extension;
use App\Scan;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExtensionVersions extends Command
{
    protected $signature = 'extensions:versions {package} {--compare-scan=}';
    protected $description = 'Update the list of modules exported by each extension';

    public function handle()
    {
        /**
         * @var $extension Extension
         */
        $extension = Extension::where('package', 'like', $this->argument('package'))->firstOrFail();
        $extension->load([
            'versions.modules' => function (BelongsToMany $query) {
                $query->orderBy('stack')->orderBy('module');
            },
        ]);

        /**
         * @var $scan Scan
         */
        $scan = null;

        if ($this->option('compare-scan')) {
            $scan = Scan::where('uid', $this->option('compare-scan'))->first();
        }

        foreach ($extension->versions as $version) {
            $this->info('Version ' . $version->version);

            foreach ($version->modules as $module) {
                $matchstate = null;

                if ($scan && !is_null($scan->report)) {
                    $modules = array_get($scan->report, 'javascript_modules');

                    $stackModules = $modules[$module->stack];

                    if (is_array($stackModules) && array_has($stackModules, $module->module)) {
                        if ($stackModules[$module->module] === $module->pivot->checksum) {
                            $matchstate = 'OK';
                        } else {
                            $matchstate = 'FAIL CHECKSUM ' . $stackModules[$module->module];
                        }
                    } else {
                        $matchstate = 'NOT FOUND';
                    }
                }

                $this->info($module->stack . '::' . $module->module . ' (' . $module->pivot->checksum . ')' . ($matchstate ? ' ' . $matchstate : ''));
            }
        }
    }
}
