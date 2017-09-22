<?php

use Virge\Core\Config;
use Virge\Stork\Service\{
    AuthClientService,
    MetaClientService,
    PushMessagingService,
    RPCClientService,
    RPCProviderService,
    WebsocketClientService,
    ZMQMessagingService
};
use Virge\Virge;

$zmqServer = Config::get('stork', 'zmq_server');
$zmqPort = Config::get('stork', 'zmq_port');

$websocketServers = Config::get('stork', 'websocket_servers');

$websocketHostname = Config::get('stork', 'websocket_hostname');

$websocketUrl = Config::get('stork', 'websocket_url');
$realm = Config::get('stork', 'realm');
$role = Config::get('stork', 'role');
$secret = Config::get('stork', 'secret');


$pubSubProvider = Config::get('stork', 'pub_sub_provider');
if(!$pubSubProvider) {
    $pubSubProvider = ZMQMessagingService::class;
}

Virge::registerService(PubSubService::class, new PubSubService($pubSubProvider));
Virge::registerService(ZMQMessagingService::class, new ZMQMessagingService($zmqServer, $zmqPort, $websocketServers));
Virge::registerService(PushMessagingService::class, new PushMessagingService($websocketHostname));
Virge::registerService(WebsocketClientService::class, new WebsocketClientService($websocketUrl, $realm, $role, $secret));
Virge::registerService(MetaClientService::class, new MetaClientService($websocketUrl, $realm, $role, $secret));
Virge::registerService(AuthClientService::class, new AuthClientService($websocketUrl, $realm, $role, $secret));
Virge::registerService(RPCProviderService::class, new RPCProviderService($websocketUrl, $realm, $role, $secret));
Virge::registerService(RPCClientService::class, new RPCClientService($websocketUrl, $realm, $role, $secret));