<?php

include 'services.php';

use Virge\Cli\Component\Input;
use Virge\Stork\Command\RunWebsocketClientCommand;

$cmd = new RunWebsocketClientCommand();
$cmd->run(new Input());