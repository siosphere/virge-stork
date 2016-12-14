<?php

include 'services.php';

use Virge\Stork\Command\RunWebsocketClientCommand;

$cmd = new RunWebsocketClientCommand();
$cmd->run();