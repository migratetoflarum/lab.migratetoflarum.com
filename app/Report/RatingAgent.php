<?php

namespace App\Report;

use App\FlarumVersion;
use App\Scan;

class RatingAgent
{
    protected $scan;

    public $rating = '-';
    public $importantRules = [];

    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    protected function shouldExpectWwwToWork(Scan $scan): bool
    {
        $wwwIsCanonical = starts_with(array_get($scan->report, 'canonical_url'), ['https://www.', 'http://www.']);

        return array_has($scan->report, 'urls.www-http') && ($wwwIsCanonical || $scan->website->is_apex);
    }

    public function rate()
    {
        $rules = [
            [
                'cap' => '-',
                'description' => 'Is not a Flarum install',
                'check' => function (Scan $scan): bool {
                    return is_null($scan->report) || !array_get($scan->report, 'homepage.boot.base_url');
                },
            ],
            [
                'cap' => 'D',
                'description' => 'Suffers from security vulnerabilities',
                'check' => function (Scan $scan): bool {
                    $versions = array_get($scan->report, 'homepage.versions', []);

                    if (in_array(FlarumVersion::BETA_7, $versions) || in_array(FlarumVersion::BETA_8, $versions)) {
                        return true;
                    }


                    if (array_has($scan->report, 'vulnerabilities') && count(array_get($scan->report, 'vulnerabilities')) > 0) {
                        return true;
                    }

                    foreach (['vendor', 'storage', 'composer'] as $access) {
                        // Old format
                        if (array_get($scan->report, "malicious_access.$access") === true) {
                            return true;
                        }

                        // Current format
                        if (array_get($scan->report, "malicious_access.$access.access") === true) {
                            return true;
                        }
                    }

                    return false;
                },
            ],
            [
                'cap' => 'C',
                'description' => 'Is answering on HTTP instead of redirecting',
                'check' => function (Scan $scan): bool {
                    return array_get($scan->report, 'urls.apex-http.type') === 'ok' ||
                        array_get($scan->report, 'urls.www-http.type') === 'ok';
                },
            ],
            [
                'cap' => 'C',
                'description' => 'Is answering on multiple urls',
                'check' => function (Scan $scan): bool {
                    return !!array_get($scan->report, 'multiple_urls');
                },
            ],
            [
                'cap' => 'C',
                'description' => 'Is using an invalid base url',
                'check' => function (Scan $scan): bool {
                    $baseUrl = array_get($scan->report, 'homepage.boot.base_url');
                    $expectedBaseUrl = rtrim(array_get($scan->report, 'canonical_url'), '/');

                    return $baseUrl !== $expectedBaseUrl;
                },
            ],
            [
                'cap' => 'B',
                'description' => 'Is using non-permanent redirects',
                'check' => function (Scan $scan): bool {
                    return array_get($scan->report, 'urls.apex-http.status') !== 301 ||
                        ($this->shouldExpectWwwToWork($scan) && array_get($scan->report, 'urls.www-http.status') !== 301);
                },
            ],
            [
                'cap' => 'B',
                'description' => 'Is redirecting from https to http',
                'check' => function (Scan $scan): bool {
                    foreach (['www', 'apex'] as $domain) {
                        $redirect = array_get($scan->report, "urls.$domain-https.redirect_to");

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
                'check' => function (Scan $scan): bool {
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
                'check' => function (Scan $scan): bool {
                    return array_get($scan->report, "homepage.boot.debug") === true;
                },
            ],
            [
                'bonus' => '+',
                'description' => 'Is using HSTS with a max-age of 6 months or more',
                'check' => function (Scan $scan): bool {
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

        $matched = array_filter($rules, function (array $rule, int $key): bool {
            // Only try the first rule if there is no report
            if (is_null($this->scan->report) && $key > 0) {
                return false;
            }

            return array_get($rule, 'check')($this->scan);
        }, ARRAY_FILTER_USE_BOTH);

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
            if ($cap = array_get($rule, 'cap')) {
                $newGrade = array_get($grades, $cap);
                $currentGrade = array_get($grades, $rating);

                if ($newGrade < $currentGrade) {
                    $rating = $cap;
                }
            }

            if ($bonus !== '-' && $newBonus = array_get($rule, 'bonus')) {
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

        $this->rating = $rating . $bonus;

        $this->importantRules = array_values(array_map(function (array $rule): array {
            return array_only($rule, [
                'description',
                'cap',
                'bonus',
            ]);
        }, array_filter($matched, function (array $rule) use ($rating, $bonus): bool {
            return array_get($rule, 'cap') === $rating || array_get($rule, 'bonus') === $bonus;
        })));
    }
}
