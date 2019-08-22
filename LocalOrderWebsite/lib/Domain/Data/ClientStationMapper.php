<?php

namespace DeCaro\Data;
use \Dorm\Data;

class ClientStationMapper {
	protected $db; //pdo adapter
	protected $identityMap; //"cache"
	private $_table = "client_stations";

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
	    $obj = $this->db->fetch($this->_table, $id, '\DeCaro\Models\ClientStation');

	    if(!$obj)
	    	throw new \OutOfBoundsException("Could not locate that client station");

	    return $obj;
	}

	public function findAll() {
		 $objArray = $this->db->fetch($this->_table, null, '\DeCaro\Models\ClientStation');
		 return $objArray;
	}

	public function findWhere($args = array()) {
		try {
			$sql = "SELECT * FROM client_stations";

			$whereDelim = " WHERE ";
			if(isset($args['client_id'])) {
				$sql .= $whereDelim . "client_id=" . $args['client_id'];
				$whereDelim = " AND ";
			}

			$stm = $this->db->pdo->prepare($sql);

			//fetch into a class instantiation
			$stm->setFetchMode(\PDO::FETCH_CLASS, '\Decaro\Models\ClientStation');

			$stm->execute();

			$objArray = $stm->fetchAll();

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
	public function insert(\DeCaro\Models\ClientStation $obj)
	{
		$obj->id = $this->db->insert($this->_table, $obj, array('id'));
		return $obj;
	}

	public function update(\DeCaro\Models\ClientStation $obj)
	{
		$obj = $this->db->update($this->_table, $obj, array('id'));
		return $obj;
	}

	public function delete(\DeCaro\Models\ClientStation $obj)
	{
		$obj = $this->db->delete($this->_table, $obj);
		return $obj;
	}
}


?>