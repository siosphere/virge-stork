<?php

use Virge\Stork\Service\ZMQMessagingService;
use Virge\Virge;

Virge::registerService(ZMQMessagingService::SERVICE_ID, new ZMQMessagingService());