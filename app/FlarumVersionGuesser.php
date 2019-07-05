<?php

namespace App;

class FlarumVersionGuesser
{
    /**
     * Takes the source code of the script tag containing app.boot() and attempts to guess the Flarum version
     * @param string $html
     * @return string[] Possible versions
     */
    public function guess(string $html): array
    {
        $matches = [];
        // beta7 calls app.boot() with the payload
        // beta8 calls app.load() with the payload then app.boot() without arguments
        if (preg_match('~app\.(boot|load)\(([^\n]+)\)~', $html, $matches) === 1) {
            if ($matches[1] === 'boot') {
                return [FlarumVersion::BETA_7];
            }

            $hasPost = preg_match('~"type":\s*"posts"~', $matches[2]) === 1;

            if ($hasPost) {
                $hasPostId = preg_match('~"type":"posts","id":"[0-9]+","attributes":{"id"~', $matches[2]) === 1;

                // Up until beta 8 there was a useless "id" attribute https://github.com/flarum/core/pull/1775
                return [$hasPostId ? FlarumVersion::BETA_8 : FlarumVersion::BETA_9];
            }

            $hasTag = preg_match('~"type":\s*"tags"~', $matches[2]) === 1;

            if ($hasTag) {
                $hasTagIcon = preg_match('~"icon":(null|"[^"]+"),"iconUrl":(null|"[^"]+"),"discussionCount"~', $matches[2]) === 1;

                // Beta 9 introduces the tag icons
                return [$hasTagIcon ? FlarumVersion::BETA_9 : FlarumVersion::BETA_8];
            }

            // If we can't see any post or tag payload there's no way to tell just by reading the homepage HTML
            return [
                FlarumVersion::BETA_8,
                FlarumVersion::BETA_9,
            ];
        }

        return [];
    }
}
