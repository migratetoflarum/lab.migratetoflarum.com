<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Pdp\Converter;
use Storage;

class UpdatePublicSuffixList extends Command
{
    protected $signature = 'update-public-suffix-list';
    protected $description = 'Update the Public Suffix List cache';

    public function handle()
    {
        $client = new Client();
        $response = $client->get('https://raw.githubusercontent.com/publicsuffix/list/master/public_suffix_list.dat');
        $content = $response->getBody()->getContents();
        $rules = (new Converter())->convert($content);

        Storage::put('public_suffix_list_converted', serialize($rules));

        $this->info('Public Suffix List cache updated.');
    }
}
