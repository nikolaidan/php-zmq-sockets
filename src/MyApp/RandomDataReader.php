<?php

namespace MyApp;

class RandomDataReader implements DataReaderInterface {
	private $id;

	public function __construct($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function getData() {
		return ['id' => $this->getId(), 'data' => rand(10, 100)];
	}

}