<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Stork\Service\ZMQMessagingService;

/**
 * listens for incoming ZMQ messages, and then broadcasts those out
 */
class RunPublishServerCommand
{
    const COMMAND = 'virge:stork:run_publish_server';
    
    public function run()
    {
        Cli::output("starting publish server");
        Virge::service(ZMQMessagingService::SERVICE_ID)
            ->startPublishingServer();
    }
}