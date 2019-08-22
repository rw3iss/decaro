<?php

class PDFGenerator extends \Dorm\Models\DormPlugin {
	public $plugin_key = "PDFGenerator";
	private $pdf = null;

	function __construct() {
		global $dorm;
		$this->_orderRepository = $dorm->di->get('OrderRepository');
		$this->_clientStationRepository = $dorm->di->get('ClientStationRepository');
	}

	function text($pdf, $text, $x = 5, $y = 5, $a = 'L', $b = 0, $w = 200, $h = 100) {
	    $pdf->SetY( $y, true );	
	    $pdf->SetX( $x, true );
		$pdf->MultiCell($w, $h, $text, $b, $a, 1, 0, '','', true);	
	}

	function line($pdf, $x1, $y1, $x2, $y2, $width=0.25, $dash=1) {
		$style = array('width' => $width, 'cap' => 'butt', 'join' => 'miter', 'dash' => $dash, 'color' => array(100,100,100));
		$pdf->Line($x1, $y1, $x2, $y2, $style);
	}

	function fullLine($yPos, $style = 1) {
		$this->line($this->pdf, 7, $yPos, $this->pdf->getPageWidth()-7, $yPos, .25, $style);
	}

	function addDocumentHeader($pdf, $pageWidth = 100) {
		$pdf->Image('assets/img/decaro.jpg', 15, 5, 42,17, 'JPG', null, '', true, 150, '', false, false, 1, false, false, false);
		$pdf->Ln(49);

	    $pdf->SetFont( 'Helvetica', 'b', 13);
	    $pdf->SetY( 5, true );
	    $pdf->SetX( 65, true );
		$pdf->MultiCell($pageWidth, 5, '22 McLellan St.' , 0, 'L', 1, 0, '', '', true);
		$pdf->Ln(6);
	    $pdf->SetX( 65, true );
		$pdf->MultiCell($pageWidth, 5, 'Newark, NJ 07114' , 0, 'L', 1, 0, '', '', true);


	    $pdf->SetY( 5, true );
	    $pdf->SetX( 110, true );
		$pdf->MultiCell($pageWidth, 5, '(973) 242-0777' , 0, 'L', 1, 0, '', '', true);
		$pdf->Ln(6);
	    $pdf->SetX( 110, true );
		$pdf->MultiCell($pageWidth, 5, 'Fax: (973) 242-1272' , 0, 'L', 1, 0, '', '', true);

/*
	    $pdf->SetFont( '', 'b', 10);
	    $pdf->SetY( 5, true );
		$this->text($pdf, 'Generated at:', $pageWidth-3, 5);
	    $pdf->SetFont( '', '', 10);
		*/
	}

	function generateOrderPDF($order) {
	    $clientStation = $this->_clientStationRepository->find($order->client_station_id);
	    $leftMargin = 15;
		$sectionMargin = 15;
		$lineHeight = 7;

		$paymentTypeOptions = array(
			'thirdparty' => 'Third Party',
			'collect' => 'Collect',
			'prepaid' => 'Prepaid'
			);
		$paymentTypeText = $paymentTypeOptions[$order->payment_type];

		//start PDF generation
		require_once(DORM_PATH . '/../tcpdf/tcpdf.php');

		//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

		$width= 500;
		$height = 300;
		$pageLayout = array($width, $height); //  or array($height, $width) 
		$pdf = new TCPDF('l', 'pt', $pageLayout, true, 'UTF-8', false);


		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('DeCaro Trucking');
		$pdf->SetTitle('DeCaro Order #' . $order->order_number . ' for ' . $order->client->name);
		$pdf->SetSubject('Order');
		$pdf->SetKeywords('DeCaro, Order, ' . $order->client->name);
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 005', PDF_HEADER_STRING);
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFooterMargin(0);
		$pdf->SetAutoPageBreak(FALSE, 0);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		$date_created = date_create()->format('n/j/Y');

		$pdf->AddPage();
	    $pdf->SetFont( 'helvetica', '', 11 );
	    $pdf->SetY( 5, true );

	    # Table parameters
	    #
	    # Column size, wide (description) column, table indent, row height.
	    $smallCol = 30;
	    $col = $smallCol * 2;
	    $wideCol = $smallCol * 3;
	    $padding = 27;// ( $pdf->getPageWidth() - 2 * 10 - $wideCol - 3 * $col ) / 2;
	    $line = 10;

	    $pageWidth = $pdf->getPageWidth() - $padding;

		$pdf->setCellPaddings(1, 1, 1, 1);
		$pdf->SetFillColor(255, 255, 255);

		$this->addDocumentHeader($pdf, $pageWidth);



		$yCurr = 5;
		
		$now = date_create();
		
		$pdf->SetFont( '', '', 10);
		$this->text($pdf, 'Order date:', $pageWidth-40, $yCurr, 'R', 0, 50, 10);
		$pdf->SetFont( '', 'b', 12);
		$this->text($pdf, $now->format('n-j-Y'), $pageWidth-40, $yCurr+5, 'R', 0, 50, 10);

		/* Origin address */
		$yCurr = 26;
		$addressStart = $yCurr;
		$addressEnd = $yCurr;
	    $addressWidth = 90;
	    $addressSpacing = 15;

		if($order->shipper_enabled == 1) {
			$addressWidth = 90;
	    	$addressSpacing = 15;
		    $pdf->SetFont( '', 'b', 10);
		    $this->text($pdf, 'Shipper:',  $leftMargin+1, $yCurr);
			$yCurr = $yCurr + $lineHeight-2;
		    $pdf->SetFont( '', '', 10);
		    $this->text($pdf, $order->shipper_name, $leftMargin+5, $yCurr);
			$yCurr = $yCurr + $lineHeight-2;
		    $this->text($pdf, $order->origin_address, $leftMargin+5, $yCurr);
			$yCurr = $yCurr + $lineHeight-2;
		    $this->text($pdf, $order->origin_city . ', ' . $order->origin_state . ' ' . $order->origin_zipcode, $leftMargin+5, $yCurr);
			$yCurr = $yCurr + $lineHeight-2;
			$yCurr += 2;
			$this->line($pdf, $leftMargin, $addressStart, $leftMargin, $yCurr);
			$this->line($pdf, $leftMargin, $yCurr, $leftMargin+$addressWidth-5, $yCurr);
			$addressEnd = $yCurr;
		}

		/* Destination address */

		$yCurr = 26;
	    $pdf->SetFont( '', 'b', 10);
	    $this->text($pdf, 'Consignee:', $leftMargin+1+$addressWidth-5, $addressStart);
		$yCurr = $yCurr + $lineHeight-2;
    	$pdf->SetFont( '', '', 10);
	    if($order->destination_name != '') {
	    	$this->text($pdf, $order->destination_name, $leftMargin+1+$addressWidth, $yCurr);
			$yCurr = $yCurr + $lineHeight-2;
	    }
	    $this->text($pdf, $order->destination_address, $leftMargin+1+$addressWidth, $yCurr);
		$yCurr = $yCurr + $lineHeight-2;
	    $this->text($pdf, $order->destination_city . ', ' . $order->destination_state . ' ' . $order->destination_zipcode, $leftMargin+1+$addressWidth, $yCurr);
		$yCurr = $yCurr + $lineHeight-2;
		$yCurr += 2;
		//left
		//bottom
		$this->line($pdf, $leftMargin+$addressWidth-5, $addressStart, $leftMargin+$addressWidth-5, $yCurr);
		$this->line($pdf, $leftMargin+$addressWidth-5, $yCurr, $leftMargin+$addressWidth+$addressWidth+$addressSpacing-8, $yCurr);
		//right

		if($yCurr > $addressEnd)
			$addressEnd = $yCurr;

	    $yCurr += 1;
	    $pdf->SetFont( '', 'b', 11); 
		$this->text($pdf, 'Order #:', $leftMargin, $yCurr, 'L', 0, 45, 10);
	    $pdf->SetFont( '', '', 11); 
	    $this->text($pdf, $order->order_number, $leftMargin+17, $yCurr);
	    $yCurr += 6;

	    if($order->customer_number != '') {
		    $pdf->SetFont( '', 'b', 11); 
			$this->text($pdf, 'Customer Reference:', $leftMargin, $yCurr, 'L', 0, 45, 10);
		    $pdf->SetFont( '', '', 12); 
		    $this->text($pdf, $order->customer_number, $leftMargin+42, $yCurr);
	    }

	    if($order->payment_type == 'thirdparty') {
			$yCurr = $addressEnd+2;

		    $pdf->SetFont( '', 'b', 10);
		    $this->text($pdf, '(Third Party address):',  $leftMargin+$addressWidth-4, $yCurr);
		    $pdf->SetFont( '', '', 10);
			$yCurr = $yCurr + $lineHeight-2;
	    	$this->text($pdf, $order->third_party_address, $leftMargin+$addressWidth+1, $yCurr);
			$yCurr = $yCurr + $lineHeight-2;
			if(!empty($order->third_party_address2)) {
			    $this->text($pdf, $order->third_party_address2, $leftMargin+$addressWidth+1, $yCurr);
				$yCurr = $yCurr + $lineHeight-2;
			}
	    	$this->text($pdf, $order->third_party_city . ', ' . 
	    		$order->third_party_state . ' ' . $order->third_party_zipcode, $leftMargin+$addressWidth+1, $yCurr);

	    	$this->line($pdf, $leftMargin+$addressWidth-5, $addressEnd, $leftMargin+$addressWidth-5, $yCurr+10);

	    	$yCurr -= 8;
	    }

	    if($order->status == 'COMPLETE' && false) {
			$yCurr = $yCurr + $lineHeight-3;
		    $pdf->SetFont( '', 'b', 11);
			$this->text($pdf, 'Order completed:', $leftMargin, $yCurr, 'R', 0, 45, 10);
		    $pdf->SetFont( '', '', 12);
			$this->text($pdf, date_create($order->pod_date)->format('n/j/Y'), $leftMargin+23, $yCurr, 'R', 0, 45, 10);
		    //$this->text($pdf, date_create($order->pod_date)->format('n/j/Y g:ia'), $leftMargin+31, $yCurr);
			$yCurr = $yCurr + $lineHeight-1;

		    $pdf->SetFont( '', 'b', 11);
			$this->text($pdf, 'Signed by:', $leftMargin, $yCurr, 'R', 0, 45, 10);
		    $pdf->SetFont( '', '', 11);
			$this->text($pdf, $order->pod_signature, $leftMargin+23, $yCurr, 'R', 0, 45, 10);
	    } else if($order->payment_type == 'thirdparty') {
	    	$yCurr += $lineHeight*2-6;
	    }


	    # ORDER CONTENTS

		$yCurr = $yCurr + $lineHeight*2-5;

		$this->line($pdf, $leftMargin, $yCurr, $pageWidth+13, $yCurr, 2, 0);
		$yCurr += $lineHeight-3;

	    $pdf->SetFont( '', 'b', 12);
		$this->text($pdf, 'Pcs', $leftMargin, $yCurr, 'R', 0, 15, 10);
		$this->text($pdf, 'Description', $leftMargin+20, $yCurr, 'L', 0, 120, 10);
		$this->text($pdf, 'Weight', $leftMargin+140, $yCurr, 'R', 0, 25, 10);
		$this->text($pdf, 'Type', $leftMargin+160, $yCurr, 'R', 0, 25, 10);

		$yCurr = $yCurr+8;
	    $pdf->SetFont( '', '', 11);
		$this->text($pdf, $order->pieces, $leftMargin, $yCurr, 'R', 0, 15, 10);

	    $pdf->setY($yCurr);
	    $pdf->setX(35);
		$this->text($pdf, $order->description, $leftMargin+20, $yCurr, 'L', 0, 120, 10);

		$this->text($pdf, $order->weight, $leftMargin+140, $yCurr, 'R', 0, 20, 10);

		$this->text($pdf, $order->payment_type, $leftMargin+160, $yCurr, 'R', 0, 25, 10);

		$yCurr = $yCurr + $lineHeight;

	    //Fuel surcharge
	    if($order->fuel_surcharge != "") {
			$yCurr = $yCurr + $lineHeight;
	  		$pdf->SetFont( '', 'b', 12);
		    $this->text($pdf, 'Fuel Surcharge: ', $leftMargin, $yCurr);
		    $pdf->SetFont( '', '', 12);
		    $this->text($pdf, $order->fuel_surcharge . '%', $leftMargin+35, $yCurr);
		}


	    switch($order->payment_type) {
	    	case "collect":
	    		$yCurr = 70;
	    		break;
	    	case "prepaid":
	    		$yCurr = 80;
	    		break;
	    	case "thirdparty":
	    		$yCurr = 95;
	    		break;
	    }
		//$this->text($pdf, 'X', $pageWidth, $yCurr);

	    //$this->text($pdf, 'PLEASE SIGN AND PRINT', $leftMargin, $yCurr);

	    /*
		$yCurr = $yCurr + 120;
	    $pdf->SetFont( '', 'b', 12);
	    $this->text($pdf, 'Additional Notes', $leftMargin, $yCurr);
		$yCurr = $yCurr + $lineHeight;
	    $pdf->SetFont( '', '', 11);
	    $this->text($pdf, $order->additional, $leftMargin, $yCurr);
		*/


	    //move total down if description is long
		$cellHeight = 8;
		if(strlen($order->description) > 200) {
			$cellHeight = 22;
		}
		else if(strlen($order->description) > 100) {
			$cellHeight = 16;
		}

		$yCurr += $cellHeight;

    	setlocale(LC_MONETARY, 'en_US.UTF-8');

    	$pdf->SetFont( '', 'b', 13);
    	$moneyString = $order->pod_total;//money_format('%.2n', str_replace('$','',str_replace(',','',$order->pod_total)));
        $this->text($pdf, $moneyString, $pageWidth-18, $yCurr + 20, 'R', 0, 30, 5 );

        $this->text($pdf, 'Order Total: ', $pageWidth-51-(strlen($moneyString)), $yCurr + 20, 'R', 0, 50, 5 );
    	$pdf->SetFont( '', '', 13);

/*
    	// lower liability wording
    	$pdf->SetFont( '', '', 8);
    	$yCurr = $pdf->getPageHeight() - 45;
    	$leftMargin = 7;
        $this->text($pdf, 'PLEASE SIGN', $leftMargin, $yCurr);
        $yCurr += 4;
        $this->text($pdf, '    AND PRINT', $leftMargin, $yCurr);
        $yCurr += 4;
		$this->line($pdf, $leftMargin+25, $yCurr, $pageWidth-50, $yCurr, .1, 0);
        $yCurr -= 4;
        $this->text($pdf, 'DATE', $pageWidth-45, $yCurr);
        $yCurr += 4;
		$this->line($pdf, $pageWidth-33, $yCurr, $pageWidth+10, $yCurr, .1, 0);

		$msg = "Liability, including negligence is limited to the sum of $50.00 per shipment, unless a greater valuation shall be paid for or agreed to be paid in writing to DeCaro Trucking prior to shipping.";
        $this->text($pdf, strtoupper($msg), $leftMargin, $yCurr += 2);
        $msg = "DeCaro Trucking will not be responsible for damages due to poor packing by shipper.";
    	$pdf->SetFont( '', 'b', 8);
        $this->text($pdf, strtoupper($msg), $leftMargin, $yCurr += 8);
    	$pdf->SetFont( '', 'b', 12);
        $this->text($pdf, "NON-NEGOTIABLE", $pageWidth-31, $yCurr-2.5);
		$this->line($pdf, $leftMargin+1, $yCurr += 6, $pageWidth+12, $yCurr, .1, 0);
        $msg = "It is mutually agreed that the goods herein described are accepted in apparent good order (except as noted) for transportation as specified herein, subject to governing classifications and tariffs in effect as of the date hereof which are filled in accordance with law. Said classifications and tariffs, copies of which are available for inspection by the parties hereto, are hereby incorporated into and made part of this contract.";
    	$pdf->SetFont( '', '', 8);
        $this->text($pdf, strtoupper($msg), $leftMargin, $yCurr+1);
*/
        
		$pdf->Output('order_' . $date_created . '.pdf', 'I');
	}

	function generateInvoicePDF($invoice) {
		$invoice_date_created = date_create()->format('n-j-Y');
		$clientStation = $this->_clientStationRepository->find($invoice->client_station_id);

		//start PDF generation
		require_once(DORM_PATH . '/../tcpdf/tcpdf.php');

		$pdf = $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('DeCaro Trucking');
		$pdf->SetTitle('DeCaro Invoice for ' . $invoice->client->name);
		$pdf->SetSubject('Invoice');
		$pdf->SetKeywords('DeCaro, Invoice, ' . $invoice->client->name);

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 005', PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(0);

		// set auto page breaks
		$pdf->SetAutoPageBreak(FALSE, 0);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->AddPage();
	    $pdf->SetFont( 'helvetica', '', 11 );
	    $pdf->SetY( 5, true );

		// ---------------------------------------------------------

	    # Table parameters
	    #
	    # Column size, wide (description) column, table indent, row height.
	    $xsCol = 15;
	    $smallCol = 15 * 2;
	    $col = $smallCol * 2;
	    $wideCol = $smallCol * 3;
	    $padding = 27;// ( $pdf->getPageWidth() - 2 * 10 - $wideCol - 3 * $col ) / 2;
	    $line = 10;

	    $leftMargin = 15;
	    $lineHeight = 7;
	    $pageWidth = $pdf->getPageWidth() - $padding;
	    $pageHeight = $pdf->getPageHeight()-$padding;
	    $col2 = 85;

		$pdf->setCellPaddings(1, 1, 1, 1);
		$pdf->SetFillColor(255, 255, 255);

		$this->addDocumentHeader($pdf, $pageWidth);

		$this->fullLine($yCurr = $lineHeight*2+11);

		$yCurr += 2;
	    $pdf->SetFont( '', 'b', 8);
		$this->text($pdf, "INVOICE NUMBER:", $pageWidth-45, $yCurr+1);
	    $pdf->SetFont( '', '', 11);
		$this->text($pdf, $invoice->invoice_number, $pageWidth-17, $yCurr+.2, 'R', 0, 35);
	    $pdf->SetFont( '', 'b', 8);
		$this->text($pdf, "INVOICE DATE:", $pageWidth-40.5, $yCurr += 7);
	    $pdf->SetFont( '', '', 11);
		$this->text($pdf, date_create($invoice->date_from)->format('n/j/Y'), $pageWidth-17, $yCurr-1, 'R', 0, 35);

		$leftMargin -= 1;
		$yCurr -= 5;
	    $pdf->SetFont( '', 'b', 8);
		$this->text($pdf, "BILLED TO:", $leftMargin, $yCurr);
	    $pdf->SetFont( '', 'b', 11);
		$this->text($pdf, $invoice->client->name, $leftMargin, $yCurr += 5);
	    $pdf->SetFont( '', '', 11);
		$this->text($pdf, $clientStation->address, $leftMargin, $yCurr+=$lineHeight-2);
		$pdf->Ln(6);
		$yCurr += $lineHeight-2;
		if(!empty($clientStation->address2)) {
			$this->text($pdf, $clientStation->address2, $leftMargin, $yCurr);
			$yCurr += $lineHeight-2;
		}
		$this->text($pdf, $clientStation->city . ', ' . $clientStation->state . ' ' . $clientStation->zipcode, $leftMargin, $yCurr);
		$pdf->Ln(6);

		$yCurr += $lineHeight+5;


	    # TABLE HEADER
	    $pdf->SetFont( '', 'b', 12);

	    //$this->text($pdf, 'Invoice Items:', $leftMargin, $yCurr);

	    $pdf->SetY( $yCurr, true );

		$style = array('width' => 10, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(200,200,200));
		$pdf->Line(7, $yCurr+=5, $pageWidth+20, $yCurr, $style);

	    $pdf->SetFont( '', 'b', 11);
	    $pdf->Cell( $padding / 2 );
	    $pdf->Cell( $smallCol-28, $line, 'Order #', 0, 0, 'R' );
	    $pdf->Cell( $smallCol - 8, $line, 'Shipped', 0, 0, 'R' );
	    $pdf->Cell( $xsCol-1, $line, 'Pcs.', 0, 0, 'R' );
	    $pdf->Cell( $xsCol-4, $line, 'Wt.', 0, 0, 'R' );
	    $pdf->Cell( $xsCol+2, $line, 'Type', 0, 0, 'R' );
	    $pdf->Cell( $xsCol+10, $line, 'Description', 0, 0, 'R' );
	    $pdf->Cell( $smallCol+57, $line, 'Total', 0, 0, 'R' );
	    $pdf->Ln();

	    $yCurr += 7;

	    $currY = $pdf->GetY();

	    # Table content rows
	    $orderTotals = 0;

	    foreach( $invoice->order_ids as $oid ) {
	    	$order = $this->_orderRepository->find($oid);
	    	$pad = 0;

	    	if(!$order) {
	    		throw new DormException("Could not locate an order for invoice generation. Order ID: " . $oid);
	    	}

	    	$pdf->SetFont( '', '', 10);

	    	$colCurr = $leftMargin-15;	
	        $this->text($pdf, $order->order_number, $colCurr, $yCurr, 'R', 0, 30, 5 );
	    	$colCurr += $smallCol-17;
	    	$this->text($pdf, date_create($order->date_created)->format('n/j/Y'), $colCurr+.5, $yCurr, 'R', 0, 40, 10 );
	    	$colCurr += $smallCol-10;
	        $this->text($pdf, $order->pieces+'11', $colCurr+2, $yCurr, 'R', 0, 30, 5 );
	    	$colCurr += $smallCol-15;
	        $this->text($pdf, $order->weight, $colCurr, $yCurr, 'R', 0, 30, 5 );
	    	$colCurr += $smallCol-13;
	        $this->text($pdf, $order->delivery_type, $colCurr, $yCurr, 'R', 0, 30, 5 );
	    	$pdf->SetFont( '', '', 10);
	    	$colCurr += $smallCol-3;
			$cellHeight = 8;
			if(strlen($order->description) > 200) {
				$cellHeight = 22;
				$pad += 2;
			}
			else if(strlen($order->description) > 100) {
				$cellHeight = 16;
				$pad += 1;
			}
			$pdf->MultiCell(85,$cellHeight-1, $order->description, 0, 'L', 1, 0, $colCurr+5, $yCurr, true, false, 0, false, false, 0, 'T', true);	
	    	
	    	$colCurr += $smallCol+55;
	    	$orderPrice = $order->pod_total ? (str_replace('$','',$order->pod_total)) : 0;

	        $this->text($pdf, '$'.$orderPrice, $colCurr, $yCurr, 'R', 0, 30, 5 );

	        $orderTotals += floatval($orderPrice);

	    	$y2 = $pdf->GetY();

	        $yCurr += $cellHeight+ $pad;

	        $this->line($pdf, 7, $yCurr, $pageWidth+20, $yCurr);

	        $yCurr += 1;

	        //bottom invoice sign/date/liability\

	        $yCurr = $pdf->getPageHeight() - 30;
	        $this->line($pdf, 7, $yCurr, $pageWidth+20, $yCurr);
	    	$pdf->SetFont( '', 'b', 12);
        	$this->text($pdf, strtoupper('PAYMENT DUE WITHIN 30 DAYS OF INVOICE DATE'), $leftMargin+45, $yCurr += 2);
    		
    		$leftMargin = 7;
	    	$pdf->SetFont( '', '', 8);
	      	$msg = "Liability, including negligence is limited to the sum of $50.00 per shipment, unless a greater valuation shall be paid for or agreed to be paid in writing to DeCaro Trucking prior to shipping.";
        	$this->text($pdf, strtoupper($msg), $leftMargin, $yCurr += 7.5);
	      	$msg = "DECARO TRUCKING WILL NOT BE RESPONSIBLE FOR DAMAGES DUE TO POOR PACKING BY SHIPPER.";
        	$this->text($pdf, strtoupper($msg), $leftMargin, $yCurr += 8);
	    }


	    # INVOICE TOTAL

    	setlocale(LC_MONETARY, 'en_US.UTF-8');

    	$pdf->SetFont( '', 'b', 13);
    	$moneyString = $orderTotals; //money_format('%.2n', $orderTotals);
        $this->text($pdf, $moneyString, $pageWidth-13, $yCurr + 20, 'R', 0, 30, 5 );

        $this->text($pdf, 'Invoice Total: ', $pageWidth-47-(strlen($moneyString)), $yCurr + 20, 'R', 0, 50, 5 );
    	$pdf->SetFont( '', '', 13);



    	#OUTPUT / FINISHED

		$pdf->Output('invoice_' . $invoice_date_created . '.pdf', 'I');


    /* ----- */

/*
		// set font
		$pdf->SetFont('freesans', '', 11);

		// add a page
		$pdf->AddPage();

		// set cell padding
		$pdf->setCellPaddings(1, 1, 1, 1);

		// set cell margins
		$pdf->setCellMargins(1, 1, 1, 1);

		// set color for background
		$pdf->SetFillColor(255, 255, 127);

		// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)

		// set some text for example
		$txt = 'Invoice for work.';

		// Multicell test
		$pdf->MultiCell(55, 5, '[LEFT] '.$txt, 1, 'L', 1, 0, '', '', true);
		$pdf->MultiCell(55, 5, '[RIGHT] '.$txt, 1, 'R', 0, 1, '', '', true);
		$pdf->MultiCell(55, 5, '[CENTER] '.$txt, 1, 'C', 0, 0, '', '', true);
		$pdf->MultiCell(55, 5, '[JUSTIFY] '.$txt."\n", 1, 'J', 1, 2, '' ,'', true);
		$pdf->MultiCell(55, 5, '[DEFAULT] '.$txt, 1, '', 0, 1, '', '', true);

		$pdf->Ln(4);

		// set color for background
		$pdf->SetFillColor(220, 255, 220);

		// Vertical alignment
		$pdf->MultiCell(55, 40, '[VERTICAL ALIGNMENT - TOP] '.$txt, 1, 'J', 1, 0, '', '', true, 0, false, true, 40, 'T');
		$pdf->MultiCell(55, 40, '[VERTICAL ALIGNMENT - MIDDLE] '.$txt, 1, 'J', 1, 0, '', '', true, 0, false, true, 40, 'M');
		$pdf->MultiCell(55, 40, '[VERTICAL ALIGNMENT - BOTTOM] '.$txt, 1, 'J', 1, 1, '', '', true, 0, false, true, 40, 'B');

		$pdf->Ln(4);

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		// set color for background
		$pdf->SetFillColor(215, 235, 255);

		// set some text for example
		$txt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed imperdiet lectus. Phasellus quis velit velit, non condimentum quam. Sed neque urna, ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras eget velit nulla, eu sagittis elit. Nunc ac arcu est, in lobortis tellus. Praesent condimentum rhoncus sodales. In hac habitasse platea dictumst. Proin porta eros pharetra enim tincidunt dignissim nec vel dolor. Cras sapien elit, ornare ac dignissim eu, ultricies ac eros. Maecenas augue magna, ultrices a congue in, mollis eu nulla. Nunc venenatis massa at est eleifend faucibus. Vivamus sed risus lectus, nec interdum nunc.

		Fusce et felis vitae diam lobortis sollicitudin. Aenean tincidunt accumsan nisi, id vehicula quam laoreet elementum. Phasellus egestas interdum erat, et viverra ipsum ultricies ac. Praesent sagittis augue at augue volutpat eleifend. Cras nec orci neque. Mauris bibendum posuere blandit. Donec feugiat mollis dui sit amet pellentesque. Sed a enim justo. Donec tincidunt, nisl eget elementum aliquam, odio ipsum ultrices quam, eu porttitor ligula urna at lorem. Donec varius, eros et convallis laoreet, ligula tellus consequat felis, ut ornare metus tellus sodales velit. Duis sed diam ante. Ut rutrum malesuada massa, vitae consectetur ipsum rhoncus sed. Suspendisse potenti. Pellentesque a congue massa.';

		// print a blox of text using multicell()
		$pdf->MultiCell(80, 5, $txt."\n", 1, 'J', 1, 1, '' ,'', true);

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		// AUTO-FITTING

		// set color for background
		$pdf->SetFillColor(255, 235, 235);

		// Fit text on cell by reducing font size
		$pdf->MultiCell(55, 60, '[FIT CELL] '.$txt."\n", 1, 'J', 1, 1, 125, 145, true, 0, false, true, 60, 'M', true);

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

		// CUSTOM PADDING

		// set color for background
		$pdf->SetFillColor(255, 255, 215);

		// set font
		$pdf->SetFont('helvetica', '', 8);

		// set cell padding
		$pdf->setCellPaddings(2, 4, 6, 8);

		$txt = "CUSTOM PADDING:\nLeft=2, Top=4, Right=6, Bottom=8\nLorem ipsum dolor sit amet, consectetur adipiscing elit. In sed imperdiet lectus. Phasellus quis velit velit, non condimentum quam. Sed neque urna, ultrices ac volutpat vel, laoreet vitae augue.\n";

		$pdf->MultiCell(55, 5, $txt, 1, 'J', 1, 2, 125, 210, true);

		// move pointer to last page
		$pdf->lastPage();

		// ---------------------------------------------------------
		//Close and output PDF document
		$pdf->Output('invoice_TEST.pdf', 'I');
		*/
	}


	function generateManifestPDF($invoice) {
		$invoice_date_created = date_create()->format('n-j-Y');
		$clientStation = $this->_clientStationRepository->find($invoice->client_station_id);

		//start PDF generation
		require_once(DORM_PATH . '/../tcpdf/tcpdf.php');

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('DeCaro Trucking');
		$pdf->SetTitle('DeCaro Invoice for ' . $invoice->client->name);
		$pdf->SetSubject('Invoice');
		$pdf->SetKeywords('DeCaro, Invoice, ' . $invoice->client->name);

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 005', PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(0);

		// set auto page breaks
		$pdf->SetAutoPageBreak(FALSE, 0);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

		$pdf->AddPage();
	    $pdf->SetFont( 'helvetica', '', 11 );
	    $pdf->SetY( 5, true );

		// ---------------------------------------------------------

	    # Table parameters
	    #
	    # Column size, wide (description) column, table indent, row height.
	    $xsCol = 15;
	    $smallCol = 15 * 2;
	    $col = $smallCol * 2;
	    $wideCol = $smallCol * 3;
	    $padding = 27;// ( $pdf->getPageWidth() - 2 * 10 - $wideCol - 3 * $col ) / 2;
	    $line = 10;

	    $leftMargin = 15;
	    $lineHeight = 7;
	    $pageWidth = $pdf->getPageWidth() - $padding;
	    $pageHeight = $pdf->getPageHeight()-$padding;
	    $col2 = 85;

		$pdf->setCellPaddings(1, 1, 1, 1);
		$pdf->SetFillColor(255, 255, 255);

		$this->addDocumentHeader($pdf, $pageWidth);

		$yCurr = 25;

		$this->text($pdf, $invoice->invoice_number, $pageWidth-10, $yCurr);

	    $pdf->SetFont( '', 'b', 12);
		//$this->text($pdf, 'Invoice start date:', $leftMargin+$col2, $yCurr);
	    $pdf->SetFont( '', '', 12);
		$this->text($pdf, date_create($invoice->date_from)->format('n/j/Y'), $leftMargin+$col2+38, $yCurr);
		$yCurr += $lineHeight;
	    //$pdf->SetFont( '', 'b', 12);
		//$this->text($pdf, 'Invoice end date:', $leftMargin+$col2, $yCurr);
	    //$pdf->SetFont( '', '', 12);
		//$this->text($pdf,date_create($invoice->date_to)->format('n/j/Y'), $leftMargin+$col2+37, $yCurr);
		

		$yCurr += $lineHeight;
	    $pdf->SetFont( '', 'b', 12);
    	$this->text($pdf, 'Invoice Total:  $'.number_format((float)$invoice->total, 2, '.', ','), $leftMargin+$col2, $yCurr, 'L', 0, 88, 5 );
    	$yCurr += $lineHeight;
	    $pdf->SetFont( '', 'b', 12);
    	$this->text($pdf, 'Invoice payment due by:  ' . date_create($invoice->date_due_by)->format('n/j/Y'), $leftMargin+$col2, $yCurr, 'L', 0, 88, 5 );

    	$yCurr -= $lineHeight*2;
	    $pdf->SetFont( '', 'b', 12);
		$this->text($pdf, $invoice->client->name, $leftMargin, $yCurr);
    	$yCurr += $lineHeight;
	    $pdf->SetFont( '', '', 11);
		$this->text($pdf, $clientStation->address, $leftMargin, $yCurr);
		$pdf->Ln(6);
		$yCurr += $lineHeight-2;
		if(!empty($clientStation->address2)) {
			$this->text($pdf, $clientStation->address2, $leftMargin, $yCurr);
			$yCurr += $lineHeight-2;
		}
		$this->text($pdf, $clientStation->city . ', ' . $clientStation->state . ' ' . $clientStation->zipcode, $leftMargin, $yCurr);
		$pdf->Ln(6);

		$yCurr += $lineHeight+3;
		$style = array('width' => 0.25, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(100,100,100));
		
		$yCurr += $lineHeight+2;
		$pdf->Line($leftMargin, $yCurr-1, $pageWidth+12, $yCurr, $style);

	    # Table header
	    $pdf->SetFont( '', 'b', 12);
	    $pdf->SetY( 52, true );

	    $this->text($pdf, 'Invoice Items:', $leftMargin, $yCurr);
		$pdf->Ln(8);

		$yCurr += $lineHeight + 30;

	    $pdf->SetY( $yCurr, true );

	    $pdf->SetFont( '', 'b', 11);
	    //$pdf->Cell( $padding / 2 );
	    $pdf->Cell( $smallCol, $line, 'Order #', 0, 0, 'R' );
	    $pdf->Cell( $smallCol - 7, $line, 'Shipped', 0, 0, 'R' );
	    $pdf->Cell( $xsCol, $line, 'Pieces', 0, 0, 'R' );
	    $pdf->Cell( $xsCol, $line, 'Weight', 0, 0, 'R' );
	    $pdf->Cell( $smallCol+55, $line, 'Description', 0, 0, 'R' );
	    $pdf->Cell( $smallCol-10, $line, 'Total', 0, 0, 'R' );
	    $pdf->Ln();

	    $yCurr += 11;

	    $currY = $pdf->GetY();

	    # Table content rows
	    $pdf->SetFont( '', '' );
	    foreach( $invoice->order_ids as $oid ) {
	    	$order = $this->_orderRepository->find($oid);
	    	$pad = 0;

	    	if(!$order) {
	    		throw new DormException("Could not locate an order for invoice generation. Order ID: " . $oid);
	    	}

	    	$pdf->SetFont( '', '', 11);

	    	$colCurr = $leftMargin;
	        $this->text($pdf, $order->order_number, $colCurr, $yCurr, 'R', 0, 30, 5 );
	    	$colCurr += $smallCol-17;
	    	$this->text($pdf, date_create($order->date_created)->format('n/j/Y'), $colCurr, $yCurr, 'R', 0, 40, 10 );
	    	$colCurr += $smallCol-5;
	        $this->text($pdf, $order->pieces, $colCurr, $yCurr, 'R', 0, 30, 5 );
	    	$colCurr += $smallCol-15;
	        $this->text($pdf, $order->weight, $colCurr, $yCurr, 'R', 0, 30, 5 );
	    	$pdf->SetFont( '', '', 10);
	    	$colCurr += $smallCol-3;
			$cellHeight = 8;
			if(strlen($order->description) > 220) {
				$cellHeight = 22;
				$pad += 2;
			}
			else if(strlen($order->description) > 120) {
				$cellHeight = 15;
				$pad += 2;
			}
			$pdf->MultiCell(84,$cellHeight, $order->description, 0, 'R', 1, 0, $colCurr+4, $yCurr, true, false, 0, false, false, 0, 'T', true);	
	    	
	    	$colCurr += $smallCol+48;
	        $this->text($pdf, $order->pod_total ? ('$'.$order->pod_total) : ($order->pod_total), $colCurr, $yCurr, 'R', 0, 30, 5 );
	    	
	    	$y2 = $pdf->GetY();

	        $yCurr += $cellHeight + $pad;
	    }

	    $pdf->SetFont( '', 'b' );


		$pdf->Output('invoice_' . $invoice_date_created . '.pdf', 'I');

	}
}

?>