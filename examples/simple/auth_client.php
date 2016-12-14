<?php

include 'services.php';

use Virge\Stork\Command\RunAuthClientCommand;

$cmd = new RunAuthClientCommand();
$cmd->run();