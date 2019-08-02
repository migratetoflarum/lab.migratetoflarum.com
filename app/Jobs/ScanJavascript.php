<?php

namespace App\Jobs;

use App\Beta8JavascriptFileParser;
use App\FlarumVersion;
use Exception;

class ScanJavascript extends TaskJob
{
    protected function handleTask()
    {
        $canonical = $this->siblingTask(ScanResolveCanonical::class);

        $forumJsHash = null;
        $adminJsHash = null;

        try {
            $revManifest = \GuzzleHttp\json_decode($this->doRequest("$safeFlarumUrl/assets/rev-manifest.json")->getBody()->getContents(), true);

            $manifestForumJsHash = array_get($revManifest, 'forum.js');
            $manifestAdminJsHash = array_get($revManifest, 'admin.js');

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
            // TODO: json decode error
            $this->log(self::LOG_PUBLIC, 'Could not decode manifest');
        }

        foreach ([
                     'forum' => $forumJsHash,
                     'admin' => $adminJsHash,
                 ] as $stack => $hash) {
            try {
                if (!$hash) {
                    continue;
                }

                $content = $this->request("$safeFlarumUrl/assets/$stack-$hash.js")->getBody()->getContents();

                if (FlarumVersion::isBeta8OrAbove($flarumVersions)) {
                    $javascriptParser = new Beta8JavascriptFileParser($content);

                    $javascriptExtensions[$stack] = [];

                    foreach ($javascriptParser->extensions() as $extension) {
                        $javascriptExtensions[$stack][array_get($extension, 'id')] = md5(array_get($extension, 'code'));
                    }
                }
            } catch (Exception $exception) {
                // silence errors
            }
        }
    }
}
