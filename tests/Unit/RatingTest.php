<?php

namespace Tests\Unit;

use App\Report\RatingAgent;
use App\Scan;
use App\Website;
use Tests\TestCase;

class RatingTest extends TestCase
{
    protected function alterGoodReport(array $report = []): array
    {
        return array_replace_recursive([
            'urls' => [
                'www-http' => [
                    'type' => 'redirect',
                    'status' => 301,
                    'redirect_to' => 'https://www.example.com',
                    'headers' => [
                        'Server' => ['test'],
                    ],
                ],
                'apex-http' => [
                    'type' => 'redirect',
                    'status' => 301,
                    'redirect_to' => 'https://example.com',
                    'headers' => [
                        'Server' => ['test'],
                    ],
                ],
                'www-https' => [
                    'type' => 'redirect',
                    'status' => 301,
                    'redirect_to' => 'https://example.com',
                    'headers' => [
                        'Server' => ['test'],
                    ],
                ],
                'apex-https' => [
                    'type' => 'ok',
                    'status' => 200,
                    'headers' => [
                        'Server' => ['test'],
                    ],
                ],
            ],
            'homepage' => [
                'boot' => [
                    'debug' => false,
                    'title' => 'Test Forum',
                    'base_url' => 'https://example.com',
                ],
                'modules' => [
                    'locale',
                    'flarum/tags/main',
                ],
            ],
            'base_address' => 'example.com/',
            'canonical_url' => 'https://example.com',
            'multiple_urls' => false,
            'malicious_access' => [
                'vendor' => [
                    'access' => false,
                    'urls' => [],
                    'errors' => [],
                ],
                'storage' => [
                    'access' => false,
                    'urls' => [],
                    'errors' => [],
                ],
                'composer' => [
                    'access' => false,
                    'urls' => [],
                    'errors' => [],
                ],
            ],
        ], $report);
    }

    protected function assertReportRating(array $report = null, string $rating, string $url = 'example.com/')
    {
        $website = new Website();
        $website->normalized_url = $url;

        $scan = new Scan();
        $scan->report = $report;
        $scan->website()->associate($website);

        $agent = new RatingAgent($scan);
        $agent->rate();

        try {
            $this->assertEquals($rating, $agent->rating);
        } catch (\Exception $exception) {
            var_dump($agent->importantRules, $agent->rating);

            throw $exception;
        }
    }

    public function testIncomplete()
    {
        $this->assertReportRating(null, '-');
    }

    public function testNotFlarum()
    {
        $this->assertReportRating($this->alterGoodReport([
            'homepage' => [
                'boot' => null,
            ],
        ]), '-');
    }

    public function testInsecure()
    {
        // These are tests for the old syntax that used a single boolean value
        $this->assertReportRating($this->alterGoodReport([
            'malicious_access' => [
                'vendor' => true,
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            'malicious_access' => [
                'storage' => true,
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            'malicious_access' => [
                'composer' => true,
            ],
        ]), 'D');

        // These are the tests for the current syntax with more details
        $this->assertReportRating($this->alterGoodReport([
            'malicious_access' => [
                'vendor' => [
                    'access' => true,
                ],
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            'malicious_access' => [
                'storage' => [
                    'access' => true,
                ],
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            'malicious_access' => [
                'composer' => [
                    'access' => true,
                ],
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            'homepage' => [
                'versions' => [
                    '0.1.0-beta.7',
                ],
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            'homepage' => [
                'versions' => [
                    '0.1.0-beta.8',
                ],
            ],
        ]), 'D');
    }

    public function testHttpErrors()
    {
        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'apex-http' => [
                    'type' => 'error',
                ],
            ],
        ]), 'B');

        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-https' => [
                    'type' => 'error',
                ],
            ],
        ]), 'B');

        // Error at 4th level shouldn't count when subdomain
        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-https' => [
                    'type' => 'error',
                ],
            ],
        ]), 'A', 'forum.example.com/');

        // Should still count if canonical
        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-http' => [
                    'type' => 'error',
                ],
            ],
            'canonical_url' => 'https://www.forum.example.com',
            'homepage' => [
                'boot' => [
                    'base_url' => 'https://www.forum.example.com',
                ],
            ],
        ]), 'B', 'forum.example.com/');

        // Should still count if it's not a subdomain
        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-https' => [
                    'type' => 'error',
                ],
            ],
        ]), 'B', 'example.co.uk/');
    }

    public function testBadRedirects()
    {
        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-http' => [
                    'status' => 302,
                ],
            ],
        ]), 'B');

        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-https' => [
                    'redirect_to' => 'http://example.com',
                ],
            ],
        ]), 'B');
    }

    public function testGoodReportWithDebug()
    {
        $this->assertReportRating($this->alterGoodReport([
            'homepage' => [
                'boot' => [
                    'debug' => true,
                ],
            ],
        ]), 'A-');
    }

    public function testGoodReport()
    {
        $this->assertReportRating($this->alterGoodReport(), 'A');
    }

    public function testGoodWithHSTS()
    {
        // 7 days
        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-https' => [
                    'headers' => [
                        'Strict-Transport-Security' => ['max-age=604800; includeSubDomains; preload'],
                    ],
                ],
                'apex-https' => [
                    'headers' => [
                        'Strict-Transport-Security' => ['max-age=604800; includeSubDomains; preload'],
                    ],
                ],
            ],
        ]), 'A');

        // 6 months
        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-https' => [
                    'headers' => [
                        'Strict-Transport-Security' => ['max-age=15552000; includeSubDomains; preload'],
                    ],
                ],
                'apex-https' => [
                    'headers' => [
                        'Strict-Transport-Security' => ['max-age=15552000; includeSubDomains; preload'],
                    ],
                ],
            ],
        ]), 'A+');

        // 12 months
        $this->assertReportRating($this->alterGoodReport([
            'urls' => [
                'www-https' => [
                    'headers' => [
                        'Strict-Transport-Security' => ['max-age=31536000; includeSubDomains; preload'],
                    ],
                ],
                'apex-https' => [
                    'headers' => [
                        'Strict-Transport-Security' => ['max-age=31536000; includeSubDomains; preload'],
                    ],
                ],
            ],
        ]), 'A+');
    }
}
