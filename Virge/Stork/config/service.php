<?php

use Virge\Core\Config;
use Virge\Stork\Service\ZMQMessagingService;
use Virge\Stork\Service\PushMessagingService;
use Virge\Stork\Service\WebsocketServerService;
use Virge\Virge;

$zmqServer = Config::get('stork', 'zmq_server');
$zmqPort = Config::get('stork', 'zmq_port');

$websocketServers = Config::get('stork', 'websocket_servers');

$websocketHostname = Config::get('stork', 'websocket_hostname');

$websocketUrl = Config::get('stork', 'websocket_url');
$realm = Config::get('stork', 'realm');
$role = Config::get('stork', 'role');
$secret = Config::get('stork', 'secret');

Virge::registerService(ZMQMessagingService::class, new ZMQMessagingService($zmqServer, $zmqPort, $websocketServers));
Virge::registerService(PushMessagingService::class, new PushMessagingService($websocketHostname));
Virge::registerService(WebsocketServerService::class, new WebsocketServerService($websocketUrl, $realm, $role, $secret));