<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Cli\Component\{
    Command,
    Input
};
use Virge\Stork\Service\ZMQMessagingService;
use Virge\Virge;

/**
 * listens for incoming ZMQ messages, and then broadcasts those out
 */
class RunPublishServerCommand extends Command
{
    const COMMAND = 'virge:stork:run_publish_server';
    const COMMAND_HELP = 'Listen for incoming ZMQ Messages and broadcast those to all websocket servers';
    const COMMAND_USAGE = 'virge:stork:run_publish_server';

    const QUIET_PERIOD = 5;
    
    public function run(Input $input)
    {
        if($this->instanceAlreadyRunning()) {
            Cli::error("Instance already running");
            $this->terminate(-1);
        }

        Cli::highlight(sprintf("Quiet period before starting publish server: %ss", self::QUIET_PERIOD));
        sleep(self::QUIET_PERIOD);
        Cli::important("Starting publish server");
        Virge::service(ZMQMessagingService::class)
            ->startPublishingServer();
    }
}