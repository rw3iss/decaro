<?php
namespace DeCaro\Repositories;

class OrderRepository {
	protected $_mapper;
	protected $_clientMapper;

	public $lastTotalRecords = 0;

	/**
     * @param \Dorm\Data\SqlDatabase $database
     */
	public function __construct(\Decaro\Data\OrderMapper $mapper, \Decaro\Data\ClientMapper $clientMapper) {
		$this->_mapper = $mapper;
		$this->_clientMapper = $clientMapper;
	}

	//acts as a factory and returns a basic representation
	public function newOrder() {
		$obj = new \DeCaro\Models\Order();
		$obj->id = 0;
		$obj->status = \DeCaro\Models\OrderStatus::INCOMPLETE;
		$obj->client_id = 0;
		$obj->client_station_id = 0;
		$obj->origin_state = 0;
		$obj->destination_state = 0;
		$obj->third_party_state = 0;
		//$obj->date_created = now();
		$obj->payment_type = 'collect';

		return $obj;
	}

	public function find($id) {
		$obj = $this->_mapper->find($id);

		//locate client
		$obj->client = $this->_clientMapper->find($obj->client_id);

		return $obj;
	}

	public function findByOrderNumber($orderNumber) {
		$obj = $this->_mapper->findByOrderNumber($orderNumber);
		return $obj;
	}

	public function findWhere($args, $sortBy = null, $sortDir = null, $pagination = null) {
		global $dorm;

		$isClientSort = false;
		if ($sortBy != null) {
			//echo "sort: " . $sortBy;
			$isClientSort = strpos($sortBy, 'client.');

			$clientSortBy = "none";

			if($isClientSort !== false) {
				$clientSortBy = substr($sortBy, 7);
				$sortBy = null;
			}
		}

		$objArray = $this->_mapper->findWhere($args, $sortBy, $sortDir, $pagination);
			
		// locate client object for each order
		foreach($objArray as $obj) {
			$obj->client = $this->_clientMapper->find($obj->client_id);
		}

		if($isClientSort !== false) {
			//echo "CLIENT SORT: " . $sortBy;
			usort($objArray, array('\DeCaro\Repositories\OrderRepository', 'sortOrders_cmp'));
		}

		$this->lastTotalRecords = $this->_mapper->lastTotalRecords;

		return $objArray;
	}

	public static function sortOrders_cmp($a,$b) {
		if($a == null || $b=null || !is_object($a) || !is_object($b)) {
			return 1;	
		}


		if($a->client != null) {
			if($b->client != null) {
	    		return strcmp($a->client->name, $b->client->name);
			}
			return 0;
		}
		return 1;	
	}

	public function findAll($sortBy = null, $sortDir = null, $filter) {
		$objArray = $this->_mapper->findAll($sortBy, $sortDir, $filter);

		foreach($objArray as $obj) {
			//locate client
			$obj->client = $this->_clientMapper->find($obj->client_id);
		}
		
		return $objArray;
	}

	public function save(\Decaro\Models\Order $obj) {
		if($obj->id == 0)
			$obj = $this->_mapper->insert($obj);
		else 
			$obj = $this->_mapper->update($obj);

		// generate a new order number if it's empty
		if(empty($obj->order_number)) {
			// 408 is arbitrary number .. was current last insert ID when I made this change. 
			// There was a request to start order numbers at 100150
			$obj->order_number = (100150 + $obj->id - 408);
			// $obj->order_number =  $obj->client_id . '-' . $obj->id . '-' .
			// 	date_create($obj->date_created)->format('ymd');
			$obj = $this->_mapper->update($obj);
		}

		return $obj;
	}

	public function remove(\Decaro\Models\Order $obj) {
		$obj = $this->_mapper->delete($obj);
		return $obj;
	}
}

?>