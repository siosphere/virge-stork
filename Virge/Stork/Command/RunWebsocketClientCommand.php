<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Stork\Service\PushMessagingService;
use Virge\Stork\Service\WebsocketClientService;
use Virge\Virge;

/**
 * listens for incoming ZMQ messages, and then broadcasts those out
 */
class RunWebsocketClientCommand extends \Virge\Cli\Component\Command
{
    const COMMAND = 'virge:stork:run_websocket_client';
    
    public function run()
    {
        if($this->instanceAlreadyRunning()) {
            $this->terminate();
        }

        Cli::output("Starting Websocket Client");
        
        $this->getWebsocketClientService()->startClient();
    }

    public function getWebsocketClientService() : WebsocketClientService
    {
        return Virge::service(WebsocketClientService::class);
    }
}