<?php

namespace App;

class FlarumVersionGuesser
{
    public array $debug = [];

    /**
     * Takes the source code of the script tag containing app.boot() and attempts to guess the Flarum version
     * @param string $html Source of the whole page
     * @param string $bootScript Source of the script tag that contains the boot() call
     * @return string[] Possible versions
     */
    public function guess(string $html, string $bootScript): array
    {
        // Hashes changed in Flarum 1.0 https://github.com/flarum/core/pull/2805
        // We'll use this as the switch to Flarum 1.0+ detection
        // Cloudflare Rocket Loader adds a type="" attribute to all script tags
        if (preg_match('~/assets/forum\.js\?v=[0-9a-f]{8}"\s*(type="[^"]+")?></script>~', $html) === 1) {
            $constraints = [];
            $this->debug[] = 'Flarum 1.x assets hash found, continuing in 1.x mode';

            // Preload was introduced in 1.1 https://github.com/flarum/core/pull/3057
            if (preg_match('~<link rel="preload" href="~', $html) === 1) {
                $constraints[] = ['>=', 1, 1, 0];
                $this->debug[] = 'Preloaded link found, must be >=1.1';
            } else {
                $constraints[] = ['<', 1, 1, 0];
                $this->debug[] = 'No preloaded link found, must be <1.1';
            }

            // Separate JSON payload tag was introduced in 1.4 https://github.com/flarum/framework/pull/3461
            // Cloudflare Rocket Loader removes the quotes around the ID
            if (preg_match('~<script\s+id=("flarum-json-payload"|flarum-json-payload)\s+type="application/json"~', $html) === 1) {
                $constraints[] = ['>=', 1, 4, 0];
                $this->debug[] = 'JSON payload tag found, must be >=1.4';
            } else {
                // If other constraints matched Flarum 1.0+, we'll take the absence of this tag as indication we are below 1.4
                // Otherwise the code will jump to beta detection
                $constraints[] = ['<', 1, 4, 0];
                $this->debug[] = 'JSON payload tag not found, must be <1.4';
            }

            // .Header-title no longer uses <h1> since Flarum 1.7 https://github.com/flarum/framework/pull/3724
            $hasHeaderTitleTag = preg_match('~<(h1|div)\s+class=("Header-title"|Header-title)~', $html, $matches) === 1;

            if ($hasHeaderTitleTag) {
                if ($matches[1] === 'div') {
                    $constraints[] = ['>=', 1, 7, 0];
                    $this->debug[] = 'Header-title uses <div>, must be >=1.7';
                } else {
                    $constraints[] = ['<', 1, 7, 0];
                    $this->debug[] = 'Header-title uses <h1>, must be <1.7';
                }
            }

            // The constraint part is only for Flarum above 1.0
            return array_values(array_filter([
                FlarumVersion::V1_0_0,
                FlarumVersion::V1_0_1,
                FlarumVersion::V1_0_2,
                FlarumVersion::V1_0_3,
                FlarumVersion::V1_0_4,
                FlarumVersion::V1_1_0,
                FlarumVersion::V1_1_1,
                FlarumVersion::V1_2_0,
                FlarumVersion::V1_2_1,
                FlarumVersion::V1_3_0,
                FlarumVersion::V1_3_1,
                FlarumVersion::V1_4_0,
                FlarumVersion::V1_5_0,
                FlarumVersion::V1_6_0,
                FlarumVersion::V1_6_1,
                FlarumVersion::V1_6_2,
                FlarumVersion::V1_6_3,
                FlarumVersion::V1_7_0,
                FlarumVersion::V1_7_1,
                FlarumVersion::V1_7_2,
                FlarumVersion::V1_8_0,
                FlarumVersion::V1_8_1,
                FlarumVersion::V1_8_2,
                FlarumVersion::V1_8_3,
                FlarumVersion::V1_8_4,
                FlarumVersion::V1_8_5,
            ], function ($version) use ($constraints) {
                $parts = explode('.', $version);

                foreach ($constraints as $constraint) {
                    switch ($constraint[0]) {
                        case '>=':
                            if ($parts[0] < $constraint[1] || ($parts[0] == $constraint[1] && $parts[1] < $constraint[2]) || ($parts[0] == $constraint[1] && $parts[1] == $constraint[2] && $parts[2] < $constraint[3])) {
                                return false;
                            }
                            break;
                        case '<':
                            if ($parts[0] > $constraint[1] || ($parts[0] == $constraint[1] && $parts[1] > $constraint[2]) || ($parts[0] == $constraint[1] && $parts[1] == $constraint[2] && $parts[2] >= $constraint[3])) {
                                return false;
                            }
                            break;
                        default:
                            return false;
                    }
                }

                return true;
            }));
        }

        $this->debug[] = 'Flarum 1.x assets hash not found, assuming beta version';

        $matches = [];
        // beta7 calls app.boot() with the payload
        // beta8 calls app.load() with the payload then app.boot() without arguments
        if (preg_match('~app\.(boot|load)\(([^\n]+)\)~', $bootScript, $matches) === 1) {
            if ($matches[1] === 'boot') {
                $this->debug[] = 'Found app.boot method, must be beta7. Older versions not supported by this script';
                return [FlarumVersion::BETA_7];
            }

            $hasHtmlDir = preg_match('~!doctype\s+html>\s*<html\s+dir="~i', $html) === 1;

            // Beta 10 adds (back) the dir and lang attributes https://github.com/flarum/core/commit/e88a9394edccc992b9b5fa2970086d2c4df86b8a
            // The meta canonical tag was also added in beta 10. But a second check is not needed
            if ($hasHtmlDir) {
                $this->debug[] = 'Found <html> tag dir="" attribute, must be >=beta10';
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
                if ($hasPostId) {
                    $this->debug[] = 'Found preloaded post payload with duplicated "id" attribute, must be beta 8';
                    return [FlarumVersion::BETA_8];
                } else {
                    $this->debug[] = 'Found preloaded post payload without duplicated "id" attribute, must be beta 9';
                    return [FlarumVersion::BETA_9];
                }
            }

            $hasTag = preg_match('~"type":\s*"tags"~', $matches[2]) === 1;

            if ($hasTag) {
                $hasTagIcon = preg_match('~"icon":(null|"[^"]+"),"iconUrl":(null|"[^"]+"),"discussionCount"~', $matches[2]) === 1;

                // Beta 9 introduces the tag icons
                if ($hasTagIcon) {
                    $this->debug[] = 'Found preloaded tag with icon attributes, must be beta 9';
                    return [FlarumVersion::BETA_9];
                } else {
                    $this->debug[] = 'Found preloaded tag without icon attributes, must be beta 8';
                    return [FlarumVersion::BETA_8];
                }
            }

            $this->debug[] = 'Found app.load but no other hints, must be beta 8 or 9';
            // If we can't see any post or tag payload there's no way to tell just by reading the homepage HTML
            return [
                FlarumVersion::BETA_8,
                FlarumVersion::BETA_9,
            ];
        }

        $this->debug[] = 'Found no clues in page HTML. Can\'t guess version';
        return [];
    }
}
