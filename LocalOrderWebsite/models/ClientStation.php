<?php
namespace DeCaro\Models;

class ClientStation {
	public $id;
	public $client_id;
	public $name;
	public $address;
	public $address2;
	public $city;
	public $state;
	public $zipcode;
	public $phone_number;
	public $fax_number;

	public function __construct() {
		//update the id to integer after PDO fetch:
		$this->id = intval($this->id);
	}
}

?>