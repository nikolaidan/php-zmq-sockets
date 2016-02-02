<?php

namespace MyApp;

define("REQUEST_TIMEOUT", 2500); //  msecs, (> 1000!)
define("REQUEST_RETRIES", 4); //  Before we abandon

use \ZMQContext;
use \ZMQSocket;
use \ZMQPoll;
use \ZMQ;

class Client extends ServerClientBase {
	private $context;
	private $readers = [];

	public function __construct(\ZMQContext $context, $location) 
	{
		$this->context = $context;
		$this->location = $location;
    }

	public function addDataReader(DataReaderInterface $reader) {
		$this->readers[] = $reader;
	}

	public function run()
	{
		$client = $this->getClient($this->context, $this->location);

		$sequence = 0;
		$retries_left = REQUEST_RETRIES;
		$read = $write = array();

		while ($retries_left) {

			$dataToSend = [];
			foreach($this->readers as $reader) {
				$dataToSend[] = $reader->getData();
			}
			$msg = $this->buildMessage(++$sequence, $dataToSend);

			//  We send a request, then we work to get a reply
			$client->send($msg);

			$expect_reply = true;
			while ($expect_reply) {
				//  Poll socket for a reply, with timeout
				$poll = new ZMQPoll();
				$poll->add($client, ZMQ::POLL_IN);
				$events = $poll->poll($read, $write, REQUEST_TIMEOUT);

				//  If we got a reply, process it
				if ($events > 0) {
					//  We got a reply from the server, must match sequence
					$reply = $this->decodeReply($client->recv());
					if (intval($reply['seq']) == $sequence) {
						printf ("I: server replied OK (%s)%s", $this->multiImplode($reply['msg'], ','), PHP_EOL);
						$retries_left = REQUEST_RETRIES;
						$expect_reply = false;
					} else {
						printf ("E: malformed reply from server: %s%s", $this->multiImplode($reply['msg'], ','), PHP_EOL);
					}
				} elseif (--$retries_left == 0) {
					echo "E: server seems to be offline, abandoning", PHP_EOL;
					break;
				} else {
					echo "W: no response from server, retrying…", PHP_EOL;
					//  Old socket will be confused; close it and open a new one
					$client = $this->getClient($this->context, $this->location);
					//  Send request again, on new socket
					$client->send($msg);
				}
			}
		}
	}


	private function getClient($context, $location)
	{
		echo "I: connecting to server...", PHP_EOL;
		$client = new ZMQSocket($context, ZMQ::SOCKET_REQ);
		$client->connect($location);

		//  Configure socket to not wait at close time
		$client->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);

		return $client;
	}
}
