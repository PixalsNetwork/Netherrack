<?php

require_once __DIR__ . "/vendor/autoload.php";

use Nether\ProxyServer;

$server = new ProxyServer();


$server->start();

