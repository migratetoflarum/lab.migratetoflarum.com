<?php

namespace App;

class Beta8JavascriptFileParser
{
    protected $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function extensions(): array
    {
        // A module import part is always added before and after each extension import
        // in https://github.com/flarum/core/blob/master/src/Frontend/Asset/ExtensionAssets.php
        if (preg_match_all('~var\s+module={};\s*(module\.exports[\s\S]+?;)\s*flarum\.extensions\[[\'"]([^\'"]+)[\'"]\]\s*=\s*module\.exports;~m', $this->content, $matches, PREG_SET_ORDER) <= 0) {
            return [];
        }

        return array_map(function (array $match) {
            return [
                'id' => $match[2],
                'code' => $match[1],
            ];
        }, $matches);
    }
}
