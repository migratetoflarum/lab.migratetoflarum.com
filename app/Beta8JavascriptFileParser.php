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
        // in https://github.com/flarum/core/blob/master/src/Extend/Frontend.php
        if (preg_match_all('~var\s+module={};\s*(module\.exports[\s\S]+?;)\s*flarum\.extensions\[[\'"]([^\'"]+)[\'"]\]\s*=\s*module\.exports;~m', $this->content, $matches, PREG_SET_ORDER) <= 0) {
            return [];
        }

        return array_map(function (array $match) {
            $content = $match[1];
            // If the code ends with two ;; following each other, remove one
            // That second ; is added by the Flarum JsCompiler and is not part of the extension source code
            // The origin of that ; is being investigated in https://github.com/flarum/core/issues/2120
            $content = preg_replace('~;\s*;$~', ';', $content);
            $content = trim($content);

            return [
                'id' => $match[2],
                'checksum' => md5($content),
            ];
        }, $matches);
    }
}
