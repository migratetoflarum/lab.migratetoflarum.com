<?php

namespace App\Console\Commands;

use App\Extension;
use App\ExtensionTranslation;
use App\ExtensionVersion;
use App\JavascriptFileParser;
use App\JavascriptModule;
use App\Locale;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

class ScanExtensions extends Command
{
    protected $signature = 'extensions:scan {--package=} {--force} {--skip-modules} {--skip-locales}';
    protected $description = 'Update the list of modules exported by each extension';

    protected $yamlParser;
    protected $localeErrors = [];

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
        }

        $skipLocales = $this->option('skip-locales') || ($version->scanned_locales_at && !$this->option('force'));

        if ($skipLocales) {
            $this->info('Skipping locales scan' . ($version->scanned_locales_at ? '(has scan date)' : '(no date scan)'));
        }

        if ($skipModules && $skipLocales) {
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
        $this->localeErrors = [];

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

            if (!$skipLocales && preg_match('~/locales?/([a-z][a-z_-]*)\.ya?ml$~', $filename, $matches) === 1) {
                $this->scanForTranslations($matches, $zip->getFromIndex($fileIndex), $version);
            }
        }

        if (!$skipModules) {
            $version->modules()->sync($syncModules);

            $version->scanned_modules_at = Carbon::now();
        }

        if (!$skipLocales) {
            $version->scanned_locales_at = Carbon::now();
            $version->locale_errors = count($this->localeErrors) ? $this->localeErrors : null;
        }

        if ($version->isDirty()) {
            $version->save();
        }
    }

    protected function addLocaleError(string $error)
    {
        $this->localeErrors[] = $error;

        $this->warn($error);
    }

    protected function scanForTranslations(array $matches, string $content, ExtensionVersion $version)
    {
        /**
         * @var $locale Locale
         */
        $locale = null;

        // If the extension is a language pack we don't use the name of the file as the locale code
        // But retrieve the language pack locale name instead
        if ($version->extension->flarum_locale_id) {
            $locale = Locale::findOrFail($version->extension->flarum_locale_id);
        } else {
            $locale = Locale::firstOrCreate([
                'code' => $matches[1],
            ]);
        }

        if (!$this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        // Based on Symfony\Component\Translation\Loader\YamlFileLoader
        try {
            $messages = $this->yamlParser->parse($content);
        } catch (ParseException $exception) {
            $this->addLocaleError('Could not parse file ' . $matches[0] . ': ' . $exception->getMessage());

            return;
        }

        // empty resource
        if ($messages === null) {
            $messages = [];
        }

        // not an array
        if (!is_array($messages)) {
            $this->addLocaleError('Could not parse file ' . $matches[0] . ': root is not an array');

            return;
        }

        foreach ($messages as $namespace => $catalogue) {
            // not an array
            if (!is_array($catalogue)) {
                $this->addLocaleError('Could not parse file ' . $matches[0] . ': namespace "' . $namespace . '"" is not an array');

                continue;
            }

            $strings = array_dot($catalogue);

            ExtensionTranslation::updateOrCreate([
                'version_id' => $version->id,
                'locale_id' => $locale->id,
                'namespace' => $namespace,
            ], [
                'namespace_extension_id' => optional(Extension::where('flarumid', $namespace)->first())->id,
                'strings_count' => count($strings),
            ]);
        }
    }
}
