<?php

use Virge\Cli;
use Virge\Stork\Command\RunPublishServerCommand;
use Virge\Stork\Command\RunWebsocketServerCommand;

Cli::add(RunPublishServerCommand::COMMAND, RunPublishServerCommand::class);
Cli::add(RunWebsocketServerCommand::COMMAND, RunWebsocketServerCommand::class);