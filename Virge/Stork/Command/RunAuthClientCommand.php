<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Cli\Component\{
    Command,
    Input
};
use Virge\Stork\Service\AuthClientService;
use Virge\Virge;

/**
 * Starts the Authentication client that handles both connection,
 * and topic subscribing authentication
 */
class RunAuthClientCommand extends Command
{
    const COMMAND = 'virge:stork:run_auth_client';
    const COMMAND_HELP = 'Start the Authentication client that handles both connection, and topic subscribing authentication';
    const COMMAND_USAGE = 'virge:stork:run_auth_client';
    
    public function run(Input $input)
    {
        if($this->instanceAlreadyRunning()) {
            Cli::error('Auth client already running');
            $this->terminate(-1);
        }
        
        Cli::important("Starting auth client");
        Virge::service(AuthClientService::class)
            ->startClient();
    }
}