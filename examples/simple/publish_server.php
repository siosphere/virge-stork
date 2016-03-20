<?php

include 'services.php';

use Virge\Stork\Command\RunPublishServerCommand;

$cmd = new RunPublishServerCommand();
$cmd->run();