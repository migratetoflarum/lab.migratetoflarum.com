<?php

namespace App\Jobs;

use App\Beta8JavascriptFileParser;
use App\FlarumVersion;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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
        $javascriptSize = [];

        foreach ([
                     'forum' => $forumJsHash,
                     'admin' => $adminJsHash,
                 ] as $stack => $hash) {
            try {
                if (!$hash) {
                    continue;
                }

                $content = $this->request("$safeFlarumUrl/assets/$stack-$hash.js")->getBody()->getContents();

                $javascriptSize[$stack] = [
                    'total' => mb_strlen($content, '8bit'),
                    'modules' => [],
                ];

                if (FlarumVersion::isBeta8OrAbove($homepage->getData('versions'))) {
                    $javascriptParser = new Beta8JavascriptFileParser($content);

                    $javascriptExtensions[$stack] = [];
                    $sizeModules = [];
                    $knownSize = 0;

                    foreach ($javascriptParser->extensions() as $extension) {
                        $javascriptExtensions[$stack][Arr::get($extension, 'id')] = Arr::get($extension, 'checksum');
                        $sizeModules[] = Arr::only($extension, ['id', 'size', 'dev']);
                        $knownSize += Arr::get($extension, 'size');
                    }

                    $coreSize = $javascriptParser->coreSize();

                    if ($coreSize) {
                        foreach ($coreSize as $id => $size) {
                            $sizeModules[] = [
                                'id' => $id,
                                'size' => $size,
                            ];

                            $knownSize += $size;
                        }
                    }

                    $sizeModules[] = [
                        'id' => 'other',
                        'size' => $javascriptSize[$stack]['total'] - $knownSize,
                    ];

                    $javascriptSize[$stack]['modules'] = Collection::make($sizeModules)->sortByDesc('size')->values()->all();
                } else {
                    $javascriptSize[$stack]['modules'][] = [
                        'id' => 'unknown',
                        'size' => $javascriptSize[$stack]['total'],
                    ];
                }
            } catch (Exception $exception) {
                $this->log(self::LOG_PUBLIC, 'Error while accessing ' . $stack . '. Skipping. ' . $exception->getMessage());
            }
        }

        $this->data['javascriptExtensions'] = $javascriptExtensions;
        $this->data['javascriptSize'] = $javascriptSize;
    }
}
