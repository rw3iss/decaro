<?php

/* Emits commands to listeners */

class OrderHandler extends \Dorm\Models\DormPlugin {
	public $plugin_key = "orders";

	function startNewOrder() {
		$order = new Dorm\Models\Order();

		$order->id = 0;
		$order->status = Dorm\Model
		$order->origin_state = 0;s\OrderStatus::INCOMPLETE;
		$order->client_id = 0;
		$order->date_created = now();
		$order->fuel_surcharge = 5; //TODO: get from config

		return $order;
	}

	function saveOrder($order) {
		echo "SAVE ORDER";
		return $order;
	}
}

/*
	public $id;
	public $status;
	public $date_created;
	public $shipper_id;
	public $department;
	public $origin_address;
	public $origin_city;
	public $origin_zipcode;
	public $description;
	public $pieces;
	public $weight;
	public $ready_time;
	public $close_time;
	public $additional;
	public $fuel_surcharge;
	public $payment_type;
	public $third_party_address;
	public $third_party_city;
	public $third_party_zipcode;
*/


?>