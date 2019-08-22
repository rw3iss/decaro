<?php
namespace DeCaro\Repositories;

class ClientStationRepository {
	protected $_mapper;

	/**
     * @param \Dorm\Data\SqlDatabase $database
     */
	public function __construct(\Decaro\Data\ClientStationMapper $mapper) {
		$this->_mapper = $mapper;
	}

	//acts as a factory and returns a basic representation
	public function newClientStation() {
		$client = new \DeCaro\Models\ClientStation();
		$client->id = 0;
		$client->state = 0;
		$client->client_stations = array();

		return $client;
	}

	public function find($id) {
		$obj = $this->_mapper->find($id);
		return $obj;
	}

	public function findForClient($id) {
		$obj = $this->_mapper->findWhere(array("client_id" => $id));
		return $obj;
	}

	public function findAll() {
		$objArray = $this->_mapper->findAll();
		return $objArray;
	}

	public function save(\Decaro\Models\ClientStation $obj) {
		if($obj->id == 0)
			$obj = $this->_mapper->insert($obj);
		else 
			$obj = $this->_mapper->update($obj);

		return $obj;
	}

	public function remove(\Decaro\Models\ClientStation $obj) {
		$obj = $this->_mapper->delete($obj);
		return $obj;
	}
}

?>