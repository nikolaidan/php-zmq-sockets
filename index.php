<?php

use MyApp\Server;
use MyApp\Client;
use MyApp\DataReaderInterface;
use MyApp\RandomDataReader;

require __DIR__ . '/vendor/autoload.php';

const BUFFER_SIZE = 128;

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

	$errno = null;
	$errstr = null;
	$dataSocket = stream_socket_client("unix:///tmp/unix-socket", $errno, $errstr, null, STREAM_CLIENT_CONNECT);

	if (!$dataSocket) {
		echo "ERROR: $errno - $errstr<br />\n";
		exit(1);
	} {
		$welcomeMsg = trim(fread($dataSocket, BUFFER_SIZE));
		echo 'Welcome to: ' . $welcomeMsg . '!' . PHP_EOL;
	}

	$dataReader = new RandomDataReader(1, $dataSocket);
	$dataReader2 = new RandomDataReader(2, $dataSocket);

	$client->addDataReader($dataReader);
	$client->addDataReader($dataReader2);

	$client->run();
}

