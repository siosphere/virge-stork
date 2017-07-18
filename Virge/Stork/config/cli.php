<?php

use Virge\Cli;
use Virge\Stork\Command\RunPublishServerCommand;
use Virge\Stork\Command\RunWebsocketClientCommand;
use Virge\Stork\Command\RunAuthClientCommand;
use Virge\Stork\Command\RunMetaClientCommand;

Cli::add(RunPublishServerCommand::COMMAND, RunPublishServerCommand::class)
    ->setHelpText(RunPublishServerCommand::COMMAND_HELP)
    ->setUsage(RunPublishServerCommand::COMMAND_USAGE)
;

Cli::add(RunWebsocketClientCommand::COMMAND, RunWebsocketClientCommand::class)
    ->setHelpText(RunWebsocketClientCommand::COMMAND_HELP)
    ->setUsage(RunWebsocketClientCommand::COMMAND_USAGE)
;

Cli::add(RunAuthClientCommand::COMMAND, RunAuthClientCommand::class)
    ->setHelpText(RunAuthClientCommand::COMMAND_HELP)
    ->setUsage(RunAuthClientCommand::COMMAND_USAGE)
;

Cli::add(RunMetaClientCommand::COMMAND, RunMetaClientCommand::class)
    ->setHelpText(RunMetaClientCommand::COMMAND_HELP)
    ->setUsage(RunMetaClientCommand::COMMAND_USAGE)
;