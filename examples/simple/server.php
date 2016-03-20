<?php

include 'services.php';

use Virge\Stork\Command\RunPushServerCommand;

$cmd = new RunPushServerCommand();
$cmd->run();