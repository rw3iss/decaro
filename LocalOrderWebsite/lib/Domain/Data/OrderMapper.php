<?php

namespace DeCaro\Data;
use \Dorm\Data;

class OrderMapper {
	protected $db; //pdo adapter
	protected $identityMap; //"cache"
	private $_table = "orders";

	//for retrieving SELECT FOUND_ROWS() of the last query
	public $lastTotalRecords = 0;

	public function __construct(\Dorm\Data\PdoDatabaseAdapter $db, \Dorm\Data\IdentityMap $identityMap) {
		$this->db = $db;
		$this->identityMap = $identityMap;
	}

	public function find($id) {
		$obj = $this->identityMap->get($id);

	    if ($obj) {
	      return $obj;
	    }

	    //fetch from database
	    $obj = $this->db->fetch($this->_table, $id, '\DeCaro\Models\Order');

	    if(!$obj)
	    	throw new \OutOfBoundsException("Could not locate that Order");

	    return $obj;
	}

	public function findWhere($args = array(), $sortBy = null, $sortDir = null, $pagination = null) {
		try {
			$sql = "SELECT ";
			if($pagination != null) {
				$sql .= "SQL_CALC_FOUND_ROWS ";
			}

			$sql .= " * FROM orders";

			$whereDelim = " WHERE ";
			if(isset($args['id'])) {
				if(is_array($args['id'])) {
					$sql .= $whereDelim . "id in (" . implode(', ', $args['id']) . ")";
				} else {
					$sql .= $whereDelim . "id=" . $args['id'];
				}
				$whereDelim = " AND ";
			}

			if(isset($args['client_id'])) {
				$sql .= $whereDelim . "client_id=" . $args['client_id'];
				$whereDelim = " AND ";
			}

			if(isset($args['client_station_id'])) {
				$sql .= $whereDelim . "client_station_id=" . $args['client_station_id'];
				$whereDelim = " AND ";
			}

			if(isset($args['order_number'])) {
				$sql .= $whereDelim . "lower(order_number) LIKE '%" . strtolower($args['order_number']) . "%'";
				$whereDelim = " AND ";
			}

			if(isset($args['status'])) {	
				$sql .= $whereDelim . "status='" . $args['status'] . "'";
				$whereDelim = " AND ";
			}

			if(isset($args['date_from'])) {
				$sql .= $whereDelim . "DATE(date_created) >= '" . $args['date_from'] . "'";
				$whereDelim = " AND ";
			}

			if(isset($args['date_to'])) {
				$sql .= $whereDelim . "DATE(date_created) <= '" . $args['date_to'] . "'";
			}

			if($sortBy != null) {
				$sql .= " ORDER BY " . $sortBy;
				if($sortDir != null)
					$sql .= " " . $sortDir;
				$sql .= ", id desc ";
			} else {
				$sql .= " ORDER BY id desc ";
			}

			if($pagination != null) {
				$page = intval($pagination['page']) - 1;
				$resultsPerPage = intval($pagination['resultsPerPage']);
				$startIndex = $page * $resultsPerPage;
				$endIndex = $startIndex + $resultsPerPage;
				$sql .= " LIMIT $startIndex,$endIndex";
			}

			$stm = $this->db->pdo->prepare($sql);

			//fetch into a class instantiation
			$stm->setFetchMode(\PDO::FETCH_CLASS, '\DeCaro\Models\Order');

			$stm->execute();

			$objArray = $stm->fetchAll();

			//echo "Got result: " . sizeof($objArray);

			if($pagination != null) {
				$totalRecords = $this->db->query("SELECT FOUND_ROWS()");
				$this->lastTotalRecords = intval($totalRecords[0][0]);
			}

			return $objArray;
		} catch(PDOException $ex) {
			echo "PDO EXCEPTION on fetchWhere";
			throw $ex;
		}
	}

	public function findByOrderNumber($orderNumber) {
		try {
			$sql = sprintf("SELECT * FROM orders WHERE order_number='%s'", $orderNumber);

			$stm = $this->db->pdo->prepare($sql);

			//fetch into a class instantiation
			$stm->setFetchMode(\PDO::FETCH_CLASS, '\DeCaro\Models\Order');

			$stm->execute();

			$objArray = $stm->fetchAll();

			if(sizeof($objArray) > 0) {
				return $objArray[0];
			} else {
				return null;
			}
		} catch(PDOException $ex) {
			echo "PDO EXCEPTION on fetchByOrderNumber";
			throw $ex;
		}
	}

	public function findAll($sortBy = null, $sortDir = null, $filter) {
		 $objArray = $this->db->fetch($this->_table, null, '\DeCaro\Models\Order');
		 return $objArray;
	}
	
	/**
	* @param User $user
	* @throws MapperException
	* @return integer A lastInsertId.
	*/
	public function insert(\DeCaro\Models\Order $obj)
	{
		//format datetimes
		//$date = date_create($obj->date_created);// \DateTime::createFromFormat('M/d/Y h:i A', $obj->date_created);
		//$obj->date_created = $date->format('Y-m-d H:i:s');
		$obj->date_created = date_create($obj->date_created)->format('Y-m-d H:i:s');
		$obj->ready_time = date_create($obj->ready_time)->format('Y-m-d H:i:s');
		$obj->close_time = date_create($obj->close_time)->format('Y-m-d H:i:s');

		if(!empty($obj->pod_date))
			$obj->pod_date = date_create($obj->pod_date)->format('Y-m-d H:i:s');

		$obj->id = $this->db->insert($this->_table, $obj, array('id', 'client'));
		
		return $obj;
	}

	public function update(\DeCaro\Models\Order $obj)
	{
		//format datetimes
		$obj->date_created = date_create($obj->date_created)->format('Y-m-d H:i:s');
		$obj->ready_time = date_create($obj->ready_time)->format('Y-m-d H:i:s');
		$obj->close_time = date_create($obj->close_time)->format('Y-m-d H:i:s');

		if(!empty($obj->pod_date))
			$obj->pod_date = date_create($obj->pod_date)->format('Y-m-d H:i:s');

		$obj = $this->db->update($this->_table, $obj, array('id', 'client'));

		return $obj;
	}

	public function delete(\DeCaro\Models\Order $obj)
	{
		$obj = $this->db->delete($this->_table, $obj);
		return $obj;
	}
}


?>