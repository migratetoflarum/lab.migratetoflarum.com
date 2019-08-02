<?php

namespace App\Jobs;

use App\FlarumVersion;
use Illuminate\Support\Arr;

class ScanRate extends TaskJob
{
    protected function shouldExpectWwwToWork(Scan $scan): bool
    {
        $wwwIsCanonical = starts_with(array_get($scan->report, 'canonical_url'), ['https://www.', 'http://www.']);

        return array_has($scan->report, 'urls.www-http') && ($wwwIsCanonical || $scan->website->is_apex);
    }

    public function handleTask()
    {
        $rules = [
            [
                'cap' => 'D',
                'description' => 'Vulnerable Flarum version',
                'check' => function (): bool {
                    $homepage = $this->siblingTask(ScanHomePage::class);

                    $versions = $homepage->getData('versions', []);

                    if (in_array(FlarumVersion::BETA_7, $versions) || in_array(FlarumVersion::BETA_8, $versions)) {
                        return true;
                    }

                    return false;
                },
            ],
            [
                'cap' => 'D',
                'description' => 'Misconfiguration of server paths',
                'check' => function (): bool {
                    $exposed = $this->siblingTask(ScanExposedFiles::class);

                    foreach (['vendor', 'storage', 'composer'] as $access) {
                        if ($exposed->getData("$access.access") === true) {
                            return true;
                        }
                    }

                    return false;
                },
            ],
            [
                'cap' => 'C',
                'description' => 'Is answering on HTTP instead of redirecting',
                'check' => function (): bool {
                    $alternate = $this->siblingTask(ScanAlternateUrlsAndHeaders::class);

                    return $alternate->getData('apex-http.type') === 'ok' ||
                        $alternate->getData('www-http.type') === 'ok';
                },
            ],
            [
                'cap' => 'C',
                'description' => 'Is answering on multiple urls',
                'check' => function (): bool {
                    $alternate = $this->siblingTask(ScanAlternateUrlsAndHeaders::class);

                    return !!$alternate->getData('multipleUrls');
                },
            ],
            [
                'cap' => 'C',
                'description' => 'Is using an invalid base url',
                'check' => function (): bool {
                    $baseUrl = array_get($scan->report, 'homepage.boot.base_url');
                    $expectedBaseUrl = rtrim(array_get($scan->report, 'canonical_url'), '/');

                    return $baseUrl !== $expectedBaseUrl;
                },
            ],
            [
                'cap' => 'B',
                'description' => 'Is using non-permanent redirects',
                'check' => function (): bool {
                    $alternate = $this->siblingTask(ScanAlternateUrlsAndHeaders::class);

                    return $alternate->getData('apex-http.status') !== 301 ||
                        ($this->shouldExpectWwwToWork($scan) && $alternate->getData('www-http.status') !== 301);
                },
            ],
            [
                'cap' => 'B',
                'description' => 'Is redirecting from https to http',
                'check' => function (): bool {
                    $alternate = $this->siblingTask(ScanAlternateUrlsAndHeaders::class);

                    foreach (['www', 'apex'] as $domain) {
                        $redirect = $alternate->getData("$domain-https.redirect_to");

                        if ($redirect && starts_with($redirect, 'http://')) {
                            return true;
                        }
                    }

                    return false;
                },
            ],
            [
                'cap' => 'B',
                'description' => 'Some urls are returning errors',
                'check' => function (): bool {
                    $domains = ['apex'];

                    if ($this->shouldExpectWwwToWork($scan)) {
                        $domains[] = 'www';
                    }

                    foreach ($domains as $domain) {
                        foreach (['http', 'https'] as $proto) {
                            if (array_get($scan->report, "urls.$domain-$proto.type") === 'error') {
                                return true;
                            }
                        }
                    }

                    return false;
                },
            ],
            [
                'bonus' => '-',
                'description' => 'Debug mode is on',
                'check' => function (): bool {
                    return array_get($scan->report, "homepage.boot.debug") === true;
                },
            ],
            [
                'bonus' => '+',
                'description' => 'Is using HSTS with a max-age of 6 months or more',
                'check' => function (): bool {
                    $domains = ['apex'];

                    if ($this->shouldExpectWwwToWork($scan)) {
                        $domains[] = 'www';
                    }

                    foreach ($domains as $domain) {
                        $hsts = array_get($scan->report, "urls.$domain-https.headers.Strict-Transport-Security");

                        if (!is_array($hsts)) {
                            return false;
                        }

                        foreach ($hsts as $value) {
                            if (preg_match('~max\-age=([0-9]+)[$\s;]~', $value, $matches) !== 1) {
                                return false;
                            }

                            $maxAge = intval($matches[1]);

                            if ($maxAge < 60 * 60 * 24 * 30 * 6) {
                                return false;
                            }
                        }
                    }

                    return true;
                },
            ],
        ];

        $incomplete = false;

        $matched = array_filter($rules, function (array $rule, int $key) use (&$incomplete): bool {
            try {
                return call_user_func(Arr::get($rule, 'check'));
            } catch (\Exception $exception) {
                $incomplete = true;
                return false;
            }
        }, ARRAY_FILTER_USE_BOTH);

        if ($incomplete) {
            $this->data['rating'] = '-';
            $this->data['criteria'] = [[
                'description' => 'The scan did not complete. Not rating.',
                'cap' => '-',
            ]];

            return;
        }

        $rating = 'A';
        $bonus = '';

        $grades = [
            'A' => 5,
            'B' => 4,
            'C' => 3,
            'D' => 2,
            '-' => 1,
        ];

        foreach ($matched as $rule) {
            if ($cap = Arr::get($rule, 'cap')) {
                $newGrade = Arr::get($grades, $cap);
                $currentGrade = Arr::get($grades, $rating);

                if ($newGrade < $currentGrade) {
                    $rating = $cap;
                }
            }

            if ($bonus !== '-' && $newBonus = Arr::get($rule, 'bonus')) {
                $bonus = $newBonus;
            }
        }

        // Can't get a + on a rating under B
        if ($bonus === '+' && !in_array($rating, ['A', 'B'])) {
            $bonus = '';
        }

        // Can't get a - on a rating under C
        if ($bonus === '-' && !in_array($rating, ['A', 'B', 'C'])) {
            $bonus = '';
        }

        $this->data['rating'] = $rating . $bonus;

        $this->data['criteria'] = array_values(array_map(function (array $rule): array {
            return array_only($rule, [
                'description',
                'cap',
                'bonus',
            ]);
        }, array_filter($matched, function (array $rule) use ($rating, $bonus): bool {
            return Arr::get($rule, 'cap') === $rating || Arr::get($rule, 'bonus') === $bonus;
        })));
    }
}
