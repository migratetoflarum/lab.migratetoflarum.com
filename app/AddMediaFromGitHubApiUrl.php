<?php

namespace App;

use GuzzleHttp\Client;
use Spatie\MediaLibrary\FileAdder\FileAdderFactory;

trait AddMediaFromGitHubApiUrl
{
    /**
     * Based on HasMediaTrait::addMediaFromUrl
     * @param string $url
     * @param array ...$allowedMimeTypes
     * @return mixed
     */
    public function addMediaFromGitHubApiUrl(string $url, ...$allowedMimeTypes)
    {
        // It is necessary to use Guzzle as GitHub requires a User Agent, which is not the case with fopen()
        $client = new Client([
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => 'token ' . config('services.github.api_access_token'),
            ],
        ]);

        $response = $client->get($url);

        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');
        file_put_contents($temporaryFile, $response->getBody()->getContents());

        $this->guardAgainstInvalidMimeType($temporaryFile, $allowedMimeTypes);

        $filename = basename(parse_url($url, PHP_URL_PATH));

        if ($filename === '') {
            $filename = 'file';
        }

        $mediaExtension = explode('/', mime_content_type($temporaryFile));

        if (! str_contains($filename, '.')) {
            $filename = "{$filename}.{$mediaExtension[1]}";
        }

        return app(FileAdderFactory::class)
            ->create($this, $temporaryFile)
            ->usingName(pathinfo($filename, PATHINFO_FILENAME))
            ->usingFileName($filename);
    }
}
