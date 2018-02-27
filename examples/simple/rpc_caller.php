<?php

include 'services.php';

use Virge\Stork;
use Virge\Stork\Service\RPCClientService;
use Virge\Virge;

Virge::service(RPCClientService::class)->do(function() {
    echo 'doing stuff' . "\n";
    $this->call('virge.stork.example.foo', [
        'name' => 'bar'
    ])->then(function($response) {
        var_dump($response);
    }, function($err) {});
    sleep(1);
})->startClient();