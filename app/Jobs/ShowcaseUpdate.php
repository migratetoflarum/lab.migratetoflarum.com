<?php

namespace App\Jobs;

use App\ScannerClient;
use App\Website;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ShowcaseUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const PAGE_LIMIT = 50;

    protected $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function handle()
    {
        if ($this->website->ignore || !$this->website->is_flarum) {
            if ($this->website->showcase_meta) {
                $this->website->showcase_meta = null;
                $this->website->save();
            }

            return;
        }

        /**
         * @var $client ScannerClient
         */
        $client = app(ScannerClient::class);

        $apiUrl = rtrim($this->website->canonical_url, '/') . '/api';

        try {
            $forum = \GuzzleHttp\json_decode($client->get($apiUrl, [
                'http_errors' => true,
            ])->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            // Only available on beta 8
            if ($exception->getCode() === 404) {
                $forum = null;
            } else {
                throw $exception;
            }
        }

        $discussionCount = $this->guessTotalItems("$apiUrl/discussions");

        // The current script can count up to 51200 discussions
        // We round it down to 50k and will show it as 50k+
        if ($discussionCount > 50000) {
            $discussionCount = 50000;
        }

        try {
            $userCount = $this->guessTotalItems("$apiUrl/users");
        } catch (ClientException $exception) {
            // It's normal to get 401 Unauthorized if user listing wasn't enabled
            if ($exception->getCode() === 401) {
                $userCount = null;
            } else {
                throw $exception;
            }
        }
        if ($userCount > 50000) {
            $userCount = 50000;
        }

        $description = trim(array_get($forum, 'data.attributes.description'));

        if ($forum && $title = trim(array_get($forum, 'data.attributes.title'))) {
            $this->website->name = $title;
        }
        $this->website->showcase_meta = [
            'description' => $description ?: null,
            'discussionCount' => $discussionCount,
            'userCount' => $userCount,
            'date' => now()->toW3cString(),
        ];
        $this->website->save();
    }

    protected function guessTotalItems(string $url): int
    {
        /**
         * @var $client ScannerClient
         */
        $client = app(ScannerClient::class);

        // We first try offset 0, because many forums will have few posts
        // And also offset 0 is never properly reached with the loop below
        $document = \GuzzleHttp\json_decode($client->get($url . '?page[limit]=' . self::PAGE_LIMIT, [
            'http_errors' => true,
        ])->getBody()->getContents(), true);

        $items = array_get($document, 'data');
        if (count($items) < self::PAGE_LIMIT) {
            return count($items);
        }

        // Must be a power of 2
        $page = 1024;

        // Try to find a page that's not completely full in as few queries as possible
        for ($i = $page / 2; $i >= 0.5; $i = $i / 2) {
            $document = \GuzzleHttp\json_decode($client->get($url . '?page[limit]=' . self::PAGE_LIMIT . '&page[offset]=' . (self::PAGE_LIMIT * $page), [
                'http_errors' => true,
            ])->getBody()->getContents(), true);

            $items = array_get($document, 'data');

            switch (count($items)) {
                case self::PAGE_LIMIT:
                    if ($i < 1) {
                        return self::PAGE_LIMIT * ($page + 1);
                    }
                    $page += $i;
                    break;
                case 0:
                    if ($i < 1) {
                        return self::PAGE_LIMIT * $page;
                    }
                    $page -= $i;
                    break;
                default:
                    return self::PAGE_LIMIT * $page + count($items);
            }
        }
    }
}
