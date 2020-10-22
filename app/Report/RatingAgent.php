<?php

namespace App\Report;

use App\FlarumVersion;
use App\Task;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RatingAgent
{
    protected $canonical;
    protected $homepage;
    protected $alternate;
    protected $exposed;
    protected $version;

    public $rating = '-';
    public $importantRules = [];

    public function __construct(Task $canonical, Task $homepage, Task $alternate, Task $exposed, Task $version)
    {
        $this->canonical = $canonical;
        $this->homepage = $homepage;
        $this->alternate = $alternate;
        $this->exposed = $exposed;
        $this->version = $version;
    }

    public function rate()
    {
        $rules = [
            [
                'cap' => 'D',
                'description' => 'Vulnerable Flarum version',
                'check' => function (): bool {
                    $versions = $this->version->getData('versions', []);

                    // We mark as vulnerable if all the possible versions are part of the vulnerable list
                    // If we're unsure, we won't mark vulnerable
                    // If the list of guest versions is empty, we skip this test, this will happen for forums running a custom build
                    if (count($versions) && count(array_diff($versions, [
                            FlarumVersion::BETA_7,
                            FlarumVersion::BETA_8,
                            FlarumVersion::BETA_9,
                            FlarumVersion::BETA_10,
                            FlarumVersion::BETA_11,
                            FlarumVersion::BETA_12,
                        ])) === 0) {
                        return true;
                    }

                    return false;
                },
            ],
            [
                'cap' => 'D',
                'description' => 'Misconfiguration of server paths',
                'check' => function (): bool {
                    foreach (['vendor', 'storage', 'composer'] as $access) {
                        if ($this->exposed->getData("$access.access") === true) {
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
                    return $this->alternate->getData('apex-http.type') === 'ok' ||
                        $this->alternate->getData('www-http.type') === 'ok';
                },
            ],
            [
                'cap' => 'C',
                'description' => 'Is answering on multiple urls',
                'check' => function (): bool {
                    return !!$this->alternate->getData('multipleUrls');
                },
            ],
            [
                'cap' => 'C',
                'description' => 'Is using an invalid base url',
                'check' => function (): bool {
                    $baseUrl = $this->homepage->getData('bootBaseUrl');
                    $expectedBaseUrl = rtrim($this->canonical->getData('destinationUrl'), '/');

                    return $baseUrl !== $expectedBaseUrl;
                },
            ],
            [
                'cap' => 'B',
                'description' => 'Is using non-permanent redirects',
                'check' => function (): bool {
                    return $this->alternate->getData('apex-http.status') !== 301 ||
                        ($this->alternate->getData('wwwShouldWork') && $this->alternate->getData('www-http.status') !== 301);
                },
            ],
            [
                'cap' => 'B',
                'description' => 'Is redirecting from https to http',
                'check' => function (): bool {
                    foreach (['www', 'apex'] as $domain) {
                        $redirect = $this->alternate->getData("$domain-https.redirect_to");

                        if ($redirect && Str::startsWith($redirect, 'http://')) {
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

                    if ($this->alternate->getData('wwwShouldWork')) {
                        $domains[] = 'www';
                    }

                    foreach ($domains as $domain) {
                        foreach (['http', 'https'] as $proto) {
                            if ($this->alternate->getData("$domain-$proto.type") === 'error') {
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
                    return $this->homepage->getData('debug') === true;
                },
            ],
            [
                'bonus' => '+',
                'description' => 'Is using HSTS with a max-age of 6 months or more',
                'check' => function (): bool {
                    $domains = ['apex'];

                    if ($this->alternate->getData('wwwShouldWork')) {
                        $domains[] = 'www';
                    }

                    foreach ($domains as $domain) {
                        $hsts = $this->alternate->getData("$domain-https.headers.Strict-Transport-Security");

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

        $matched = array_filter($rules, function (array $rule): bool {
            return call_user_func(Arr::get($rule, 'check'));
        });

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

        $this->rating = $rating . $bonus;

        $this->importantRules = array_values(array_map(function (array $rule): array {
            return Arr::only($rule, [
                'description',
                'cap',
                'bonus',
            ]);
        }, array_filter($matched, function (array $rule) use ($rating, $bonus): bool {
            return Arr::get($rule, 'cap') === $rating || Arr::get($rule, 'bonus') === $bonus;
        })));
    }
}
