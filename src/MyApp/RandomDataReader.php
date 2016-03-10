<?php

namespace MyApp;

class RandomDataReader implements DataReaderInterface {
	private $id;
	private $connector;

	public function __construct($id, $connector) {
		$this->id = $id;
		$this->connector = $connector;
	}

	public function getId() {
		return $this->id;
	}

	public function getData() {
		fwrite($this->connector, "getData " . (int)$this->id);

		$retval = trim(fread($this->connector, BUFFER_SIZE));
		return $retval;
	}

}