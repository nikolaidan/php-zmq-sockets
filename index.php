<?php

use MyApp\Server;
use MyApp\Client;
use MyApp\DataReaderInterface;
use MyApp\RandomDataReader;

require __DIR__ . '/vendor/autoload.php';

if(!isset($argv[1]) || empty($mode = $argv[1])) {
	echo 'Server or client?' . PHP_EOL;
	exit;
}

switch($mode) {
	case 'server':
		startServer();
		break;

	case 'client': default:
		startClient();
		break;
}

function startServer() {
	$context = new ZMQContext();
	$server = new Server($context);
	$server->run();
}

function startClient() {
	$context = new ZMQContext();
	$client = new Client($context, "tcp://localhost:5555");

	$dataReader = new RandomDataReader(69);

	$client->addDataReader($dataReader);
	$client->run();
}

