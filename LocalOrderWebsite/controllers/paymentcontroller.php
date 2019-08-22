<?php

class PaymentController extends \Dorm\Models\DormController {
	private $_repository;

	function __construct() {
		global $dorm;
		//locate the repository
		$this->_repository = $dorm->di->get('ClientRepository');
	}

	function getPaymentsForClient($clientId) {
		echo "get payments for client";
	}

}

?>