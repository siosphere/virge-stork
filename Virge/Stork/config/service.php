<?php

use Virge\Core\Config;
use Virge\Stork\Service\{
    AuthClientService,
    MetaClientService,
    PubSubProvider\RedisProvider,
    PubSubProvider\ZMQProvider,
    PubSubService,
    PushMessagingService,
    RPCClientService,
    RPCProviderService,
    WebsocketClientService
};
use Virge\Virge;

$websocketHostname = Config::get('stork', 'websocket_hostname');

$websocketUrl = Config::get('stork', 'websocket_url');
$realm = Config::get('stork', 'realm');
$role = Config::get('stork', 'role');
$secret = Config::get('stork', 'secret');


$pubSubProvider = Config::get('stork', 'pub_sub_provider');
if(!$pubSubProvider) {
    $pubSubProvider = ZMQProvider::class;
}

Virge::registerService(RedisProvider::class, new RedisProvider());
Virge::registerService(ZMQProvider::class, new ZMQProvider());
Virge::registerService(PubSubService::class, new PubSubService($pubSubProvider));

Virge::registerService(PushMessagingService::class, new PushMessagingService($websocketHostname));
Virge::registerService(WebsocketClientService::class, new WebsocketClientService($websocketUrl, $realm, $role, $secret));
Virge::registerService(MetaClientService::class, new MetaClientService($websocketUrl, $realm, $role, $secret));
Virge::registerService(AuthClientService::class, new AuthClientService($websocketUrl, $realm, $role, $secret));
Virge::registerService(RPCProviderService::class, new RPCProviderService($websocketUrl, $realm, $role, $secret));
Virge::registerService(RPCClientService::class, new RPCClientService($websocketUrl, $realm, $role, $secret));