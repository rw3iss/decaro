<?php
namespace DeCaro\Models;

class Order {
	public $id;
	public $order_number;
	public $customer_number;
	public $status;
	public $date_created;
	public $client_id;
	public $client_station_id;

	public $shipper_enabled;
	public $shipper_name;
	public $origin_address;
	public $origin_address2;
	public $origin_city;
	public $origin_state;
	public $origin_zipcode;
	public $destination_name;
	public $destination_address;
	public $destination_address2;
	public $destination_city;
	public $destination_state;
	public $destination_zipcode;

	public $description;
	public $pieces;
	public $weight;
	public $ready_time;
	public $close_time;
	public $additional;
	public $fuel_surcharge;

	public $delivery_type;
	public $payment_type;
	public $third_party_address;
	public $third_party_address2;
	public $third_party_city;
	public $third_party_state;
	public $third_party_zipcode;
	
	public $pod_signature;
	public $pod_date;
	public $pod_total;

	public function __construct() {
		$this->id = intval($this->id);
		$this->client_id = intval($this->client_id);
		$this->client_station_id = intval($this->client_station_id);
		$this->pieces = intval($this->pieces);
		//$this->fuel_surcharge = intval($this->fuel_surcharge);

		//format the date strings
		/*
		if(!empty($this->date_created))
			$this->date_created = date_create($this->date_created)->format('n/j/Y h:i A');

		if(!empty($this->ready_time))
			$this->ready_time = date_create($this->ready_time)->format('n/j/Y h:i A');

		if(!empty($this->close_time))
			$this->close_time = date_create($this->close_time)->format('n/j/Y h:i A');
		*/
	}
}

abstract class OrderStatus {
	const INCOMPLETE = "INCOMPLETE";
	const IN_TRANSIT = "IN TRANSIT";
	const DELIVERED = "DELIVERED";
	const COMPLETE = "COMPLETE";
}

?>