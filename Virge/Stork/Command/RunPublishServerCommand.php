<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Stork\Service\ZMQMessagingService;
use Virge\Virge;

/**
 * listens for incoming ZMQ messages, and then broadcasts those out
 */
class RunPublishServerCommand extends \Virge\Cli\Component\Command
{
    const COMMAND = 'virge:stork:run_publish_server';
    
    public function run()
    {
        if($this->instanceAlreadyRunning()) {
            $this->terminate();
        }
        
        Cli::output("starting publish server");
        Virge::service(ZMQMessagingService::SERVICE_ID)
            ->startPublishingServer();
    }
}