<?php

namespace App;

class FlarumVersionGuesser
{
    /**
     * Takes the source code of the script tag containing app.boot() and attempts to guess the Flarum version
     * @param string $html Source of the whole page
     * @param string $bootScript Source of the script tag that contains the boot() call
     * @return string[] Possible versions
     */
    public function guess(string $html, string $bootScript): array
    {
        // Preload was introduced in 1.1 https://github.com/flarum/core/pull/3057
        if (preg_match('~rel="preload" href="[^"]+/fa-solid-900\.woff2" as="font"~', $html) === 1) {
            return [
                FlarumVersion::V1_1_0,
                FlarumVersion::V1_1_1,
                FlarumVersion::V1_2_0,
            ];
        }

        // Hashes changed in Flarum 1.0 https://github.com/flarum/core/pull/2805
        if (preg_match('~/assets/forum\.js\?v=[0-9a-f]{8}"></script>~', $html) === 1) {
            return [
                FlarumVersion::V1_0_0,
                FlarumVersion::V1_0_1,
                FlarumVersion::V1_0_2,
                FlarumVersion::V1_0_3,
                FlarumVersion::V1_0_4,
            ];
        }

        $matches = [];
        // beta7 calls app.boot() with the payload
        // beta8 calls app.load() with the payload then app.boot() without arguments
        if (preg_match('~app\.(boot|load)\(([^\n]+)\)~', $bootScript, $matches) === 1) {
            if ($matches[1] === 'boot') {
                return [FlarumVersion::BETA_7];
            }

            $hasHtmlDir = preg_match('~!doctype\s+html>\s*<html\s+dir="~i', $html) === 1;

            // Beta 10 adds (back) the dir and lang attributes https://github.com/flarum/core/commit/e88a9394edccc992b9b5fa2970086d2c4df86b8a
            // The meta canonical tag was also added in beta 10. But a second check is not needed
            if ($hasHtmlDir) {
                return [
                    FlarumVersion::BETA_10,
                    FlarumVersion::BETA_11,
                    FlarumVersion::BETA_12,
                    FlarumVersion::BETA_13,
                    FlarumVersion::BETA_14,
                    FlarumVersion::BETA_14_1,
                    FlarumVersion::BETA_15,
                    FlarumVersion::BETA_16,
                ];
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
