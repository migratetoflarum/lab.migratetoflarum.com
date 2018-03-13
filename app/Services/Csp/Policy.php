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
            ->addDirective(Directive::IMG, 'https://flagrow.io')
            ->addDirective(Directive::IMG, 'https://analytics.kilowhat.net')
            ->addDirective(Directive::SCRIPT, 'https://analytics.kilowhat.net')
            ->addDirective(Directive::SCRIPT, 'https://*.pusher.com')
            ->addDirective(Directive::CONNECT, 'https://*.pusher.com')
            ->addDirective(Directive::CONNECT, 'wss://*.pusher.com');
    }
}
