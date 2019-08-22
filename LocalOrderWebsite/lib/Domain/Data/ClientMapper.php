<?php

namespace DeCaro\Data;
use \Dorm\Data;

class ClientMapper {
	protected $db; //pdo adapter
	protected $identityMap; //"cache"
	private $_table = "clients";

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
	    $obj = $this->db->fetch($this->_table, $id, '\DeCaro\Models\Client');

	    // if(!$obj)
	    // 	throw new \OutOfBoundsException("Could not locate that Client");

	    return $obj;
	}

	public function findAll() {
		 $objArray = $this->db->fetch($this->_table, null, '\DeCaro\Models\Client');
		 return $objArray;
	}
	
	public function findWhere($args = array(), $sortBy = null, $sortDir = null, $pagination = null) {
		try {
			$sql = "SELECT ";
			if($pagination != null) {
				$sql .= "SQL_CALC_FOUND_ROWS ";
			}

			$sql .= " * FROM clients";

			$whereDelim = " WHERE ";
			if(isset($args['clientName'])) {
				$sql .= $whereDelim . "lower(name) LIKE lower('%" . $args['clientName'] . "%')";
			}

			//if($sortBy != null) {
				$sql .= " ORDER BY " . $sortBy;
				if ($sortDir != null)
					$sql .= " ASC";// . $sortDir;
			//}

			if($pagination != null) {
				$page = intval($pagination['page']) - 1;
				$resultsPerPage = intval($pagination['resultsPerPage']);
				$startIndex = $page * $resultsPerPage;
				$endIndex = $startIndex + $resultsPerPage;
				$sql .= " LIMIT $startIndex,$endIndex";
			}

			$stm = $this->db->pdo->prepare($sql);

			//fetch into a class instantiation
			$stm->setFetchMode(\PDO::FETCH_CLASS, '\DeCaro\Models\Client');

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
	* @param User $user
	* @throws MapperException
	* @return integer A lastInsertId.
	*/
	public function insert(\DeCaro\Models\Client $obj)
	{
		$obj->id = $this->db->insert($this->_table, $obj, array('id', 'client_stations'));
		return $obj;
	}

	public function update(\DeCaro\Models\Client $obj)
	{
		$obj = $this->db->update($this->_table, $obj, array('id', 'client_stations'));
		return $obj;
	}

	public function delete(\DeCaro\Models\Client $obj)
	{
        $obj = $this->db->delete($this->_table, $obj);
        return $obj;
	}
}


?>