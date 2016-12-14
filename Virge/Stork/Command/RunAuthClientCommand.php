<?php

namespace Virge\Stork\Command;

use Virge\Cli;
use Virge\Stork\Service\AuthClientService;
use Virge\Virge;

/**
 * Starts the Authentication client that handles both connection,
 * and topic subscribing authentication
 */
class RunAuthClientCommand extends \Virge\Cli\Component\Command
{
    const COMMAND = 'virge:stork:run_auth_client';
    
    public function run()
    {
        if($this->instanceAlreadyRunning()) {
            $this->terminate();
        }
        
        Cli::output("starting auth client");
        Virge::service(AuthClientService::class)
            ->startClient();
    }
}