<?php

namespace MyApp;

use \ZMQContext;
use \ZMQSocket;
use \ZMQ;

class Server extends ServerClientBase {
	private $context;
	private $server;

	public function __construct(ZMQContext $context) 
	{
		$this->context = $context;
		$this->server = new ZMQSocket($this->context, ZMQ::SOCKET_REP);
		$this->server->bind("tcp://*:5555");
		printf ("Bound" . PHP_EOL);
		flush();
    }

	public function run()
	{
		printf ("Running..." . PHP_EOL);
        flush();

		$cycles = 0;
		while (true) {
			$request = $this->decodeReply($this->server->recv());
			$cycles++;


			//  Simulate various problems, after a few cycles
			/*if ($cycles > 3 && rand(0, 20) == 0) {
				echo "I: simulating a crash", PHP_EOL;
				break;
			} elseif ($cycles > 3 && rand(0, 20) == 0) {
				echo "I: simulating CPU overload", PHP_EOL;
				sleep(5);
			}*/
			printf ("I: normal request (%s)%s", $this->multiImplode($request['msg'], ' -> '), PHP_EOL);
			flush();
			sleep(1);

			$response = $this->buildMessage($request['seq'], ['info' => 'OK!']);

			$this->server->send($response);
		}
	}
}
