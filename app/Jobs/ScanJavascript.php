<?php

namespace App\Jobs;

use App\Beta8JavascriptFileParser;
use App\FlarumVersion;
use Exception;
use Illuminate\Support\Arr;

class ScanJavascript extends TaskJob
{
    protected function handleTask()
    {
        $homepage = $this->siblingTask(ScanHomePage::class);

        $safeFlarumUrl = $homepage->getData('safeFlarumUrl');

        $forumJsHash = null;
        $adminJsHash = null;

        try {
            $revManifest = \GuzzleHttp\json_decode($this->request("$safeFlarumUrl/assets/rev-manifest.json")->getBody()->getContents(), true);

            $manifestForumJsHash = Arr::get($revManifest, 'forum.js');
            $manifestAdminJsHash = Arr::get($revManifest, 'admin.js');

            if (preg_match('~^[0-9a-f]{8}$~', $manifestForumJsHash) === 1) {
                if ($forumJsHash && $forumJsHash !== $manifestForumJsHash) {
                    $this->log(self::LOG_PUBLIC, 'forum.js hash from homepage (' . $forumJsHash . ') is different from rev-manifest (' . $manifestForumJsHash . ')');
                }

                $forumJsHash = $manifestForumJsHash;
            }

            if (preg_match('~^[0-9a-f]{8}$~', $manifestAdminJsHash) === 1) {
                $adminJsHash = $manifestAdminJsHash;
            }
        } catch (Exception $exception) {
            $this->log(self::LOG_PUBLIC, 'Could not decode manifest: ' . $exception->getMessage());
        }

        $javascriptExtensions = [];

        foreach ([
                     'forum' => $forumJsHash,
                     'admin' => $adminJsHash,
                 ] as $stack => $hash) {
            try {
                if (!$hash) {
                    continue;
                }

                $content = $this->request("$safeFlarumUrl/assets/$stack-$hash.js")->getBody()->getContents();

                if (FlarumVersion::isBeta8OrAbove($homepage->getData('versions'))) {
                    $javascriptParser = new Beta8JavascriptFileParser($content);

                    $javascriptExtensions[$stack] = [];

                    foreach ($javascriptParser->extensions() as $extension) {
                        $javascriptExtensions[$stack][Arr::get($extension, 'id')] = md5(Arr::get($extension, 'code'));
                    }
                }
            } catch (Exception $exception) {
                $this->log(self::LOG_PUBLIC, 'Error while accessing ' . $stack . '. Skipping. ' . $exception->getMessage());
            }
        }

        $this->data['javascriptExtensions'] = $javascriptExtensions;
    }
}
