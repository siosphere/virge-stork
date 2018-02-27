<?php

include 'services.php';

use Virge\Stork;
use Virge\Stork\Service\RPCProviderService;
use Virge\Virge;

Stork::registerRPC(ExampleController::class);

Virge::service(RPCProviderService::class)
            ->startClient();