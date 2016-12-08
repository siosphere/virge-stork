<?php

use Virge\Core\Config;
use Virge\Stork\Service\ZMQMessagingService;
use Virge\Stork\Service\PushMessagingService;
use Virge\Virge;

$zmqServer = Config::get('stork', 'zmq_server');
$zmqPort = Config::get('stork', 'zmq_port');

$websocketServers = Config::get('stork', 'websocket_servers');

Virge::registerService(ZMQMessagingService::class, new ZMQMessagingService($zmqServer, $zmqPort, $websocketServers));
Virge::registerService(PushMessagingService::class, new PushMessagingService());