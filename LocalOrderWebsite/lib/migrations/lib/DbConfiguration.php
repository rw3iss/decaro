<?php

require_once('config.php');

class DbConfiguration {
	public $dbhost;
	public $dbname;
	public $dbuser;
	public $dbpass;

	public function __construct($config = array()) {
		if(isset($config['dbhost'])) {
			$this->dbhost = $config['dbhost'];
		} else {
			throw new Exception("'dbhost' value was not set in the database configuration");
		}

		if(isset($config['dbname'])) {
			$this->dbname = $config['dbname'];
		} else {
			throw new Exception("'dbname' value was not set in the database configuration");
		}

		if(isset($config['dbuser'])) {
			$this->dbuser = $config['dbuser'];
		} else {
			throw new Exception("'dbuser' value was not set in the database configuration");
		}

		if(isset($config['dbpass'])) {
			$this->dbpass = $config['dbpass'];
		} else {
			throw new Exception("'dbpass' value was not set in the database configuration");
		}
	}
}

?>