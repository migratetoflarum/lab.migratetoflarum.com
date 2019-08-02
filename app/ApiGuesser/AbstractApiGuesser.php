<?php

namespace App\ApiGuesser;

abstract class AbstractApiGuesser
{
    protected $resultsPerPage = 50;

    public function guess(): int
    {
        $count = $this->resultsOnPage(0);

        if ($count < $this->resultsPerPage) {
            return $count;
        }

        for ($i = 0; $i < 10; $i++) {
            $page = pow(2, $i);

            $count = $this->resultsOnPage($page);

            if ($count === 0) {
                if ($i <= 5) {
                    for ($j = pow(2, $i - 1); $j >= 0.5; $j = $j / 2) {
                        switch ($count) {
                            case $this->resultsPerPage:
                                if ($j < 1) {
                                    return $this->resultsPerPage * ($page + 1);
                                }
                                $page += $j;
                                break;
                            case 0:
                                if ($j < 1) {
                                    return $this->resultsPerPage * $page;
                                }
                                $page -= $j;
                                break;
                            default:
                                return $this->resultsPerPage * $page + $count;
                        }
                        $count = $this->resultsOnPage($page);
                    }
                } else {
                    return (pow(2, $i - 1) + 1) * $this->resultsPerPage;
                }
            }

            if ($count < $this->resultsPerPage) {
                return $count;
            }
        }


        return 0;
    }

    abstract protected function resultsOnPage(int $page): int;
}
