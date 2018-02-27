<?php

include 'services.php';

use Virge\Cli\Component\Input;
use Virge\Stork\Command\RunAuthClientCommand;

$cmd = new RunAuthClientCommand();
$cmd->run(new Input());