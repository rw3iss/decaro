<?php

class OrderController extends \Dorm\Models\DormController {
	private $_repository;
	private $_clientRepository;

	function __construct() {
		global $dorm;
		//locate the repository
		$this->_repository = $dorm->di->get('OrderRepository');
		$this->_clientStationRepository = $dorm->di->get('ClientStationRepository');
		$this->_mapper = $dorm->di->get('OrderMapper');
	}

	function getOrder($id) {
		try {
			$order = $this->_repository->find($id);

			if($order == null) {
				//TODO: throw not found 
			}

			echo json_encode($order);
		} catch(\OutOfBoundsException $ex) {
			\Dorm\Util\Header::notFoundError();

			echo json_encode(array('status'=>'error', 'message'=>'Could not locate that order.'));
		}
	}


	function getAllOrders() {
		global $dorm;

		$sortBy = $dorm->input->get('sortBy');
		$sortDir = $dorm->input->get('sortDir');

		$filter = $dorm->input->get('filter');

		if($filter) {
			if(isset($filter['client_id']))
				if($filter['client_id'] == '0')
					unset($filter['client_id']);

			if(isset($filter['order_number']))
				if($filter['order_number'] == '')
					unset($filter['order_number']);

			if(isset($filter['date_from']))
				if($filter['date_from'] == '')
					unset($filter['date_from']);
				else 
					$filter['date_from'] = date_create($filter['date_from'])->format('Y-n-j');

			if(isset($filter['date_to']))
				if($filter['date_to'] == '')
					unset($filter['date_to']);
				else
					$filter['date_to'] = date_create($filter['date_to'])->format('Y-n-j');

			if(isset($filter['status']))
				if($filter['status'] == 'ALL')
					unset($filter['status']);
		}

		//default pagination
		$paginationData = array('resultsPerPage' => 25, 'page' => 1);

		$pagination = $dorm->input->get('pagination');
		if($pagination)
			$paginationData = $pagination;

		$orders = $this->_repository->findWhere($filter, $sortBy, $sortDir, $paginationData);

		$totalRecords = $this->_repository->lastTotalRecords;

		$orderResults = array(
			'totalRecords' => $totalRecords,
			'orders' => $orders,
			'pagination' => $paginationData
			);

		echo json_encode($orderResults);
	}

	//fulfill a service request
	function startNewOrder() {
		$order = $this->_repository->newOrder();

		echo json_encode($order);
	}

	//fulfill a service request
	function saveOrder($id) {
		//grab from request
		$order = new \DeCaro\Models\Order();
		$order = fill_object($order);

		// Changed Oct 29, 2018: allowing duplicate order numbers.
		/* if(!empty($order->order_number)) {
			//see if existing order exists with same order number (error) 
			$existingOrder = $this->_repository->findByOrderNumber($order->order_number);
			if ($existingOrder) {
				if($existingOrder->id != $order->id) {
					dorm()->response->error_response("An order already exists with this order number. Please choose another order number.");
					return;
				}
			}
		} */
				
		$order = $this->_repository->save($order);

		echo json_encode($order);
	}

	//fulfill a service request
	function removeOrder($id) {
		$order = $this->_repository->find($id);
		if(!$order) {
			throw new Exception("Order does not exist: " . $id);
		}

		$this->_repository->remove($order);

		return dorm()->response->success_response();
	}

	function getOrdersForClient($clientId) {
		global $dorm;

		//check that client exists
		$dateFrom = date_create($dorm->input->get('dateFrom'))->format('Y-n-j');
		$dateTo = date_create($dorm->input->get('dateTo'))->format('Y-n-j');

		$orders = $this->_repository->findWhere(array("client_id" => $clientId, "date_from" => $dateFrom, "date_to" => $dateTo));

		echo json_encode($orders);
	}	

	function getOrdersForClientStation($clientStationId) {
		global $dorm;

		//check that client exists
		$dateFrom = date_create($dorm->input->get('dateFrom'))->format('Y-n-j');
		$dateTo = date_create($dorm->input->get('dateTo'))->format('Y-n-j');

		$orders = $this->_repository->findWhere(array("client_station_id" => $clientStationId, "date_from" => $dateFrom, "date_to" => $dateTo));

		echo json_encode($orders);
	}

	function viewOrderPDF($orderId) {
		global $dorm;

		$order = $this->_repository->find($orderId);

		if(!$order) {
			error_response("Error locating this order with ID " . $orderId . ".");
			return;
		}

	  $clientStation = $this->_clientStationRepository->find($order->client_station_id);

		//generate webpage view based on order data:
		$data = array(
			'order' => $order,
			'clientStation' => $clientStation
	    );
	  
		$dorm->response->data_view('partials/orderPDF', $data);
	}

	function generateOrderPDF($orderId) {
		// global $dorm;

		// $pdfSource = $dorm->PDFGenerator->generateInvoicePDF($invoice);

		// header('Content-type: application/pdf');
		// echo $pdfSource;

		//header('Content-Disposition: inline; filename="' . $filename . '"');
		//header('Content-Transfer-Encoding: binary');
		//header('Accept-Ranges: bytes');

		$this->viewOrderPDF($orderId);
		return;
		
		//////////////////////////////


		// $command = 'wkhtmltopdf --orientation Landscape --page-height 234mm --page-width 100mm --margin-top 0 --margin-bottom 0';
		// $filePath = $dorm->config['upload_dir'] . 'order_' . $orderId . '.pdf';
		// $domain = $dorm->config['domain'];
		// $output = '> ' . $dorm->config['upload_dir'] . 'out.txt 2> ' . $dorm->config['upload_dir'] . 'out_error.txt';

		// $scriptCmd = $command . ' ' . $domain . '/service/viewOrderPDF/' . $orderId . ' ' . $filePath . ' ' . $output;
		// //echo $scriptCmd;
		// //exit();

		// if(file_exists($filePath)) {	
		// 	if(!unlink($filePath)) {
		// 		echo 'Error deleting existing file: ' . $filePath;
		// 		return;
		// 	}
		// }

		// // give the pdf script up to 10 seconds to finish, otherwise show error:
		// $starttime = round(microtime(true) * 1000);
		// $endtime = $starttime;
		// while (($endtime - $starttime) <= 1000*10)
		// {
		// 	exec($scriptCmd);

		// 	if(file_exists($filePath)) {
		// 		break;
		// 	}

		// 	$endtime = round(microtime(true) * 1000);
		// }

		// if(!file_exists($filePath)) {
		// 	echo "It took too long to generate the file: " . $filePath . ". Please report this error.";
		// 	echo "\nCmd: " . $scriptCmd;
		// 	exit();
		// } else {
		// 	header("Content-type: application/pdf");
		// 	header("Content-Disposition: inline; filename=order_" . $orderId . ".pdf");
		// 	@readfile($filePath);
		// }
	}
}

?>