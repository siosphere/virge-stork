<?php
include 'services.php';

use Virge\Cli\Component\Input;
use Virge\Stork\Command\RunPublishServerCommand;

$cmd = new RunPublishServerCommand();
$cmd->run(new Input());