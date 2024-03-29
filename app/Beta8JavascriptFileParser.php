<?php

namespace App;

use Illuminate\Support\Str;

class Beta8JavascriptFileParser
{
    protected string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function extensions(): array
    {
        // A module import part is always added before and after each extension import
        // in https://github.com/flarum/core/blob/master/src/Extend/Frontend.php
        // Since beta 14, there are a lot less semicolumns because of https://github.com/flarum/core/pull/2280
        preg_match_all('~var\s+module={};?\s*(module\.exports[\s\S]+?;)\s*flarum\.extensions\[[\'"]([^\'"]+)[\'"]]\s*=\s*module\.exports;?~m', $this->content, $matches1, PREG_SET_ORDER);

        // With webpack 5, the common format has changed, the dist file no longer starts with module.exports=[...]
        // Instead, it's an immediately invoked function (()=>{[...]module.exports=n})();
        // There is sometimes a comment before the module, like "/*! For license information please see admin.js.LICENSE.txt */"
        preg_match_all('~var\s+module={};?\s*((?:/\*![\s\S]+?\*/\s*)?\(\(\)=>\{[\s\S]+?module\.exports=.{1,2}}\)\(\);)\s*flarum\.extensions\[[\'"]([^\'"]+)[\'"]]\s*=\s*module\.exports;?~m', $this->content, $matches2, PREG_SET_ORDER);

        return array_map(function (array $match) {
            $content = $match[1];
            // If the code ends with two ;; following each other, remove one
            // That second ; is added by the Flarum JsCompiler and is not part of the extension source code
            // Linked issue https://github.com/flarum/core/issues/2120
            $content = preg_replace('~;\s*;$~', ';', $content);
            $content = trim($content);

            return [
                'id' => $match[2],
                'checksum' => md5($content),
                'size' => mb_strlen($match[0], '8bit'),
                'dev' => str_contains($content, '/******/'),
            ];
        }, array_merge($matches1, $matches2));
    }

    public function coreSize(): array
    {
        // We truncate the input with substr because otherwise it's possible to reach pcre.backtrack_limit
        // We know Flarum's largest core JS is around 360kB and we're going to be generous and allow 240kB of TextFormatter, which is unlikely
        $areaOfInterest = mb_substr($this->content, 0, 600000, '8bit');

        // We detect the end of the core javascript by its known content
        // Core and its ending sequence will be captured as group 1
        // We also know everything between core and the first module will be TextFormatter, this is capture group 2
        // 1.2.0 forum: ;ki.app=ii;var Ei=vi(ki,"forum")})(),flarum.core=r})();
        // 1.2.0 admin: ;$r.app=dr;var Rr=Pr($r,"admin")})(),flarum.core=r})();
        $preg = $this->pregMatch('~^([\s\S]*var [A-Za-z]{2}=[A-Za-z]{2}\([$A-Za-z]{2},"(?:forum|admin)"\)\}\)\(\),flarum\.core=[a-z]\}\)\(\);)([\s\S]*?)var\s+module\s*=\s*\{\}~m', $areaOfInterest, $matches);

        // Older Flarum versions are handled with separate regexes because it's becoming too difficult to maintain as a single regex
        if ($preg !== 1) {
            // 1.1.0 forum: $e=n(74);De.app=je;var Ie=Object($e.a)(De,"forum")}]);
            // 1.1.0 admin: Dt=n(74);At.app=jt;var Pt=Object(Dt.a)(At,"admin")}]);
            // 1.0.0 forum: $e=n(73);De.app=je;var Ie=Object($e.a)(De,"forum")}]);
            // 1.0.0 admin: kt=n(73);Ct.app=_t;var jt=Object(kt.a)(Ct,"admin")}]);
            // Beta 15 forum: window.app=Fe,He.app=Fe;var qe=Object(Ue.a)(He,"forum")}]);
            // Beta 15 admin: window.app=Tt,xt.app=Tt;var Nt=Object(Ot.a)(xt,"admin")}]);
            // Beta 14.1 forum: window.app=Fe,Ue.app=Fe}]);
            // Beta 14.1 admin: window.app=dt,lt.app=dt}]);
            // Beta 14 forum: window.app=Fe,Ue.app=Fe}]);
            // Beta 14 admin: window.app=dt,lt.app=dt}]);
            $preg = $this->pregMatch('~^([\s\S]*(?:(?:window\.app=[A-Za-z]{2},|[$A-Za-z]{2}=n\([0-9]{2}\);)[A-Za-z]{2}\.app=[_A-Za-z]{2})(?:;var [A-Za-z]{2}=Object\([$A-Za-z]{2}\.a\)\([A-Za-z]{2},"(?:forum|admin)"\))?\}\]\);)([\s\S]*?)var\s+module\s*=\s*\{\}~m', $areaOfInterest, $matches);
        }

        if ($preg !== 1) {
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
            $preg = $this->pregMatch('~^([\s\S]*\(e,"compat",\(?function\(\)\{return [a-z]{2}\}\)\)?\}\]\);)([\s\S]*?)var\s+module\s*=\s*\{\}~m', $areaOfInterest, $matches);
        }

        if ($preg !== 1) {
            return [];
        }

        // One common change made by proxies/CDNs is to collapse the copyright comments for Sizzle/jQuery/etc
        // We will expend them back to their original format to make the checksum test work
        // We also verify the first char before the comment to verify it has indeed been collapsed with the previous line
        $coreCode = preg_replace_callback('~(?<![\r\n])/\*![\s\S]+?\*/(?=([\s\S]|$))~', function ($commentMatches) {
            $comment = $commentMatches[0];

            // If this is a collapsed comment (no space between newline and `*`)
            // Add initial newline, and a space in front of each line from the second line
            if (Str::contains($comment, "\n*")) {
                $comment = "\n" . implode("\n ", explode("\n", $comment));

                // In the original dist file, some comments must be followed by a newline
                // We check for a list of exceptions before adding the missing newline
                if (!in_array($commentMatches[1], [
                    "\n", // Two comments are followed by a newline
                    '!', // Two comments are followed by `!` and don't go to a newline
                    '/', // Start of another comment. There's one space that will be added by the block below. Without this there would be two newlines
                    'i', // Start of a `if` (line 52 on beta 14)
                ])) {
                    $comment .= "\n";
                }
            } else if (Str::startsWith($comment, '/*!https')) {
                // punycode one-line comment also has a spaces at each end of the comment that are removed by optimizers
                $comment = preg_replace('~/\*!(\s?)(.+?)(\s?)\*/~', '/*! \\2 */', $comment);
            }

            return $comment;
        }, $matches[1]);

        $modules = [
            [
                'id' => 'core',
                'checksum' => md5($coreCode),
                'size' => mb_strlen($matches[1], '8bit'),
            ],
        ];

        // On admin, there will be some space between core and the first module, but we won't consider it as TextFormatter
        if (strlen($matches[2]) > 10) {
            $modules[] = [
                'id' => 'textformatter',
                'size' => mb_strlen($matches[2], '8bit'),
            ];
        }

        return $modules;
    }

    /**
     * Wraps preg_match with an exception
     * @param string $pattern
     * @param string $subject
     * @param $matches
     * @return int
     * @throws \Exception
     */
    protected function pregMatch(string $pattern, string $subject, &$matches): int
    {
        $preg = preg_match($pattern, $subject, $matches);

        if ($preg === false) {
            throw new \Exception(preg_last_error_msg());
        }

        return $preg;
    }
}
