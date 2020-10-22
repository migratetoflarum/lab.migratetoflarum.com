<?php


namespace App\Console\Commands;


use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GetCoreJavascriptHash extends Command
{
    protected $signature = 'getcorejshash {tag}';

    public function handle()
    {
        foreach (['forum', 'admin'] as $frontend) {
            $this->info($frontend . ': ' . $this->getHashForFrontend($this->argument('tag'), $frontend));
        }
    }

    protected function getHashForFrontend(string $tag, string $frontend): string
    {
        $client = new Client();

        $response = $client->get("https://raw.githubusercontent.com/flarum/core/$tag/js/dist/$frontend.js");

        return md5(trim(str_replace("//# sourceMappingURL=$frontend.js.map", '', $response->getBody()->getContents())));
    }
}
