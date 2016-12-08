<?php

include 'services.php';

use Virge\Stork;

$message = new MyMessage();

Stork::push(['v1.test.1'], $message);