<?php

namespace DeCaro\Data;
use \Dorm\Data;

class InvoiceMapper {
	protected $db; //pdo adapter
	protected $identityMap; //"cache"
	private $_table = "invoices";

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
	    $obj = $this->db->fetch($this->_table, $id, '\DeCaro\Models\Invoice');

	    if(!$obj)
	    	throw new \OutOfBoundsException("Could not locate that Invoice");

	    if(is_string($obj->order_ids))
	    	$obj->order_ids = explode(',', $obj->order_ids);

	    return $obj;
	}

	public function findAll() {
		 $objArray = $this->db->fetch($this->_table, null, '\DeCaro\Models\Invoice');

		 foreach($objArray as $obj) {
	    	if(is_string($obj->order_ids))
	    		$obj->order_ids = explode(',', $obj->order_ids);
		 }

		 return $objArray;
	}
	
	public function findByInvoiceNumber($invoiceNumber) {
		try {
			$sql = sprintf("SELECT * FROM invoices WHERE invoice_number='%s'", $invoiceNumber);

			$stm = $this->db->pdo->prepare($sql);

			//fetch into a class instantiation
			$stm->setFetchMode(\PDO::FETCH_CLASS, '\DeCaro\Models\Invoice');

			$stm->execute();

			$objArray = $stm->fetchAll();

			if(sizeof($objArray) > 0) {
				return $objArray[0];
			} else {
				return null;
			}
		} catch(PDOException $ex) {
			echo "PDO EXCEPTION on fetchByInvoiceNumber";
			throw $ex;
		}
	}

	public function findWhere($args = array(), $sortBy = null, $sortDir = null, $pagination = null) {
		try {
			$sql = "SELECT ";
			if($pagination != null) {
				$sql .= "SQL_CALC_FOUND_ROWS ";
			}

			$sql .= " * FROM invoices";

			$whereDelim = " WHERE ";
			if(isset($args['id'])) {
				if(is_array($args['id'])) {
					$sql .= $whereDelim . "id in (" . implode(', ', $args['id']) . ")";
				} else {
					$sql .= $whereDelim . "id=" . $args['id'];
				}
				$whereDelim = " AND ";
			}

			// if(isset($args['client_name'])) {
			// 	$sql .= $whereDelim . "client_id=" . $args['client_id'];
			// 	$whereDelim = " AND ";
			// }

			if(isset($args['invoice_number'])) {
				$sql .= $whereDelim . "lower(invoice_number) LIKE '%" . strtolower($args['invoice_number']) . "%'";
				$whereDelim = " AND ";
			}

			if(isset($args['status'])) {	
				if ($args['status'] == 'paid') {
					$sql .= $whereDelim . " date_paid IS NOT NULL";
					$whereDelim = " AND ";
				} else if ($args['status'] == 'unpaid') {
					$sql .= $whereDelim . " date_paid IS NULL";
					$whereDelim = " AND ";
				}
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
			$stm->setFetchMode(\PDO::FETCH_CLASS, '\DeCaro\Models\Invoice');

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

	/**
	* @param Invoice $invoice
	* @throws MapperException
	* @return integer A lastInsertId.
	*/
	public function insert(\DeCaro\Models\Invoice $obj)
	{
		//convert order_ids array to string:
		$orderIds = '';
		$delim = '';
		if($obj->order_ids) {
			foreach($obj->order_ids as $id) {
				$orderIds .= $delim . $id;
				$delim = ',';
			}
		}

		$obj->order_ids = $orderIds;

		$obj->id = $this->db->insert($this->_table, $obj, array('id', 'client'));

	    $obj->order_ids = explode(',', $obj->order_ids);

		return $obj;
	}

	public function update(\DeCaro\Models\Invoice $obj)
	{
		//convert order_ids array to string:
		$orderIds = '';
		$delim = '';
		if($obj->order_ids) {
			foreach($obj->order_ids as $id) {
				$orderIds .= $delim . $id;
				$delim = ',';
			}
		}

		$obj->order_ids = $orderIds;

		$obj = $this->db->update($this->_table, $obj, array('id', 'client'));

	    $obj->order_ids = explode(',', $obj->order_ids);
	    array_map(function($o) {
	    	$o = intval($o);
	    }, $obj->order_ids);

		return $obj;
	}

	public function delete(\DeCaro\Models\Invoice $obj)
	{
        $obj = $this->db->delete($this->_table, $obj);
        return $obj;
	}
}


?>