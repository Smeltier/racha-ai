<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Session;

date_default_timezone_set('America/Sao_Paulo');

$session = new Session();
$session->start();
