<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Cli\Component\{
    Command,
    Input
};
use Virge\Stork\Service\PushMessagingService;
use Virge\Stork\Service\WebsocketClientService;
use Virge\Virge;

/**
 * listens for incoming ZMQ messages, and then broadcasts those out
 */
class RunWebsocketClientCommand extends \Virge\Cli\Component\Command
{
    const COMMAND = 'virge:stork:run_websocket_client';
    const COMMAND_HELP = 'Listen for incoming ZMQ Messages and broadcast out to their topics';
    const COMMAND_USAGE = 'virge:stork:run_websocket_client';
    
    public function run(Input $input)
    {
        if($this->instanceAlreadyRunning()) {
            Cli::error("Instance already running");
            $this->terminate(-1);
        }

        Cli::important("Starting Websocket Client");
        
        $this->getWebsocketClientService()->startClient();
    }

    public function getWebsocketClientService() : WebsocketClientService
    {
        return Virge::service(WebsocketClientService::class);
    }
}