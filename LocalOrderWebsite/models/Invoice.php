<?php
namespace DeCaro\Models;

class Invoice {
	public $id;
	public $invoice_number;
	public $client_id;
	public $client_station_id;
	public $order_ids;
	public $date_from;
	public $date_to;
	public $total;
	public $date_due_by;
	public $date_paid;
	public $client; //loaded separately
	public $date_created;
	public $invoice_date; // shown on invoice

	public function __construct() {
		//update the id to integer after PDO fetch:
		$this->id = intval($this->id);

		//parse out order IDs string to array:
		if($this->order_ids == "")
			$this->order_ids = array();
		else
			$this->order_ids = explode(',', $this->order_ids);

		$this->order_ids = array_map(function($id) {
			return intval($id);
		}, $this->order_ids);
	}
}

?>
