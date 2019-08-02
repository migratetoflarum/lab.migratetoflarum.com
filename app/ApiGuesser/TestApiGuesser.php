<?php

namespace App\ApiGuesser;

class TestApiGuesser extends AbstractApiGuesser
{
    protected $pageResultCount = [];

    public function __construct(array $pageResultCount)
    {
        $this->pageResultCount = $pageResultCount;
    }

    protected function resultsOnPage(int $page): int
    {
        return array_get($this->pageResultCount, $page, 0);
    }
}
