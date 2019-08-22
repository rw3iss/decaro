<?php

class InvoiceController extends \Dorm\Models\DormController {
	private $_repository;

	function __construct() {
		global $dorm;
		//locate the repository
		$this->_orderRepository = $dorm->di->get('OrderRepository');
		$this->_repository = $dorm->di->get('InvoiceRepository');
		$this->_clientRepository = $dorm->di->get('ClientRepository');
		$this->_clientStationRepository = $dorm->di->get('ClientStationRepository');
	}

	function getInvoice($id) {
		try {
			$invoice = $this->_repository->find($id);

			if($invoice == null) {
				//TODO: throw not found 
				throw new \Exception("Invoice could not be found");
			}

			echo json_encode($invoice);
		} catch(\OutOfBoundsException $ex) {
			\Dorm\Util\Header::notFoundError();

			echo json_encode(array('status'=>'error', 'message'=>'Could not locate that invoice.'));
		}
	}

	// DEFUNCT
	function getInvoices() {
		$invoices = $this->_repository->findAll();

		echo json_encode($invoices);
	}

	function getAllInvoices() {
		global $dorm;

		$sortBy = $dorm->input->get('sortBy');
		$sortDir = $dorm->input->get('sortDir');
		$filter = $dorm->input->get('filter');

		// sanitize filter input
		if ($filter) {
			if(isset($filter['invoice_id']))
				if($filter['invoice_id'] == '0')
					unset($filter['invoice_id']);

			// if(isset($filter['client_name']))
			// 	if($filter['order_number'] == '')
			// 		unset($filter['order_number']);

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
			'invoices' => $orders,
			'pagination' => $paginationData
		);

		echo json_encode($orderResults);
	}

	function startNewInvoice() {
		$invoice = $this->_repository->newInvoice();

		echo json_encode($invoice);
	}

	function saveInvoice($id) {
		//grab from request
		$invoice = new \DeCaro\Models\Invoice();

		$invoice = fill_object($invoice);


		if(!empty($invoice->invoice_number)) {
			//see if existing invoice exists with same invoice number (error) 
			$existingInvoice = $this->_repository->findByInvoiceNumber($invoice->invoice_number);
			if($existingInvoice) {
				if($existingInvoice->id != $invoice->id) {
					dorm()->response->error_response("An invoice already exists with this invoice number. Please choose another invoice number.");
					return;
				}
			}
		}

		$invoice->total = str_replace('$', '', $invoice->total);
		$invoice->total = str_replace(',', '', $invoice->total);

		$invoice->date_from = date_create($invoice->date_from)->format('Y-n-j');
		$invoice->date_to = date_create($invoice->date_to)->format('Y-n-j');
		$invoice->date_due_by = date_create($invoice->date_due_by)->format('Y-n-j');
		$invoice->invoice_date = date_create($invoice->invoice_date)->format('Y-n-j');

		//save the instance
		$invoice = $this->_repository->save($invoice);

		$invoice->date_from = date_create_from_format('Y-m-d', $invoice->date_from)->format('m/j/Y');
		$invoice->date_to = date_create_from_format('Y-m-d', $invoice->date_to)->format('m/j/Y');
		$invoice->date_due_by = date_create_from_format('Y-m-d', $invoice->date_due_by)->format('m/j/Y');
		$invoice->invoice_date = date_create_from_format('Y-m-d', $invoice->invoice_date)->format('m/j/Y');

		echo json_encode($invoice);
	}

	//fulfill a service request
	function removeInvoice($id) {
		$invoice = $this->_repository->find($id);
		if(!$invoice) {
			throw new Exception("Invoice does not exist: " . $id);
		}

		$this->_repository->remove($invoice);

		return dorm()->response->success_response();
	}

	function viewInvoicePDF($invoiceId) {
		global $dorm;

		$invoice = $this->_repository->find($invoiceId);

		if(!$invoice) {
			error_response("Error locating this invoice with ID " . $invoiceId . ".");
			return;
		}	

	    $client = $this->_clientRepository->find($invoice->client_id);
	    $clientStation = $this->_clientStationRepository->find($invoice->client_station_id);

	    //grab all orders in this invoice
	    $orders = $this->_orderRepository->findWhere(array('id' => $invoice->order_ids));
	    $invoice->orders = $orders;

		//generate webpage view based on order data:
		$data = array(
			'invoice' => $invoice,
			'client' => $client,
			'clientStation' => $clientStation
	    );

		$dorm->response->data_view('partials/invoicePDF', $data);
	}


	function viewManifestPDF($invoiceId) {
		global $dorm;

		$invoice = $this->_repository->find($invoiceId);

		if(!$invoice) {
			error_response("Error locating this invoice with ID " . $invoiceId . ".");
			return;
		}	

	    $client = $this->_clientRepository->find($invoice->client_id);
	    $clientStation = $this->_clientStationRepository->find($invoice->client_station_id);

	    //grab all orders in this invoice
	    $orders = $this->_orderRepository->findWhere(array('id' => $invoice->order_ids));
	    $invoice->orders = $orders;

		//generate webpage view based on order data:
		$data = array(
			'invoice' => $invoice,
			'client' => $client,
			'clientStation' => $clientStation
	    );

		$dorm->response->data_view('partials/manifestPDF', $data);
	}

	function generateInvoicePDF($invoiceId) {
		global $dorm;

		$invoice = $this->_repository->find($invoiceId);

		if ( !$invoice ) {
			error_response("Error locating invoice with ID " . $invoiceId . ".");
			return;
		}

		$dorm->loader->loadPlugin('PDFGenerator');
		$pdfSource = $dorm->PDFGenerator->generateInvoicePDF($invoice);

		header('Content-type: application/pdf');
		echo $pdfSource;
			
		/*
		$command = 'wkhtmltopdf --orientation Landscape --page-height 234mm --page-width 100mm --margin-top 0 --margin-bottom 0';
		$filePath = $dorm->config['upload_dir'] . 'invoice_' . $invoiceId . '.pdf';
		$domain = $dorm->config['domain'];
		$output = '> ' . $dorm->config['upload_dir'] . 'out.txt 2> ' . $dorm->config['upload_dir'] . 'out_error.txt';


		if(file_exists($filePath)) {
			if(!unlink($filePath)) {
				echo 'Error deleting existing file: ' . $filePath;
				return;
			}
		}

		$scriptCmd = $command . ' ' . $domain . '/service/viewInvoicePDF/' . $invoiceId . ' ' . $filePath . ' ' . $output;
		echo $scriptCmd;
		exit();
		
		// give the pdf script up to 10 seconds to finish, otherwise show error:
		$starttime = round(microtime(true) * 1000);
		$endtime = $starttime;
		while (($endtime - $starttime) <= 10000)
		{
			exec($scriptCmd);

			if(file_exists($filePath)) {
				break;
			}
			$endtime = round(microtime(true) * 1000);
		}

		if(!file_exists($filePath)) {
			echo "It took too long to generate the file: " . $filePath . ". Please report this error.";
		} else {
			header("Content-type: application/pdf");
			header("Content-Disposition: inline; filename=invoice_" . $invoiceId . ".pdf");
			@readfile($filePath);
		}
		*/
	}

	function generateManifestPDF($invoiceId) {
		global $dorm;

		$invoice = $this->_repository->find($invoiceId);

		if ( !$invoice ) {
			error_response("Error locating invoice with ID " . $invoiceId . ".");
			return;
		}

		$dorm->loader->loadPlugin('PDFGenerator');
		$pdfSource = $dorm->PDFGenerator->generateManifestPDF($invoice);

		header('Content-type: application/pdf');
		echo $pdfSource;

		// $this->viewManifestPDF($invoiceId);
		// return;
	}

	function generateInvoicePDF_old($invoiceId) {
		global $dorm;

		$invoice = $this->_repository->find($invoiceId);

		if(!$invoice) {
			error_response("Error locating this invoice with ID " . $invoiceId . ".");
			return;
		}	

	    $client = $this->_clientRepository->find($invoice->client_id);
	    $clientStation = $this->_clientStationRepository->find($invoice->client_station_id);

	    //grab all orders in this invoice
	    $orders = $this->_orderRepository->findWhere(array('id' => $invoice->order_ids));
	    $invoice->orders = $orders;

		//generate webpage view based on order data:
		$data = array(
			'invoice' => $invoice,
			'client' => $client,
			'clientStation' => $clientStation
	    );

		$dorm->response->data_view('partials/invoicePDF', $data);
	}

	/* OLD FUNCTIONS */

	function generateInvoicePDF_defunct($invoiceId) {
		global $dorm;

		//ob_clean();

		$invoice = $this->_repository->find($invoiceId);

		if(!$invoice) {
			error_response("Error locating this invoice with ID " . $invoiceId . ".");
			return;
		}

		$dorm->loader->loadPlugin('PDFGenerator');
		$dorm->PDFGenerator->generateInvoicePDF($invoice);
		ob_end_flush();
	}

}

?>