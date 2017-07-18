<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Cli\Component\{
    Command,
    Input
};
use Virge\Stork\Service\PushMessagingService;
use Virge\Stork\Service\MetaClientService;
use Virge\Virge;

/**
 * Emits events when clients join or leave the websocket service
 */
class RunMetaClientCommand extends \Virge\Cli\Component\Command
{
    const COMMAND = 'virge:stork:run_meta_client';
    const COMMAND_HELP = 'Subscribes to WAMP Meta events and emits Virge Events on clients joining and leaving';
    const COMMAND_USAGE = 'virge:stork:run_meta_client';
    
    public function run(Input $input)
    {
        if($this->instanceAlreadyRunning()) {
            Cli::error("Instance already running");
            $this->terminate(-1);
        }

        Cli::important("Starting Meta Client");
        
        $this->getMetaClientService()->startClient();
    }

    public function getMetaClientService() : MetaClientService
    {
        return Virge::service(MetaClientService::class);
    }
}