<?php
namespace DeCaro\Repositories;

class ClientRepository {
	protected $_mapper;
	protected $_stationRepository;
	
	public $lastTotalRecords = 0;

	/**
     * @param \Dorm\Data\SqlDatabase $database
     */
	public function __construct(\Decaro\Data\ClientMapper $mapper) {//, \Decaro\Data\ClientStationMapper $mapper) {
		global $dorm;
		$this->_mapper = $mapper;
		$this->_stationRepository = $dorm->di->get('ClientStationRepository');
	}

	//acts as a factory and returns a basic representation
	public function newClient() {
		$client = new \DeCaro\Models\Client();
		$client->name = 'Untitled';
		$client->id = 0;
		$client->client_stations = array();

		$client = $this->_mapper->insert($client);

		return $client;
	}

	public function find($id) {
		$obj = $this->_mapper->find($id);

		if($obj) {
			//load stations for this client:
			$obj->client_stations = $this->_stationRepository->findForClient($id);
		}

		return $obj;
	}

	public function findAll() {
		$objArray = $this->_mapper->findAll();

		foreach($objArray as $obj) {
			if($obj) {
				//load stations for this client:
				$obj->client_stations = $this->_stationRepository->findForClient($obj->id);
			}
		}

		return $objArray;
	}

	public function findWhere($args, $sortBy = null, $sortDir = null, $pagination = null) {
		global $dorm;
		//echo 'fw'.print_r($args,true);
		$objArray = $this->_mapper->findWhere($args, $sortBy, $sortDir, $pagination);

		foreach($objArray as $obj) {
			if($obj) {
				//load stations for this client:
				$obj->client_stations = $this->_stationRepository->findForClient($obj->id);
			}
		}
		
		$this->lastTotalRecords = $this->_mapper->lastTotalRecords;

		return $objArray;
	}

	public function save(\Decaro\Models\Client $obj) {
		if($obj->id == 0)
			$obj = $this->_mapper->insert($obj);
		else 
			$obj = $this->_mapper->update($obj);

		return $obj;
	}

	public function remove(\Decaro\Models\Client $obj) {
		$obj = $this->_mapper->delete($obj);
		return $obj;
	}
}

?>