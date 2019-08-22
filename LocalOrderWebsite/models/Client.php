<?php
namespace DeCaro\Models;

class Client {
	public $id;
	public $name;
	public $client_stations;

	public function __construct() {
		//update the id to integer after PDO fetch:
		$this->id = intval($this->id);
	}
}

?>