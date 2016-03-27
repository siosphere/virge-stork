<?php

use Virge\Core\Config;
use Virge\Stork\Service\ZMQMessagingService;
use Virge\Virge;

$zmqServer = Config::get('stork', 'zmq_server');
$zmqPort = Config::get('stork', 'zmq_port');

$websocketServers = Config::get('stork', 'websocket_servers');

Virge::registerService(ZMQMessagingService::SERVICE_ID, new ZMQMessagingService($zmqServer, $zmqPort, $websocketServers));