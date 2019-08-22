<?php
namespace DeCaro\Models;

class Setting {
	public $id;
	public $name;
	public $value;

	public function __construct() {
		//update the id to integer after PDO fetch:
		$this->id = intval($this->id);
	}
}

?>