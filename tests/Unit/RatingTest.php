<?php

namespace Tests\Unit;

use App\Jobs\ScanAlternateUrlsAndHeaders;
use App\Jobs\ScanExposedFiles;
use App\Jobs\ScanHomePage;
use App\Jobs\ScanResolveCanonical;
use App\Report\RatingAgent;
use App\Scan;
use App\Task;
use App\Website;
use Illuminate\Support\Arr;
use Tests\TestCase;

class RatingTest extends TestCase
{
    protected function alterGoodReport(array $report = []): array
    {
        return array_replace_recursive([
            ScanAlternateUrlsAndHeaders::class => [
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
                'multipleUrls' => false,
                'wwwShouldWork' => true,
            ],
            ScanHomePage::class => [
                'debug' => false,
                'bootTitle' => 'Test Forum',
                'bootBaseUrl' => 'https://example.com',
            ],
            ScanResolveCanonical::class => [
                'destinationUrl' => 'https://example.com'
            ],
            ScanExposedFiles::class => [
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

    protected function createTaskFromArray(string $job, array $report)
    {
        $task = new Task();
        $task->job = $job;
        $task->data = Arr::get($report, $job);

        return $task;
    }

    protected function assertReportRating(array $report, string $rating, string $url = 'example.com/')
    {
        $website = new Website();
        $website->normalized_url = $url;

        $scan = new Scan();
        $scan->report = $report;
        $scan->website()->associate($website);

        $agent = new RatingAgent(
            $this->createTaskFromArray(ScanResolveCanonical::class, $report),
            $this->createTaskFromArray(ScanHomePage::class, $report),
            $this->createTaskFromArray(ScanAlternateUrlsAndHeaders::class, $report),
            $this->createTaskFromArray(ScanExposedFiles::class, $report)
        );
        $agent->rate();

        try {
            $this->assertEquals($rating, $agent->rating);
        } catch (\Exception $exception) {
            var_dump($agent->importantRules, $agent->rating);

            throw $exception;
        }
    }

    public function testInsecure()
    {
        // These are the tests for the current syntax with more details
        $this->assertReportRating($this->alterGoodReport([
            ScanExposedFiles::class => [
                'vendor' => [
                    'access' => true,
                ],
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            ScanExposedFiles::class => [
                'storage' => [
                    'access' => true,
                ],
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            ScanExposedFiles::class => [
                'composer' => [
                    'access' => true,
                ],
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            ScanHomePage::class => [
                'versions' => [
                    '0.1.0-beta.7',
                ],
            ],
        ]), 'D');

        $this->assertReportRating($this->alterGoodReport([
            ScanHomePage::class => [
                'versions' => [
                    '0.1.0-beta.8',
                ],
            ],
        ]), 'D');
    }

    public function testHttpErrors()
    {
        $this->assertReportRating($this->alterGoodReport([
            ScanAlternateUrlsAndHeaders::class => [
                'apex-http' => [
                    'type' => 'error',
                ],
            ],
        ]), 'B');

        $this->assertReportRating($this->alterGoodReport([
            ScanAlternateUrlsAndHeaders::class => [
                'www-https' => [
                    'type' => 'error',
                ],
            ],
        ]), 'B');

        // Error at 4th level shouldn't count when subdomain
        $this->assertReportRating($this->alterGoodReport([
            ScanAlternateUrlsAndHeaders::class => [
                'www-https' => [
                    'type' => 'error',
                ],
                'wwwShouldWork' => false,
            ],
        ]), 'A', 'forum.example.com/');

        // Should still count if canonical
        $this->assertReportRating($this->alterGoodReport([
            ScanAlternateUrlsAndHeaders::class => [
                'www-http' => [
                    'type' => 'error',
                ],
                'wwwShouldWork' => true,
            ],
        ]), 'B', 'forum.example.com/');

        // Should still count if it's not a subdomain
        $this->assertReportRating($this->alterGoodReport([
            ScanAlternateUrlsAndHeaders::class => [
                'www-https' => [
                    'type' => 'error',
                ],
            ],
        ]), 'B', 'example.co.uk/');
    }

    public function testBadRedirects()
    {
        $this->assertReportRating($this->alterGoodReport([
            ScanAlternateUrlsAndHeaders::class => [
                'www-http' => [
                    'status' => 302,
                ],
            ],
        ]), 'B');

        $this->assertReportRating($this->alterGoodReport([
            ScanAlternateUrlsAndHeaders::class => [
                'www-https' => [
                    'redirect_to' => 'http://example.com',
                ],
            ],
        ]), 'B');
    }

    public function testGoodReportWithDebug()
    {
        $this->assertReportRating($this->alterGoodReport([
            ScanHomePage::class => [
                'debug' => true,
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
            ScanAlternateUrlsAndHeaders::class => [
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
            ScanAlternateUrlsAndHeaders::class => [
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
            ScanAlternateUrlsAndHeaders::class => [
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
