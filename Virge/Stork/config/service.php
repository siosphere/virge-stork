<?php

use Virge\Stork\Service\PushMessagingService;
use Virge\Stork\Service\WebsocketServer;
use Virge\Stork\Service\ZMQMessagingService;
use Virge\Virge;

Virge::registerService(PushMessagingService::class, new PushMessagingService());
Virge::registerService(WebsocketServer::class, new WebsocketServer());
Virge::registerService(ZMQMessagingService::class, new ZMQMessagingService());