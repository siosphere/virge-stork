<?php

include 'services.php';

use Virge\Stork\Command\RunWebsocketServerCommand;

$cmd = new RunWebsocketServerCommand();
$cmd->run();