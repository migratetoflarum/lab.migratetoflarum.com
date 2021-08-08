<?php

namespace App\Services\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Basic;

class Policy extends Basic
{
    public function configure()
    {
        parent::configure();

        $this
            ->addDirective(Directive::IMG, 'https://flarum-badge-api.davwheat.dev')
            ->addDirective(Directive::IMG, 'https://extiverse.com')
            ->addDirective(Directive::IMG, 'https://analytics.kilowhat.net')
            ->addDirective(Directive::SCRIPT, 'https://analytics.kilowhat.net')
            ->addDirective(Directive::SCRIPT, 'https://*.pusher.com')
            ->addDirective(Directive::CONNECT, 'https://analytics.kilowhat.net')
            ->addDirective(Directive::CONNECT, 'https://*.pusher.com')
            ->addDirective(Directive::CONNECT, 'wss://*.pusher.com');
    }
}
