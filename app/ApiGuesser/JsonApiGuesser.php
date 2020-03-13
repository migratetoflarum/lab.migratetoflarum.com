<?php

namespace App\ApiGuesser;

use GuzzleHttp\Client;

class JsonApiGuesser extends AbstractApiGuesser
{
    protected $url;
    protected $client;

    public function __construct(string $url, Client $client)
    {
        $this->url = $url;
        $this->client = $client;
    }

    protected function resultsOnPage(int $page): int
    {
        $document = \GuzzleHttp\json_decode($this->client->get($this->url . '?page[limit]=' . $this->resultsPerPage . '&page[offset]=' . ($this->resultsPerPage * $page), [
            'http_errors' => true,
        ])->getBody()->getContents(), true);

        return count(array_get($document, 'data'));
    }
}
