<?php

class ClientController extends \Dorm\Models\DormController {
	private $_repository;

	function __construct() {
		global $dorm;
		//locate the repository
		$this->_repository = $dorm->di->get('ClientRepository');
		$this->_stationRepository = $dorm->di->get('ClientStationRepository');
	}

	function getClient($id) {
		try {
			$client = $this->_repository->find($id);

			if($client == null) {
				//TODO: throw not found 
			}

			echo json_encode($client);
		} catch(\OutOfBoundsException $ex) {
			\Dorm\Util\Header::notFoundError();

			echo json_encode(array('status'=>'error', 'message'=>'Could not locate that client.'));
		}
	}


	function getAllClients() {
		global $dorm;
		$sortBy = $dorm->input->get('sortBy');
		$sortDir = $dorm->input->get('sortDir');

		$filter = $dorm->input->get('filter');

		if($filter) {
			if(isset($filter['clientName']))
				if($filter['clientName'] == '')
					unset($filter['clientName']);
		}

		//default pagination
		$paginationData = null;// array('resultsPerPage' => 25, 'page' => 1);

		$pagination = $dorm->input->get('pagination');
		if($pagination)
			$paginationData = $pagination;

		$clients = $this->_repository->findWhere($filter, $sortBy, $sortDir, $paginationData);

		$totalRecords = $this->_repository->lastTotalRecords;

		$results = array(
			'totalRecords' => $totalRecords,
			'clients' => $clients,
			'pagination' => $paginationData
			);

		echo json_encode($results);
	}

	//fulfill a service request
	function startNewClient() {
		$client = $this->_repository->newClient();

		echo json_encode($client);
	}

	//fulfill a service request
	function saveClient($id) {
		//grab from request
		$client = new \DeCaro\Models\Client();
		$client = fill_object($client);

		//save the instance
		$client = $this->_repository->save($client);

		echo json_encode($client);
	}

	//fulfill a service request
	function removeClient($id) {
		//grab from request
		$client = $this->_repository->find($id);

		if($client == null) {
			//TODO: throw not found 
			throw new \Exception("Could not locate that client");
		}

		//save the instance
		$client = $this->_repository->remove($client);

		echo json_encode($client);
	}

	//fulfill a service request
	function saveClientStation($id) {
		//grab from request
		$client = new \DeCaro\Models\ClientStation();
		$client = fill_object($client);

		//save the instance
		$client = $this->_stationRepository->save($client);

		echo json_encode($client);
	}

	//fulfill a service request
	function removeClientStation($id) {
		global $dorm;
		
		//grab from request
		$clientStation = $this->_stationRepository->find($id);

		if($clientStation == null) {
			//TODO: throw not found 
			throw new \Exception("Could not locate that client station");
		}

		$this->_stationRepository->remove($clientStation);

		$dorm->response->success_response();
	}

	function getStationsForClient($clientId) {
		global $dorm;

		//grab from request
		$stations = $this->_stationRepository->findForClient($clientId);

		echo json_encode($stations);
	}
}

?>