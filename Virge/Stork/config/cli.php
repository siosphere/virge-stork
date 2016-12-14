<?php

use Virge\Cli;
use Virge\Stork\Command\RunPublishServerCommand;
use Virge\Stork\Command\RunWebsocketClientCommand;
use Virge\Stork\Command\RunAuthClientCommand;

Cli::add(RunPublishServerCommand::COMMAND, RunPublishServerCommand::class);
Cli::add(RunWebsocketClientCommand::COMMAND, RunWebsocketClientCommand::class);
Cli::add(RunAuthClientCommand::COMMAND, RunAuthClientCommand::class);