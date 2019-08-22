<?php
require_once 'lib/Zend/Pdf/Canvas/Interface.php';
require_once 'lib/Zend/Pdf/Canvas/Abstract.php';
require_once 'lib/Zend/Pdf/Page.php';

require_once 'lib/Zend/Pdf/ElementFactory/Interface.php';
require_once 'lib/Zend/Pdf/ElementFactory/Proxy.php';
require_once 'lib/Zend/Pdf/ElementFactory.php';
require_once 'lib/Zend/Pdf/Element.php';
require_once 'lib/Zend/Pdf/Element/Dictionary.php';
require_once 'lib/Zend/Pdf/Element/Array.php';
require_once 'lib/Zend/Pdf/Element/String.php';
require_once 'lib/Zend/Pdf/Element/Numeric.php';
require_once 'lib/Zend/Pdf/Element/Object.php';
require_once 'lib/Zend/Pdf/Element/Stream.php';
require_once 'lib/Zend/Pdf/Element/Object/Stream.php';
require_once 'lib/Zend/Pdf/Element/Name.php';
require_once 'lib/Zend/Pdf/Element/String/Binary.php';

require_once 'lib/Zend/Pdf/Trailer.php';
require_once 'lib/Zend/Pdf/Trailer/Generator.php';

require_once 'lib/Zend/Exception.php';
require_once 'lib/Zend/Pdf/Exception.php';
require_once 'lib/Zend/Pdf/Font.php';
require_once 'lib/Zend/Pdf/Cmap.php';
require_once 'lib/Zend/Pdf/Cmap/ByteEncoding.php';
require_once 'lib/Zend/Pdf/Cmap/ByteEncoding/Static.php';
require_once 'lib/Zend/Pdf/Resource.php';
require_once 'lib/Zend/Pdf/Resource/Font.php';
require_once 'lib/Zend/Pdf/Resource/Font/Simple.php';
require_once 'lib/Zend/Pdf/Resource/Font/Simple/Standard.php';
require_once 'lib/Zend/Pdf/Resource/Font/Simple/Standard/Courier.php';
require_once 'lib/Zend/Pdf/Resource/Font/Simple/Standard/CourierBold.php';
require_once 'lib/Zend/Pdf/Resource/Font/Simple/Standard/Helvetica.php';
require_once 'lib/Zend/Pdf/Resource/Font/Simple/Standard/HelveticaBold.php';
require_once 'lib/Zend/Pdf/Image.php';
require_once 'lib/Zend/Pdf/Resource/ImageFactory.php';
require_once 'lib/Zend/Pdf/Resource/Image.php';
require_once 'lib/Zend/Pdf/Resource/Image/Jpeg.php';
require_once 'lib/Zend/Pdf/UpdateInfoContainer.php';
require_once 'lib/Zend/Pdf/RecursivelyIteratableObjectsContainer.php';

require_once 'lib/Zend/Memory.php';
require_once 'lib/Zend/Memory/Manager.php';
require_once 'lib/Zend/Memory/Container/Interface.php';
require_once 'lib/Zend/Memory/Container.php';
require_once 'lib/Zend/Memory/Container/Locked.php';

require_once 'lib/Zend/Pdf.php';


class PDFGenerator extends \Dorm\Models\DormPlugin
{
    public $plugin_key = "PDFGenerator";
    private $pdf = null;
    
    private $headerHeight = 97;
    private $padLeft = 45;
    private $lineHeight = 15;
    
    function __construct()
    {
        global $dorm;
        $this->_orderRepository         = $dorm->di->get('OrderRepository');
        $this->_clientStationRepository = $dorm->di->get('ClientStationRepository');
    }
    
    function addDocumentHeader($pdf, $pageWidth = 100)
    {
        $pdf->Image('assets/img/decaro.jpg', 15, 5, 42, 17, 'JPG', null, '', true, 150, '', false, false, 1, false, false, false);
        $pdf->Ln(49);
        
        $pdf->SetFont('Helvetica', 'b', 13);
        $pdf->SetY(5, true);
        $pdf->SetX(65, true);
        $pdf->MultiCell($pageWidth, 5, '22 McLellan St.', 0, 'L', 1, 0, '', '', true);
        $pdf->Ln(6);
        $pdf->SetX(65, true);
        $pdf->MultiCell($pageWidth, 5, 'Newark, NJ 07114', 0, 'L', 1, 0, '', '', true);
        
        
        $pdf->SetY(5, true);
        $pdf->SetX(110, true);
        $pdf->MultiCell($pageWidth, 5, '(973) 242-0777', 0, 'L', 1, 0, '', '', true);
        $pdf->Ln(6);
        $pdf->SetX(110, true);
        $pdf->MultiCell($pageWidth, 5, 'Fax: (973) 242-1272', 0, 'L', 1, 0, '', '', true);
    }
    
    function generateOrderPDF($order)
    {
        $clientStation = $this->_clientStationRepository->find($order->client_station_id);
        $leftMargin    = 15;
        $sectionMargin = 15;
        $lineHeight    = 7;
        
        $paymentTypeOptions = array(
            'thirdparty' => 'Third Party',
            'collect' => 'Collect',
            'prepaid' => 'Prepaid'
        );
        $paymentTypeText    = $paymentTypeOptions[$order->payment_type];
        
        //start PDF generation
        require_once(DORM_PATH . '/../tcpdf/tcpdf.php');
        
        //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'ISO-8859-1', false);
        
        $width      = 500;
        $height     = 300;
        $pageLayout = array(
            $width,
            $height
        ); //  or array($height, $width) 
        $pdf        = new TCPDF('l', 'pt', $pageLayout, true, 'ISO-8859-1', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('DeCaro Trucking');
        $pdf->SetTitle('DeCaro Order #' . $order->order_number . ' for ' . $order->client->name);
        $pdf->SetSubject('Order');
        $pdf->SetKeywords('DeCaro, Order, ' . $order->client->name);
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 005', PDF_HEADER_STRING);
        $pdf->setHeaderFont(Array(
            PDF_FONT_NAME_MAIN,
            '',
            PDF_FONT_SIZE_MAIN
        ));
        $pdf->setFooterFont(Array(
            PDF_FONT_NAME_DATA,
            '',
            PDF_FONT_SIZE_DATA
        ));
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
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
        
        // ---------------------------------------------------------
        
        $date_created = date_create()->format('n/j/Y');
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetY(5, true);
        
        # Table parameters
        #
        # Column size, wide (description) column, table indent, row height.
        $smallCol = 30;
        $col      = $smallCol * 2;
        $wideCol  = $smallCol * 3;
        $padding  = 27; // ( $pdf->getPageWidth() - 2 * 10 - $wideCol - 3 * $col ) / 2;
        $line     = 10;
        
        $pageWidth = $pdf->getPageWidth() - $padding;
        
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->SetFillColor(255, 255, 255);
        
        $this->addDocumentHeader($pdf, $pageWidth);
        
        
        // ------------------------------------------------
        
        $yCurr = 5;
        
        $now = date_create();
        
        $pdf->SetFont('', '', 10);
        $this->text($pdf, 'Order date:', $pageWidth - 40, $yCurr, 'R', 0, 50, 10);
        $pdf->SetFont('', 'b', 12);
        $this->text($pdf, $now->format('n-j-Y'), $pageWidth - 40, $yCurr + 5, 'R', 0, 50, 10);
        
        /* Origin address */
        $yCurr          = 26;
        $addressStart   = $yCurr;
        $addressEnd     = $yCurr;
        $addressWidth   = 90;
        $addressSpacing = 15;
        
        if ($order->shipper_enabled == 1) {
            $addressWidth   = 90;
            $addressSpacing = 15;
            $pdf->SetFont('', 'b', 10);
            $this->text($pdf, 'Shipper:', $leftMargin + 1, $yCurr);
            $yCurr = $yCurr + $lineHeight - 2;
            $pdf->SetFont('', '', 10);
            $this->text($pdf, $order->shipper_name, $leftMargin + 5, $yCurr);
            $yCurr = $yCurr + $lineHeight - 2;
            $this->text($pdf, $order->origin_address, $leftMargin + 5, $yCurr);
            $yCurr = $yCurr + $lineHeight - 2;
            $this->text($pdf, $order->origin_city . ', ' . $order->origin_state . ' ' . $order->origin_zipcode, $leftMargin + 5, $yCurr);
            $yCurr = $yCurr + $lineHeight - 2;
            $yCurr += 2;
            $this->line($pdf, $leftMargin, $addressStart, $leftMargin, $yCurr);
            $this->line($pdf, $leftMargin, $yCurr, $leftMargin + $addressWidth - 5, $yCurr);
            $addressEnd = $yCurr;
        }
        
        /* Destination address */
        $yCurr = 26;
        $pdf->SetFont('', 'b', 10);
        $this->text($pdf, 'Consignee:', $leftMargin + 1 + $addressWidth - 5, $addressStart);
        $yCurr = $yCurr + $lineHeight - 2;
        $pdf->SetFont('', '', 10);
        if ($order->destination_name != '') {
            $this->text($pdf, $order->destination_name, $leftMargin + 1 + $addressWidth, $yCurr);
            $yCurr = $yCurr + $lineHeight - 2;
        }
        $this->text($pdf, $order->destination_address, $leftMargin + 1 + $addressWidth, $yCurr);
        $yCurr = $yCurr + $lineHeight - 2;
        $this->text($pdf, $order->destination_city . ', ' . $order->destination_state . ' ' . $order->destination_zipcode, $leftMargin + 1 + $addressWidth, $yCurr);
        $yCurr = $yCurr + $lineHeight - 2;
        $yCurr += 2;
        $this->line($pdf, $leftMargin + $addressWidth - 5, $addressStart, $leftMargin + $addressWidth - 5, $yCurr);
        $this->line($pdf, $leftMargin + $addressWidth - 5, $yCurr, $leftMargin + $addressWidth + $addressWidth + $addressSpacing - 8, $yCurr);
        
        if ($yCurr > $addressEnd)
            $addressEnd = $yCurr;
        
        $yCurr += 1;
        $pdf->SetFont('', 'b', 11);
        $this->text($pdf, 'Order #:', $leftMargin, $yCurr, 'L', 0, 45, 10);
        $pdf->SetFont('', '', 11);
        $this->text($pdf, $order->order_number, $leftMargin + 17, $yCurr);
        $yCurr += 6;
        
        if ($order->customer_number != '') {
            $pdf->SetFont('', 'b', 11);
            $this->text($pdf, 'Customer Reference:', $leftMargin, $yCurr, 'L', 0, 45, 10);
            $pdf->SetFont('', '', 12);
            $this->text($pdf, $order->customer_number, $leftMargin + 42, $yCurr);
        }
        
        if ($order->payment_type == 'thirdparty') {
            $yCurr = $addressEnd + 2;
            
            $pdf->SetFont('', 'b', 10);
            $this->text($pdf, '(Third Party address):', $leftMargin + $addressWidth - 4, $yCurr);
            $pdf->SetFont('', '', 10);
            $yCurr = $yCurr + $lineHeight - 2;
            $this->text($pdf, $order->third_party_address, $leftMargin + $addressWidth + 1, $yCurr);
            $yCurr = $yCurr + $lineHeight - 2;
            if (!empty($order->third_party_address2)) {
                $this->text($pdf, $order->third_party_address2, $leftMargin + $addressWidth + 1, $yCurr);
                $yCurr = $yCurr + $lineHeight - 2;
            }
            $this->text($pdf, $order->third_party_city . ', ' . $order->third_party_state . ' ' . $order->third_party_zipcode, $leftMargin + $addressWidth + 1, $yCurr);
            
            $this->line($pdf, $leftMargin + $addressWidth - 5, $addressEnd, $leftMargin + $addressWidth - 5, $yCurr + 10);
            
            $yCurr -= 8;
        }
        
        if ($order->status == 'COMPLETE' && false) {
            $yCurr = $yCurr + $lineHeight - 3;
            $pdf->SetFont('', 'b', 11);
            $this->text($pdf, 'Order completed:', $leftMargin, $yCurr, 'R', 0, 45, 10);
            $pdf->SetFont('', '', 12);
            $this->text($pdf, date_create($order->pod_date)->format('n/j/Y'), $leftMargin + 23, $yCurr, 'R', 0, 45, 10);
            //$this->text($pdf, date_create($order->pod_date)->format('n/j/Y g:ia'), $leftMargin+31, $yCurr);
            $yCurr = $yCurr + $lineHeight - 1;
            
            $pdf->SetFont('', 'b', 11);
            $this->text($pdf, 'Signed by:', $leftMargin, $yCurr, 'R', 0, 45, 10);
            $pdf->SetFont('', '', 11);
            $this->text($pdf, $order->pod_signature, $leftMargin + 23, $yCurr, 'R', 0, 45, 10);
        } else if ($order->payment_type == 'thirdparty') {
            $yCurr += $lineHeight * 2 - 6;
        }
        
        
        $yCurr = $yCurr + $lineHeight * 2 - 5;
        
        $this->line($pdf, $leftMargin, $yCurr, $pageWidth + 13, $yCurr, 2, 0);
        $yCurr += $lineHeight - 3;
        
        $pdf->SetFont('', 'b', 12);
        $this->text($pdf, 'Pcs', $leftMargin, $yCurr, 'R', 0, 15, 10);
        $this->text($pdf, 'Description', $leftMargin + 20, $yCurr, 'L', 0, 120, 10);
        $this->text($pdf, 'Weight', $leftMargin + 140, $yCurr, 'R', 0, 25, 10);
        $this->text($pdf, 'Type', $leftMargin + 160, $yCurr, 'R', 0, 25, 10);
        
        $yCurr = $yCurr + 8;
        $pdf->SetFont('', '', 11);
        $this->text($pdf, $order->pieces, $leftMargin, $yCurr, 'R', 0, 15, 10);
        
        $pdf->setY($yCurr);
        $pdf->setX(35);
        $this->text($pdf, $order->description, $leftMargin + 20, $yCurr, 'L', 0, 120, 10);
        
        $this->text($pdf, $order->weight, $leftMargin + 140, $yCurr, 'R', 0, 20, 10);
        
        $this->text($pdf, $order->payment_type, $leftMargin + 160, $yCurr, 'R', 0, 25, 10);
        
        $yCurr = $yCurr + $lineHeight;
        
        //Fuel surcharge
        if ($order->fuel_surcharge != "") {
            $yCurr = $yCurr + $lineHeight;
            $pdf->SetFont('', 'b', 12);
            $this->text($pdf, 'Fuel Surcharge: ', $leftMargin, $yCurr);
            $pdf->SetFont('', '', 12);
            $this->text($pdf, $order->fuel_surcharge . '%', $leftMargin + 35, $yCurr);
        }
        
        switch ($order->payment_type) {
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
        
        //move total down if description is long
        $cellHeight = 8;
        if (strlen($order->description) > 200) {
            $cellHeight = 22;
        } else if (strlen($order->description) > 100) {
            $cellHeight = 16;
        }
        
        $yCurr += $cellHeight;
        
        setlocale(LC_MONETARY, 'en_US.ISO-8859-1');
        
        $pdf->SetFont('', 'b', 13);
        $moneyString = $order->pod_total; //money_format('%.2n', str_replace('$','',str_replace(',','',$order->pod_total)));
        $this->text($pdf, $moneyString, $pageWidth - 18, $yCurr + 20, 'R', 0, 30, 5);
        
        $this->text($pdf, 'Order Total: ', $pageWidth - 51 - (strlen($moneyString)), $yCurr + 20, 'R', 0, 50, 5);
        $pdf->SetFont('', '', 13);
        
        $pdf->Output('order_' . $date_created . '.pdf', 'I');
    }
    
    function stringWidth($string, $font, $fontSize)
    {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters    = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs      = $font->glyphNumbersForCharacters($characters);
        $widths      = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }
    
    function drawManifestHeader($page, $invoice = null)
    {
        $padTop       = 15;
        $headerHeight = $this->headerHeight / 2; // (header of the logo image)
        $lineHeight   = 15;
        
        $w = $page->getWidth();
        $h = $page->getHeight();
        
        $image = Zend_Pdf_Image::imageWithPath(getcwd() . "/assets/img/decaro.jpg");
        $page->drawImage($image, 90, $h - (97 / 2) - $padTop, (243 / 2) + 107, $h - $padTop);
        
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $page->setFont($font, 12);
        $ts = '22 McLellan St. - Newark, NJ 07114 - (973) 242-0777 - Fax: (973) 242-1272';
        $page->drawText($ts, ($w - $this->stringWidth($ts, $font, 12)) / 2, $h - $headerHeight - $lineHeight - $padTop);
        $page->setFont($font, 10);
        $page->drawText('www.decarotrucking.com', $w / 2 - 60, $h - $headerHeight - $lineHeight + 2);
        
        $page->setFont($font, 16);
        $page->drawText('MANIFEST', $w / 2 - 31, $h - $lineHeight * 3 + 5);
        
        $padRight = 90;
        $page->drawLine($w - 140 - $padRight, $h - $padTop - 5, $w - 140 - $padRight, $h - $padTop - 40 - 5);
        $page->drawLine($w - 140 - $padRight, $h - $padTop - 5, $w - $padRight, $h - $padTop - 5);
        $page->drawLine($w - $padRight, $h - $padTop - 5, $w - $padRight, $h - $padTop - 40 - 5);
        $page->drawLine($w - $padRight, $h - $padTop - 40 - 5, $w - 140 - $padRight, $h - $padTop - 40 - 5);
        
        $page->setFont($font, 12);
        
        if ($invoice)
            $page->drawText($invoice->invoice_number, ($w - 70 - $padRight) - $this->stringWidth($invoice->invoice_number, $font, 12) / 2, $h - $lineHeight * 3);
    }
    
    function drawInvoiceHeader($page, $invoice = null)
    {
        $padTop       = 15;
        $headerHeight = $this->headerHeight / 2; // (header of the logo image)
        $lineHeight   = 15;
        
        $w = $page->getWidth();
        $h = $page->getHeight();
        
        $image = Zend_Pdf_Image::imageWithPath(getcwd() . "/assets/img/decaro.jpg");
        $page->drawImage($image, 20, $h - (97 / 2) - $padTop, (243 / 2) + 47, $h - $padTop);
        
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $page->setFont($font, 12);
        $ts = '22 McLellan St. - Newark, NJ 07114 - (973) 242-0777 - Fax: (973) 242-1272';
        $page->drawText($ts, ($w - $this->stringWidth($ts, $font, 12)) / 2, $h - $headerHeight - $lineHeight - $padTop);
        $page->setFont($font, 10);
        $page->drawText('www.decarotrucking.com', $w / 2 - 60, $h - $headerHeight - $lineHeight + 2);
        
        $page->setFont($font, 16);
        $page->drawText('INVOICE', $w / 2 - 31, $h - $lineHeight * 3 + 5);
        
        $padRight = 20;
        $page->drawLine($w - 140 - $padRight, $h - $padTop - 5, $w - 140 - $padRight, $h - $padTop - 40 - 5);
        $page->drawLine($w - 140 - $padRight, $h - $padTop - 5, $w - $padRight, $h - $padTop - 5);
        $page->drawLine($w - $padRight, $h - $padTop - 5, $w - $padRight, $h - $padTop - 40 - 5);
        $page->drawLine($w - $padRight, $h - $padTop - 40 - 5, $w - 140 - $padRight, $h - $padTop - 40 - 5);
        
        $page->setFont($font, 12);
        
        if ($invoice) {
            $page->drawText($invoice->invoice_number, ($w - 70 - $padRight) - $this->stringWidth($invoice->invoice_number, $font, 12) / 2, $h - $lineHeight * 3);
        }
    }
    
    function generateInvoicePDF($invoice)
    {
        $pdf                     = new Zend_Pdf();
        $page                    = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER);
        $font                    = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $fontBold                = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $w                       = $page->getWidth();
        $h                       = $page->getHeight();
        $hCurr                   = $h - 10;
        $DESCRIPTION_LINE_LENGTH = 35;
        $currPage                = 1;
        
        // Header
        $this->drawInvoiceHeader($page, $invoice);
        $hCurr -= $this->headerHeight;
        
        // Client
        $invoiceDate   = date_create()->format('n-j-Y');
        $clientStation = $this->_clientStationRepository->find($invoice->client_station_id);
        $page->setFont($fontBold, 12);
        $page->drawText('CLIENT', 0 + $this->padLeft * 2, $hCurr);
        $page->drawText('DATE', $w - $this->padLeft * 5, $hCurr);
        
        $page->setFont($font, 11);
        $this->lineHeight = 12;
        
        if (!empty($invoice->invoice_date)) {
            $page->drawText(date_create($invoice->invoice_date)->format('n/j/Y'), $w - $this->padLeft * 5, $hCurr -= $this->lineHeight + 3);
        } else {
            $page->drawText(date_create($invoice->date_from)->format('n/j/Y'), $w - $this->padLeft * 5, $hCurr -= $this->lineHeight + 3);
        }
        $page->drawText($invoice->client->name, 0 + $this->padLeft * 2, $hCurr);
        $page->drawText($clientStation->address, 0 + $this->padLeft * 2, $hCurr -= $this->lineHeight);
        if (!empty($clientStation->address2)) {
            $page->drawText($clientStation->address2, 0 + $this->padLeft * 2, $hCurr -= $this->lineHeight);
        }
        $page->drawText($clientStation->city . ', ' . $clientStation->state . ' ' . $clientStation->zipcode, 0 + $this->padLeft * 2, $hCurr -= $this->lineHeight);
        
        $hCurr -= 13;
        $page->drawText($clientStation->phone_number, 0 + $this->padLeft * 2, $hCurr);
        
        
        $this->lineHeight = 15;
        $hCurr -= 15;
        
        $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
        $hCurr -= 15;
        $xCurr = 30;
        $page->setFont($fontBold, 11);
        
        $page->drawText('ORDER #', $xCurr, $hCurr);
        $page->drawText('DATE', $xCurr += 80, $hCurr);
        $page->drawText('PCS.', $xCurr += 70, $hCurr);
        $page->drawText('WEIGHT', $xCurr += 50, $hCurr);
        $page->drawText('DESCRIPTION', $xCurr += 60, $hCurr);
        $page->drawText('CHARGES', $xCurr += 220, $hCurr);
        $hCurr -= 7;
        $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
        
        // Orders
        $orderTotals = 0;
        $page->setFont($font, 10);
        $xCurr = 30;
        $hCurr -= $this->lineHeight + 5;
        
        /*         $invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        $invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        $invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        $invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        $invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7;
        $invoice->order_ids[] = 7; */
        
        
        foreach ($invoice->order_ids as $oid) {
            // Todo: if we are past the current page height, start a new page...
            $order = $this->_orderRepository->find($oid);
            $pad   = 0;
            
            if (!$order) {
                throw new DormException("Could not locate an order for invoice generation. Order ID: " . $oid);
            }
            
            // Make a new page if we have to
            if ($hCurr < 0 + 50) {
                $pdf->pages[] = $page;
                $page         = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER);
                $this->drawInvoiceHeader($page, $invoice);
                $currPage++;
                
                $hCurr = $h - 105;
                
                $clientHeader = 'CLIENT: ' . $invoice->client->name;
                $page->drawText($clientHeader, ($w / 2) - ($this->stringWidth($clientHeader, $font, 10) / 2) - 8, $hCurr);
                
                $hCurr -= 14;
                $page->setFont($font, 10);
                $ts = '( INVOICE PAGE # ' . $currPage . ' )';
                $page->drawText($ts, ($w / 2) - ($this->stringWidth($ts, $font, 10) / 2), $hCurr);
                
                $hCurr -= 15;
                $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
                $hCurr -= 15;
                $xCurr = 30;
                $page->setFont($fontBold, 11);
                $page->drawText('ORDER #', $xCurr, $hCurr);
                $page->drawText('DATE', $xCurr += 80, $hCurr);
                $page->drawText('PCS.', $xCurr += 70, $hCurr);
                $page->drawText('WEIGHT', $xCurr += 50, $hCurr);
                $page->drawText('DESCRIPTION', $xCurr += 60, $hCurr);
                $page->drawText('CHARGES', $xCurr += 220, $hCurr);
                $hCurr -= 7;
                $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
                
                $page->setFont($font, 10);
                
                $xCurr = 30;
                $hCurr -= 20;
            }
            
            $page->drawText($order->order_number, $xCurr, $hCurr);
            $page->drawText(date_create($order->date_created)->format('n/j/Y'), $xCurr += 80, $hCurr);
            $page->drawText($order->pieces, $xCurr += 70, $hCurr);
            $page->drawText($order->weight, $xCurr += 50, $hCurr);
            
            $orderPrice = $order->pod_total ? (str_replace('$', '', $order->pod_total)) : 0;
            $page->drawText('$' . $orderPrice, $xCurr += 280, $hCurr);
            
            //$order->description .= ' teset test  effe fef efee fwte st et ete test ste te s etes te stes tes. set est este ';
            $xCurr -= 220;
            $lineDelim = 0;
            
            // Only nl2br was working, explode \n didn't
            $description = nl2br($order->description);
            $descrLines  = explode('<br />', $description);
            
            foreach ($descrLines as $line) {
                //wordwrap this line if necessary
                if (strlen($line) > $DESCRIPTION_LINE_LENGTH) {
                    $lineBroken = wordwrap($line, $DESCRIPTION_LINE_LENGTH, '\n', true);
                    $lineParts  = explode('\n', $lineBroken);
                    
                    foreach ($lineParts as $l) {
                        // echo 'l:' . $l . '<br>';
                        if ($l !== '') {
                            $hCurr -= $lineDelim;
                            $page->drawText(strip_tags(ltrim($l)), $xCurr, $hCurr, 'ISO-8859-1');
                            $lineDelim = 12;
                        }
                    }
                } else {
                    $hCurr -= $lineDelim;
                    $page->drawText(strip_tags(ltrim($line)), $xCurr, $hCurr, 'ISO-8859-1');
                    $lineDelim = 12;
                }
            }
            // $page->drawText( $description, $xCurr+=60, $hCurr );
            $hCurr -= $this->lineHeight + 5;
            $xCurr = 30;
        }
        
        // See if there's enough room on the current page for the liability + signature at the bottom, otherwise need another page:
        if ($hCurr - 130 < 0) {
            // Make new page for order
            $pdf->pages[] = $page;
            $page         = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER);
            $this->drawInvoiceHeader($page, $invoice);
            $currPage++;
            
            $hCurr = $h - 105;
            
            $clientHeader = 'CLIENT: ' . $invoice->client->name;
            $page->drawText($clientHeader, ($w / 2) - ($this->stringWidth($clientHeader, $font, 10) / 2) - 8, $hCurr);
            
            $hCurr -= 14;
            $page->setFont($font, 10);
            $ts = '( INVOICE PAGE # ' . $currPage . ' )';
            $page->drawText($ts, ($w / 2) - ($this->stringWidth($ts, $font, 10) / 2), $hCurr);
            $page->setFont($font, 10);
            $xCurr = 30;
            $hCurr -= 15;
        }
        $hCurr -= 5;
        
        // Draw total --------------------------------
        // (put at bottom of page)
        $hCurr    = 130;
        $padTop   = 15;
        $padRight = 60;
        $page->setFont($fontBold, 16);
        $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
        $hCurr -= 20;
        $page->drawText('TOTAL:', $w - 250, $hCurr - 10);
        $hCurr += $this->lineHeight;
        $page->drawLine($w - 120 - $padRight, $hCurr - 5, $w - 120 - $padRight, $hCurr - 30 - 5);
        $page->drawLine($w - 120 - $padRight, $hCurr - 5, $w - $padRight, $hCurr - 5);
        $page->drawLine($w - $padRight, $hCurr - 5, $w - $padRight, $hCurr - 30 - 5);
        $page->drawLine($w - $padRight, $hCurr - 30 - 5, $w - 120 - $padRight, $hCurr - 30 - 5);
        $moneyString = str_replace('$', '', str_replace(',', '', $invoice->total));
        $moneyString = '$' . number_format(floatval($moneyString), 2);
        $page->drawText($moneyString, $w - 120 - ($this->stringWidth($moneyString, $fontBold, 16) / 2), $hCurr - 25);
        $hCurr -= $this->lineHeight * 3;
        $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
        
        // Draw liability
        $page->setFont($fontBold, 12);
        $ts = 'PAYMENT DUE WITHIN 30 DAYS OF INVOICE DATE';
        $page->drawText($ts, $w / 2 - ($this->stringWidth($ts, $fontBold, 12) / 2), $hCurr -= $this->lineHeight);
        
        $lineDelim = 0;
        $page->setFont($font, 9);
        $liabilityLines = explode('\n', wordwrap(strtoupper("Liability, including negligence is limited to the sum of $50.00 per shipment, unless a greater valuation shall be paid for or agreed to be paid in writing to DeCaro Trucking prior to shipping."), 100, '\n', true));
        $hCurr -= $this->lineHeight;
        foreach ($liabilityLines as $line) {
            if ($line !== '') {
                $hCurr -= $lineDelim;
                $page->drawText(strip_tags(ltrim($line)), $xCurr, $hCurr, 'ISO-8859-1');
                $lineDelim = 10;
            }
        }
        //$page->drawText(strtoupper($ts), , $hCurr -= $this->lineHeight);
        $page->setFont($fontBold, 9);
        $msg = "DECARO TRUCKING WILL NOT BE RESPONSIBLE FOR DAMAGES DUE TO POOR PACKING BY SHIPPER.";
        $page->drawText(strtoupper($msg), 30, $hCurr -= 10);
        
        $pdf->pages[] = $page;
        $pdfSource    = $pdf->render();
        
        return $pdfSource;
    }
    
    function generateManifestPDF($invoice)
    {
        $pdf                     = new Zend_Pdf();
        $page                    = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER);
        $font                    = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $fontBold                = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $w                       = $page->getWidth();
        $h                       = $page->getHeight();
        $hCurr                   = $h - 5;
        $DESCRIPTION_LINE_LENGTH = 35;
        $currPage                = 1;
        
        // Header
        $this->drawManifestHeader($page, $invoice);
        $hCurr -= $this->headerHeight;
        
        // Client
        $invoiceDate   = date_create()->format('n-j-Y');
        $clientStation = $this->_clientStationRepository->find($invoice->client_station_id);
        $page->setFont($fontBold, 12);
        $page->drawText('CLIENT', 0 + $this->padLeft * 2, $hCurr);
        $page->drawText('DATE', $w - $this->padLeft * 5, $hCurr);
        
        $page->setFont($font, 11);
        $this->lineHeight = 12;
        
        $page->drawText(date_create($invoice->date_from)->format('n/j/Y'), $w - $this->padLeft * 5, $hCurr -= $this->lineHeight + 3);
        $page->drawText($invoice->client->name, 0 + $this->padLeft * 2, $hCurr);
        $page->drawText($clientStation->address, 0 + $this->padLeft * 2, $hCurr -= $this->lineHeight);
        if (!empty($clientStation->address2)) {
            $page->drawText($clientStation->address2, 0 + $this->padLeft * 2, $hCurr -= $this->lineHeight);
        }
        $page->drawText($clientStation->city . ', ' . $clientStation->state . ' ' . $clientStation->zipcode, 0 + $this->padLeft * 2, $hCurr -= $this->lineHeight);
        
        $this->lineHeight = 15;
        $hCurr -= 15;
        
        $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
        $hCurr -= 15;
        $xCurr = 30;
        $page->setFont($fontBold, 11);
        
        $page->drawText('ORDER #', $xCurr, $hCurr);
        $page->drawText('DATE', $xCurr += 80, $hCurr);
        $page->drawText('PCS.', $xCurr += 70, $hCurr);
        $page->drawText('WEIGHT', $xCurr += 50, $hCurr);
        $page->drawText('DESCRIPTION', $xCurr += 60, $hCurr);
        $page->drawText('CHARGES', $xCurr += 220, $hCurr);
        $hCurr -= 7;
        $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
        
        // Orders
        $orderTotals = 0;
        $page->setFont($font, 10);
        $xCurr = 30;
        $hCurr -= $this->lineHeight + 5;
        
        //$invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        //$invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        //$invoice->order_ids = array_merge($invoice->order_ids, $invoice->order_ids);
        // $invoice->order_ids[] = 7;
        // $invoice->order_ids[] = 7;
        // $invoice->order_ids[] = 7;
        // $invoice->order_ids[] = 7;
        // $invoice->order_ids[] = 7;
        
        foreach ($invoice->order_ids as $oid) {
            // Todo: if we are past the current page height, start a new page...
            $order = $this->_orderRepository->find($oid);
            $pad   = 0;
            
            if (!$order) {
                throw new DormException("Could not locate an order for invoice generation. Order ID: " . $oid);
            }
            
            // Make a new page if we have to:
            if ($hCurr < 0 + 50) {
                $pdf->pages[] = $page;
                $page         = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER);
                $this->drawManifestHeader($page, $invoice);
                $currPage++;
                
                $hCurr = $h - 105;
                $page->setFont($font, 10);
                $ts = '( MANIFEST PAGE # ' . $currPage . ' )';
                $page->drawText($ts, ($w / 2) - ($this->stringWidth($ts, $font, 10) / 2), $hCurr);
                
                $hCurr -= 10;
                $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
                $hCurr -= 15;
                $xCurr = 30;
                $page->setFont($fontBold, 11);
                $page->drawText('ORDER #', $xCurr, $hCurr);
                $page->drawText('DATE', $xCurr += 80, $hCurr);
                $page->drawText('PCS.', $xCurr += 70, $hCurr);
                $page->drawText('WEIGHT', $xCurr += 50, $hCurr);
                $page->drawText('DESCRIPTION', $xCurr += 60, $hCurr);
                $page->drawText('CHARGES', $xCurr += 220, $hCurr);
                $hCurr -= 7;
                $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
                
                $page->setFont($font, 10);
                
                $xCurr = 30;
                $hCurr -= 20;
            }
            
            $page->drawText($order->order_number, $xCurr, $hCurr);
            $page->drawText(date_create($order->date_created)->format('n/j/Y'), $xCurr += 80, $hCurr);
            $page->drawText($order->pieces, $xCurr += 70, $hCurr);
            $page->drawText($order->weight, $xCurr += 50, $hCurr);
            
            $orderPrice = $order->pod_total ? (str_replace('$', '', $order->pod_total)) : 0;
            $page->drawText('$' . $orderPrice, $xCurr += 280, $hCurr);
            
            //$order->description .= 'teset test  effe fef efee fwte st et ete test ste te s etes te stes tes. set est este ';
            $xCurr -= 220;
            $lineDelim          = 0;
            $order->description = wordwrap($order->description, $DESCRIPTION_LINE_LENGTH, '\n', true);
            $descrLines         = explode('\n', $order->description);
            foreach ($descrLines as $line) {
                if ($line !== '') {
                    $hCurr -= $lineDelim;
                    $page->drawText(strip_tags(ltrim($line)), $xCurr, $hCurr, 'ISO-8859-1');
                    $lineDelim = 12;
                }
            }
            //$page->drawText( $order->description, $xCurr+=60, $hCurr );
            $hCurr -= $this->lineHeight + 5;
            $xCurr = 30;
        }
        
        // See if there's enough room on the current page for the liability + signature at the bottom, otherwise need another page:
        if ($hCurr - 130 < 0) {
            // Make new page for order
            $pdf->pages[] = $page;
            $page         = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER);
            $this->drawManifestHeader($page, $invoice);
            $currPage++;
            
            $hCurr = $h - 105;
            $page->setFont($font, 10);
            $ts = '( MANIFEST PAGE # ' . $currPage . ' )';
            $page->drawText($ts, ($w / 2) - ($this->stringWidth($ts, $font, 10) / 2), $hCurr);
            $page->setFont($font, 10);
            $xCurr = 30;
            $hCurr -= 15;
        }
        $hCurr -= 5;
        
        // Draw total --------------------------------
        // (put at bottom of page)
        $hCurr    = 130;
        $padTop   = 15;
        $padRight = 60;
        $page->setFont($fontBold, 16);
        $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
        $hCurr -= 20;
        $page->drawText('TOTAL:', $w - 250, $hCurr - 10);
        $hCurr += $this->lineHeight;
        $page->drawLine($w - 120 - $padRight, $hCurr - 5, $w - 120 - $padRight, $hCurr - 30 - 5);
        $page->drawLine($w - 120 - $padRight, $hCurr - 5, $w - $padRight, $hCurr - 5);
        $page->drawLine($w - $padRight, $hCurr - 5, $w - $padRight, $hCurr - 30 - 5);
        $page->drawLine($w - $padRight, $hCurr - 30 - 5, $w - 120 - $padRight, $hCurr - 30 - 5);
        $moneyString = str_replace('$', '', str_replace(',', '', $invoice->total));
        $moneyString = '$' . number_format(floatval($moneyString), 2);
        $page->drawText($moneyString, $w - 120 - ($this->stringWidth($moneyString, $fontBold, 16) / 2), $hCurr - 25);
        $hCurr -= $this->lineHeight * 3;
        $page->drawLine(0 + 15, $hCurr, $w - 15, $hCurr);
        
        // Draw liability
        $page->setFont($fontBold, 12);
        $ts = 'PAYMENT DUE WITHIN 30 DAYS OF INVOICE DATE';
        $page->drawText($ts, $w / 2 - ($this->stringWidth($ts, $fontBold, 12) / 2), $hCurr -= $this->lineHeight);
        
        $lineDelim = 0;
        $page->setFont($font, 9);
        $liabilityLines = explode('\n', wordwrap(strtoupper("Liability, including negligence is limited to the sum of $50.00 per shipment, unless a greater valuation shall be paid for or agreed to be paid in writing to DeCaro Trucking prior to shipping."), 100, '\n', true));
        $hCurr -= $this->lineHeight;
        foreach ($liabilityLines as $line) {
            if ($line !== '') {
                $hCurr -= $lineDelim;
                $page->drawText(strip_tags(ltrim($line)), $xCurr, $hCurr, 'ISO-8859-1');
                $lineDelim = 10;
            }
        }
        //$page->drawText(strtoupper($ts), , $hCurr -= $this->lineHeight);
        $page->setFont($fontBold, 9);
        $msg = "DECARO TRUCKING WILL NOT BE RESPONSIBLE FOR DAMAGES DUE TO POOR PACKING BY SHIPPER.";
        $page->drawText(strtoupper($msg), 30, $hCurr -= 10);
        
        $pdf->pages[] = $page;
        $pdfSource    = $pdf->render();
        
        return $pdfSource;
    }
	
/*
    function generateInvoicePDF_old($invoice)
    {
        $invoice_date_created = date_create()->format('n-j-Y');
        $clientStation        = $this->_clientStationRepository->find($invoice->client_station_id);
        
        //start PDF generation
        require_once(DORM_PATH . '/../tcpdf/tcpdf.php');
        
        $pdf = $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'ISO-8859-1', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('DeCaro Trucking');
        $pdf->SetTitle('DeCaro Invoice for ' . $invoice->client->name);
        $pdf->SetSubject('Invoice');
        $pdf->SetKeywords('DeCaro, Invoice, ' . $invoice->client->name);
        
        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 005', PDF_HEADER_STRING);
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(
            PDF_FONT_NAME_MAIN,
            '',
            PDF_FONT_SIZE_MAIN
        ));
        $pdf->setFooterFont(Array(
            PDF_FONT_NAME_DATA,
            '',
            PDF_FONT_SIZE_DATA
        ));
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
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetY(5, true);
        
        // ---------------------------------------------------------
        
        $xsCol    = 15;
        $smallCol = 15 * 2;
        $col      = $smallCol * 2;
        $wideCol  = $smallCol * 3;
        $padding  = 27; // ( $pdf->getPageWidth() - 2 * 10 - $wideCol - 3 * $col ) / 2;
        $line     = 10;
        
        $leftMargin = 15;
        $lineHeight = 7;
        $pageWidth  = $pdf->getPageWidth() - $padding;
        $pageHeight = $pdf->getPageHeight() - $padding;
        $col2       = 85;
        
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->SetFillColor(255, 255, 255);
        
        $this->addDocumentHeader($pdf, $pageWidth);
        
        $this->fullLine($yCurr = $lineHeight * 2 + 11);
        
        $yCurr += 2;
        $pdf->SetFont('', 'b', 8);
        $this->text($pdf, "INVOICE NUMBER:", $pageWidth - 45, $yCurr + 1);
        $pdf->SetFont('', '', 11);
        $this->text($pdf, $invoice->invoice_number, $pageWidth - 17, $yCurr + .2, 'R', 0, 35);
        $pdf->SetFont('', 'b', 8);
        $this->text($pdf, "INVOICE DATE:", $pageWidth - 40.5, $yCurr += 7);
        $pdf->SetFont('', '', 11);
        $this->text($pdf, date_create($invoice->date_from)->format('n/j/Y'), $pageWidth - 17, $yCurr - 1, 'R', 0, 35);
        
        $leftMargin -= 1;
        $yCurr -= 5;
        $pdf->SetFont('', 'b', 8);
        $this->text($pdf, "BILLED TO:", $leftMargin, $yCurr);
        $pdf->SetFont('', 'b', 11);
        $this->text($pdf, $invoice->client->name, $leftMargin, $yCurr += 5);
        $pdf->SetFont('', '', 11);
        $this->text($pdf, $clientStation->address, $leftMargin, $yCurr += $lineHeight - 2);
        $pdf->Ln(6);
        $yCurr += $lineHeight - 2;
        if (!empty($clientStation->address2)) {
            $this->text($pdf, $clientStation->address2, $leftMargin, $yCurr);
            $yCurr += $lineHeight - 2;
        }
        $this->text($pdf, $clientStation->city . ', ' . $clientStation->state . ' ' . $clientStation->zipcode, $leftMargin, $yCurr);
        $pdf->Ln(6);
        
        $yCurr += $lineHeight + 5;
        
        
        $pdf->SetFont('', 'b', 12);
        
        //$this->text($pdf, 'Invoice Items:', $leftMargin, $yCurr);
        
        $pdf->SetY($yCurr, true);
        
        $style = array(
            'width' => 10,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => array(
                200,
                200,
                200
            )
        );
        $pdf->Line(7, $yCurr += 5, $pageWidth + 20, $yCurr, $style);
        
        $pdf->SetFont('', 'b', 11);
        $pdf->Cell($padding / 2);
        $pdf->Cell($smallCol - 28, $line, 'Order #', 0, 0, 'R');
        $pdf->Cell($smallCol - 8, $line, 'Shipped', 0, 0, 'R');
        $pdf->Cell($xsCol - 1, $line, 'Pcs.', 0, 0, 'R');
        $pdf->Cell($xsCol - 4, $line, 'Wt.', 0, 0, 'R');
        $pdf->Cell($xsCol + 2, $line, 'Type', 0, 0, 'R');
        $pdf->Cell($xsCol + 10, $line, 'Description', 0, 0, 'R');
        $pdf->Cell($smallCol + 57, $line, 'Total', 0, 0, 'R');
        $pdf->Ln();
        
        $yCurr += 7;
        
        $currY = $pdf->GetY();
        
        $orderTotals = 0;
        
        foreach ($invoice->order_ids as $oid) {
            $order = $this->_orderRepository->find($oid);
            $pad   = 0;
            
            if (!$order) {
                throw new DormException("Could not locate an order for invoice generation. Order ID: " . $oid);
            }
            
            $pdf->SetFont('', '', 10);
            
            $colCurr = $leftMargin - 15;
            $this->text($pdf, $order->order_number, $colCurr, $yCurr, 'R', 0, 30, 5);
            $colCurr += $smallCol - 17;
            $this->text($pdf, date_create($order->date_created)->format('n/j/Y'), $colCurr + .5, $yCurr, 'R', 0, 40, 10);
            $colCurr += $smallCol - 10;
            $this->text($pdf, $order->pieces + '11', $colCurr + 2, $yCurr, 'R', 0, 30, 5);
            $colCurr += $smallCol - 15;
            $this->text($pdf, $order->weight, $colCurr, $yCurr, 'R', 0, 30, 5);
            $colCurr += $smallCol - 13;
            $this->text($pdf, $order->delivery_type, $colCurr, $yCurr, 'R', 0, 30, 5);
            $pdf->SetFont('', '', 10);
            $colCurr += $smallCol - 3;
            $cellHeight = 8;
            if (strlen($order->description) > 200) {
                $cellHeight = 22;
                $pad += 2;
            } else if (strlen($order->description) > 100) {
                $cellHeight = 16;
                $pad += 1;
            }
            $pdf->MultiCell(85, $cellHeight - 1, $order->description, 0, 'L', 1, 0, $colCurr + 5, $yCurr, true, false, 0, false, false, 0, 'T', true);
            
            $colCurr += $smallCol + 55;
            $orderPrice = $order->pod_total ? (str_replace('$', '', $order->pod_total)) : 0;
            
            $this->text($pdf, '$' . $orderPrice, $colCurr, $yCurr, 'R', 0, 30, 5);
            
            $orderTotals += floatval($orderPrice);
            
            $y2 = $pdf->GetY();
            
            $yCurr += $cellHeight + $pad;
            
            $this->line($pdf, 7, $yCurr, $pageWidth + 20, $yCurr);
            
            $yCurr += 1;
            
            //bottom invoice sign/date/liability\
            
            $yCurr = $pdf->getPageHeight() - 30;
            $this->line($pdf, 7, $yCurr, $pageWidth + 20, $yCurr);
            $pdf->SetFont('', 'b', 12);
            $this->text($pdf, strtoupper('PAYMENT DUE WITHIN 30 DAYS OF INVOICE DATE'), $leftMargin + 45, $yCurr += 2);
            
            $leftMargin = 7;
            $pdf->SetFont('', '', 8);
            $msg = "Liability, including negligence is limited to the sum of $50.00 per shipment, unless a greater valuation shall be paid for or agreed to be paid in writing to DeCaro Trucking prior to shipping.";
            $this->text($pdf, strtoupper($msg), $leftMargin, $yCurr += 7.5);
            $msg = "DECARO TRUCKING WILL NOT BE RESPONSIBLE FOR DAMAGES DUE TO POOR PACKING BY SHIPPER.";
            $this->text($pdf, strtoupper($msg), $leftMargin, $yCurr += 8);
        }
        
        setlocale(LC_MONETARY, 'en_US.ISO-8859-1');
        
        $pdf->SetFont('', 'b', 13);
        $moneyString = $orderTotals; //money_format('%.2n', $orderTotals);
        $this->text($pdf, $moneyString, $pageWidth - 13, $yCurr + 20, 'R', 0, 30, 5);
        
        $this->text($pdf, 'Invoice Total: ', $pageWidth - 47 - (strlen($moneyString)), $yCurr + 20, 'R', 0, 50, 5);
        $pdf->SetFont('', '', 13);
        
        
        $pdf->Output('invoice_' . $invoice_date_created . '.pdf', 'I');
    }
    
    function generateManifestPDF_old($invoice)
    {
        $invoice_date_created = date_create()->format('n-j-Y');
        $clientStation        = $this->_clientStationRepository->find($invoice->client_station_id);
        
        //start PDF generation
        require_once(DORM_PATH . '/../tcpdf/tcpdf.php');
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'ISO-8859-1', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('DeCaro Trucking');
        $pdf->SetTitle('DeCaro Invoice for ' . $invoice->client->name);
        $pdf->SetSubject('Invoice');
        $pdf->SetKeywords('DeCaro, Invoice, ' . $invoice->client->name);
        
        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 005', PDF_HEADER_STRING);
        
        // set header and footer fonts
        $pdf->setHeaderFont(Array(
            PDF_FONT_NAME_MAIN,
            '',
            PDF_FONT_SIZE_MAIN
        ));
        $pdf->setFooterFont(Array(
            PDF_FONT_NAME_DATA,
            '',
            PDF_FONT_SIZE_DATA
        ));
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
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetY(5, true);
        
        // ---------------------------------------------------------
        $xsCol    = 15;
        $smallCol = 15 * 2;
        $col      = $smallCol * 2;
        $wideCol  = $smallCol * 3;
        $padding  = 27; // ( $pdf->getPageWidth() - 2 * 10 - $wideCol - 3 * $col ) / 2;
        $line     = 10;
        
        $leftMargin = 15;
        $lineHeight = 7;
        $pageWidth  = $pdf->getPageWidth() - $padding;
        $pageHeight = $pdf->getPageHeight() - $padding;
        $col2       = 85;
        
        $pdf->setCellPaddings(1, 1, 1, 1);
        $pdf->SetFillColor(255, 255, 255);
        
        $this->addDocumentHeader($pdf, $pageWidth);
        
        $yCurr = 25;
        
        $this->text($pdf, $invoice->invoice_number, $pageWidth - 10, $yCurr);
        
        $pdf->SetFont('', 'b', 12);
        //$this->text($pdf, 'Invoice start date:', $leftMargin+$col2, $yCurr);
        $pdf->SetFont('', '', 12);
        $this->text($pdf, date_create($invoice->date_from)->format('n/j/Y'), $leftMargin + $col2 + 38, $yCurr);
        $yCurr += $lineHeight;
        //$pdf->SetFont( '', 'b', 12);
        //$this->text($pdf, 'Invoice end date:', $leftMargin+$col2, $yCurr);
        //$pdf->SetFont( '', '', 12);
        //$this->text($pdf,date_create($invoice->date_to)->format('n/j/Y'), $leftMargin+$col2+37, $yCurr);
        
        
        $yCurr += $lineHeight;
        $pdf->SetFont('', 'b', 12);
        $this->text($pdf, 'Invoice Total:  $' . number_format((float) $invoice->total, 2, '.', ','), $leftMargin + $col2, $yCurr, 'L', 0, 88, 5);
        $yCurr += $lineHeight;
        $pdf->SetFont('', 'b', 12);
        $this->text($pdf, 'Invoice payment due by:  ' . date_create($invoice->date_due_by)->format('n/j/Y'), $leftMargin + $col2, $yCurr, 'L', 0, 88, 5);
        
        $yCurr -= $lineHeight * 2;
        $pdf->SetFont('', 'b', 12);
        $this->text($pdf, $invoice->client->name, $leftMargin, $yCurr);
        $yCurr += $lineHeight;
        $pdf->SetFont('', '', 11);
        $this->text($pdf, $clientStation->address, $leftMargin, $yCurr);
        $pdf->Ln(6);
        $yCurr += $lineHeight - 2;
        if (!empty($clientStation->address2)) {
            $this->text($pdf, $clientStation->address2, $leftMargin, $yCurr);
            $yCurr += $lineHeight - 2;
        }
        $this->text($pdf, $clientStation->city . ', ' . $clientStation->state . ' ' . $clientStation->zipcode, $leftMargin, $yCurr);
        $pdf->Ln(6);
        
        $yCurr += $lineHeight + 3;
        $style = array(
            'width' => 0.25,
            'cap' => 'butt',
            'join' => 'miter',
            'dash' => 0,
            'color' => array(
                100,
                100,
                100
            )
        );
        
        $yCurr += $lineHeight + 2;
        $pdf->Line($leftMargin, $yCurr - 1, $pageWidth + 12, $yCurr, $style);
        
        $pdf->SetFont('', 'b', 12);
        $pdf->SetY(52, true);
        
        $this->text($pdf, 'Invoice Items:', $leftMargin, $yCurr);
        $pdf->Ln(8);
        
        $yCurr += $lineHeight + 30;
        
        $pdf->SetY($yCurr, true);
        
        $pdf->SetFont('', 'b', 11);
        //$pdf->Cell( $padding / 2 );
        $pdf->Cell($smallCol, $line, 'Order #', 0, 0, 'R');
        $pdf->Cell($smallCol - 7, $line, 'Shipped', 0, 0, 'R');
        $pdf->Cell($xsCol, $line, 'Pieces', 0, 0, 'R');
        $pdf->Cell($xsCol, $line, 'Weight', 0, 0, 'R');
        $pdf->Cell($smallCol + 55, $line, 'Description', 0, 0, 'R');
        $pdf->Cell($smallCol - 10, $line, 'Total', 0, 0, 'R');
        $pdf->Ln();
        
        $yCurr += 11;
        
        $currY = $pdf->GetY();
        
        $pdf->SetFont('', '');
        foreach ($invoice->order_ids as $oid) {
            $order = $this->_orderRepository->find($oid);
            $pad   = 0;
            
            if (!$order) {
                throw new DormException("Could not locate an order for invoice generation. Order ID: " . $oid);
            }
            
            $pdf->SetFont('', '', 11);
            
            $colCurr = $leftMargin;
            $this->text($pdf, $order->order_number, $colCurr, $yCurr, 'R', 0, 30, 5);
            $colCurr += $smallCol - 17;
            $this->text($pdf, date_create($order->date_created)->format('n/j/Y'), $colCurr, $yCurr, 'R', 0, 40, 10);
            $colCurr += $smallCol - 5;
            $this->text($pdf, $order->pieces, $colCurr, $yCurr, 'R', 0, 30, 5);
            $colCurr += $smallCol - 15;
            $this->text($pdf, $order->weight, $colCurr, $yCurr, 'R', 0, 30, 5);
            $pdf->SetFont('', '', 10);
            $colCurr += $smallCol - 3;
            $cellHeight = 8;
            if (strlen($order->description) > 220) {
                $cellHeight = 22;
                $pad += 2;
            } else if (strlen($order->description) > 120) {
                $cellHeight = 15;
                $pad += 2;
            }
            $pdf->MultiCell(84, $cellHeight, $order->description, 0, 'R', 1, 0, $colCurr + 4, $yCurr, true, false, 0, false, false, 0, 'T', true);
            
            $colCurr += $smallCol + 48;
            $this->text($pdf, $order->pod_total ? ('$' . $order->pod_total) : ($order->pod_total), $colCurr, $yCurr, 'R', 0, 30, 5);
            
            $y2 = $pdf->GetY();
            
            $yCurr += $cellHeight + $pad;
        }
        
        $pdf->SetFont('', 'b');
        
        $pdf->Output('invoice_' . $invoice_date_created . '.pdf', 'I');
	}
*/

}

?>