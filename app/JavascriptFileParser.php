<?php

namespace App;

use MatthiasMullie\Minify\JS;

class JavascriptFileParser
{
    protected $content;

    public function __construct(string $content)
    {
        $this->content = (new JS($content))->execute();
    }

    public function modules(): array
    {
        // Only if not minified
        // ~(?!\n)System\.register\(['"]([^'"]+)['"][\s\S]+?\n}\);~m
        // A System.register call is usually followed by 'use script' before another System.register
        // (function(root, factory){}) is the start of an amd loader declaration
        // (function(document, window is the start of a jquery plugin
        // or followed by the end of the file
        if (preg_match_all('~(System\.register\([\'"]([^\'"]+)[\'"][\s\S]+?}\));?\n?(?:(?:[\'"]use strict[\'"])|(?=System\.register)|(?:\(function\(root,factory)|(?:\(function\(document,window)|(?:\w*\Z))~m', $this->content, $matches, PREG_SET_ORDER) <= 0) {
            return [];
        }

        return array_map(function (array $match) {
            return [
                'module' => $match[2],
                'code' => $match[1], // the code without the ending ; that is not always there
            ];
        }, $matches);
    }
}
