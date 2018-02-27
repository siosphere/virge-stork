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

$websocketUrl = Config::get('stork', 'websocket_url');
$realm = Config::get('stork', 'realm');
$role = Config::get('stork', 'role');
$secret = Config::get('stork', 'secret');


Virge::registerService(RedisProvider::class, RedisProvider::class);
Virge::registerService(ZMQProvider::class, ZMQProvider::class);
Virge::registerService(PubSubService::class, function() {
    $pubSubProvider = Config::get('stork', 'pub_sub_provider');
    if(!$pubSubProvider) {
        $pubSubProvider = ZMQProvider::class;
    }

    return new PubSubService($pubSubProvider);
});

Virge::registerService(PushMessagingService::class, function() {

    $websocketHostname = Config::get('stork', 'websocket_hostname');

    return new PushMessagingService($websocketHostname);
});

Virge::registerService(WebsocketClientService::class, function() use($websocketUrl, $realm, $role, $secret) {
    return new WebsocketClientService($websocketUrl, $realm, $role, $secret);
});

Virge::registerService(MetaClientService::class, function() use($websocketUrl, $realm, $role, $secret) {
    return new MetaClientService($websocketUrl, $realm, $role, $secret);
});

Virge::registerService(AuthClientService::class, function() use($websocketUrl, $realm, $role, $secret) {
    return new AuthClientService($websocketUrl, $realm, $role, $secret);
});

Virge::registerService(RPCProviderService::class, function() use($websocketUrl, $realm, $role, $secret) {
    return new RPCProviderService($websocketUrl, $realm, $role, $secret);
});

Virge::registerService(RPCClientService::class, function() use($websocketUrl, $realm, $role, $secret) {
    return new RPCClientService($websocketUrl, $realm, $role, $secret);
});