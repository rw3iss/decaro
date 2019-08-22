<?php
namespace DeCaro\Repositories;

class InvoiceRepository {
	protected $_mapper;

	/**
     * @param \Dorm\Data\SqlDatabase $database
     */
	public function __construct(\Decaro\Data\InvoiceMapper $mapper, \Decaro\Repositories\ClientRepository $clientRepo) {
		$this->_mapper = $mapper;
		$this->_clientRepo = $clientRepo;
	}

	//acts as a factory and returns a basic representation
	public function newInvoice() {
		$obj = new \DeCaro\Models\Invoice();
		$obj->id = 0;
		$obj->state = 0;

		return $obj;
	}

	public function find($id) {
		$obj = $this->_mapper->find($id);
		$obj->client = $this->_clientRepo->find($obj->client_id);

		return $obj;
	}

	public function findByInvoiceNumber($invoiceNumber) {
		$obj = $this->_mapper->findByInvoiceNumber($invoiceNumber);
		
		return $obj;
	}

	public function findWhere($args, $sortBy = null, $sortDir = null, $pagination = null) {
		global $dorm;

		// $isClientSort = false;
		// if ($sortBy != null) {
		// 	$isClientSort = strpos($sortBy, 'client.');
		// 	$clientSortBy = "none";

		// 	if($isClientSort !== false) {
		// 		$clientSortBy = substr($sortBy, 7);
		// 		$sortBy = null;
		// 	}
		// }

		$objArray = $this->_mapper->findWhere($args, $sortBy, $sortDir, $pagination);
			
		// locate client object for each order
		foreach($objArray as $obj) {
			$obj->client = $this->_clientRepo->find($obj->client_id);
		}

		// if($isClientSort !== false) {
		// 	usort($objArray, array('\DeCaro\Repositories\OrderRepository', 'sortOrders_cmp'));
		// }

		$this->lastTotalRecords = $this->_mapper->lastTotalRecords;

		return $objArray;
	}

	public function findAll() {
		$objArray = $this->_mapper->findAll();

		foreach($objArray as $obj) {
			if($obj) {
				$obj->client = $this->_clientRepo->find($obj->client_id);
			}
		}

		return $objArray;
	}

	public function save(\Decaro\Models\Invoice $obj) {
		$obj->total = str_replace('$', '', $obj->total);

		if($obj->id == 0)
			$obj = $this->_mapper->insert($obj);
		else 
			$obj = $this->_mapper->update($obj);;

		if(empty($obj->invoice_number) || trim($obj->invoice_number) == "") {
			//generate invoice number from clientID + weekstart->weekend
			$obj->invoice_number = $obj->client_id . "-" . $obj->id . "-" . date_create($obj->date_from)->format('Ynj');

			$obj = $this->_mapper->update($obj);
		}

		return $obj;
	}

	public function remove(\Decaro\Models\Invoice $obj) {
		$obj = $this->_mapper->delete($obj);
		return $obj;
	}
}

?>