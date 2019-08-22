<?php
namespace DeCaro\Models;

class User {
	public $id;
	public $role;
	public $username;
	public $password;
	public $firstname;
	public $lastname;
	public $email;
	public $address;
	public $address2;
	public $city;
	public $state;
	public $zipcode;
	public $phone;

	public function __construct() {
		//update the id to integer after PDO fetch:
		$this->id = intval($this->id);
	}
}

?>