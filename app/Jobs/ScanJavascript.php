<?php

namespace App\Jobs;

use App\Beta8JavascriptFileParser;
use App\FlarumVersion;
use Exception;
use GuzzleHttp\Utils;
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
            $revManifest = Utils::jsonDecode($this->request("$safeFlarumUrl/assets/rev-manifest.json")->getBody()->getContents(), true);

            $manifestForumJsHash = Arr::get($revManifest, 'forum.js');
            $manifestAdminJsHash = Arr::get($revManifest, 'admin.js');

            if (preg_match('~^[0-9a-f]{8}$~', $manifestForumJsHash) === 1) {
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
        $coreChecksums = [];

        foreach ([
                     'forum' => $forumJsHash,
                     'admin' => $adminJsHash,
                 ] as $stack => $hash) {
            try {
                if (!$hash) {
                    continue;
                }

                if (FlarumVersion::isV1OrAbove($homepage->getData('versions'))) {
                    $assetUrl = "$safeFlarumUrl/assets/$stack.js?v=$hash";
                } else {
                    $assetUrl = "$safeFlarumUrl/assets/$stack-$hash.js";
                }

                $response = $this->request($assetUrl);

                $content = $response->getBody()->getContents();

                $javascriptSize[$stack] = [
                    'total' => mb_strlen($content, '8bit'),
                    'expectedGzipSize' => mb_strlen(gzencode($content), '8bit'),
                    'modules' => [],
                ];

                if ($response->getHeaderLine('Content-Encoding')) {
                    $javascriptSize[$stack]['compressed'] = true;

                    // The length of the body has been saved by request()
                    $size = $response->getHeaderLine('X-Body-Compressed-Size');

                    if ($size) {
                        $javascriptSize[$stack]['actualCompressedSize'] = intval($size);
                    }
                }

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

                    foreach ($javascriptParser->coreSize() as $coreElement) {
                        $sizeModules[] = Arr::only($coreElement, ['id', 'size']);
                        $knownSize += Arr::get($coreElement, 'size');

                        if (Arr::get($coreElement, 'id') === 'core') {
                            $coreChecksums[$stack] = Arr::get($coreElement, 'checksum');
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
        $this->data['coreChecksums'] = $coreChecksums;
    }
}
