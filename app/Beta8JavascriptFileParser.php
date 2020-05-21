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
                'size' => mb_strlen($match[0], '8bit'),
                'dev' => str_contains($content, '/******/'),
            ];
        }, $matches);
    }

    public function coreSize(): ?array
    {
        // We detect the end of the core javascript by a its known content
        // Beta 13 forum: (e,"compat",(function(){return he}))}]);
        // Beta 13 admin: (e,"compat",(function(){return ct}))}]);
        // Beta 12 forum: (e,"compat",(function(){return he}))}]);
        // Beta 12 admin: (e,"compat",(function(){return ct}))}]);
        // Beta 11 forum: (e,"compat",function(){return he})}]);
        // Beta 11 admin: (e,"compat",function(){return ct})}]);
        // Beta 10 forum: (e,"compat",function(){return he})}]);
        // Beta 10 admin: (e,"compat",function(){return ct})}]);
        // Beta 09 forum: (e,"compat",function(){return he})}]);
        // Beta 09 admin: (e,"compat",function(){return ct})}]);
        // Beta 08 forum: (e,"compat",function(){return he})}]);
        // Beta 08 admin: (e,"compat",function(){return lt})}]);
        if (preg_match('~^([\s\S]*\(e,"compat",\(?function\(\)\{return [a-z]{2}\}\)\)?\}\]\);)([\s\S]*?)var\s+module\s*=\s*\{\}~m', $this->content, $matches) !== 1) {
            return null;
        }

        $modules = [
            'core' => mb_strlen($matches[1], '8bit'),
        ];

        // On admin, there will be some space between core and the first module, but we won't consider it as textformatter
        if (strlen($matches[2]) > 10) {
            $modules['textformatter'] = mb_strlen($matches[2], '8bit');
        }

        return $modules;
    }
}
