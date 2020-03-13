<?php

namespace Tests\Unit;

use App\ApiGuesser\JsonApiGuesser;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

class ApiGuesserTest extends TestCase
{
    protected function urlToPageNumber(int $page): string
    {
        return 'https://example/api/users?page[limit]=50&page[offset]=' . (50 * $page);
    }

    protected function responseWithResultCount(int $count): ResponseInterface
    {
        return new Response(200, [], \GuzzleHttp\json_encode([
            'data' => array_fill(0, $count, null),
        ]));
    }

    protected function assertGuessCount(int $count, array $pageCounts)
    {
        $client = Mockery::mock(Client::class);
        foreach ($pageCounts as $page => $count) {
            $client->expects()
                ->get($this->urlToPageNumber($page), ['http_errors' => true])
                ->andReturns($this->responseWithResultCount($count));
        }
        $guesser = new JsonApiGuesser('https://example/api/users', $client);
        $this->assertEquals($count, $guesser->guess());
    }

    public function testOneEmptyPage()
    {
        $this->assertGuessCount(0, [
            0 => 0,
        ]);
    }

    public function testOnePage()
    {
        $this->assertGuessCount(5, [
            0 => 5,
        ]);
    }

    public function testTwoPages()
    {
        $this->assertGuessCount(55, [
            0 => 50,
            1 => 5,
        ]);
    }

    public function testThreePages()
    {
        $this->assertGuessCount(105, [
            0 => 50,
            1 => 50,
            2 => 5,
        ]);
    }

    public function testFourPages()
    {
        $this->assertGuessCount(155, [
            0 => 50,
            1 => 50,
            2 => 50,
            4 => 0,
            3 => 5,
        ]);
    }

    public function testEighteenPages()
    {
        $this->assertGuessCount(855, [
            0 => 50,
            1 => 50,
            2 => 50,
            4 => 50,
            8 => 50,
            16 => 50,
            32 => 0,
            24 => 0,
            20 => 0,
            18 => 0,
            17 => 5,
        ]);
    }

    public function testSixtyOnePages()
    {
        $this->assertGuessCount(3005, [
            0 => 50,
            1 => 50,
            2 => 50,
            4 => 50,
            8 => 50,
            16 => 50,
            32 => 50,
            64 => 0,
            48 => 50,
            56 => 60,
            60 => 5,
        ]);
    }

    public function testSixtyTwoPages()
    {
        $this->assertGuessCount(3005, [
            0 => 50,
            1 => 50,
            2 => 50,
            4 => 50,
            8 => 50,
            16 => 50,
            32 => 50,
            64 => 0,
            48 => 50,
            56 => 60,
            60 => 5,
        ]);
    }

    public function testTooManyPages()
    {
        $this->assertGuessCount(163840050, [
            0 => 50,
            1 => 50,
            2 => 50,
            4 => 50,
            8 => 50,
            16 => 50,
            32 => 50,
            64 => 50,
            128 => 50,
            256 => 50,
            512 => 50,
            1024 => 50,
            2048 => 50,
            4096 => 50,
            8192 => 50,
            16384 => 50,
            32768 => 50,
            65536 => 50,
        ]);
    }
}
