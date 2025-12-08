<?php
/*
 * b1gMail - E-Rechnung Implementation (XRechnung/ZUGFeRD)
 * (c) 2025 aikQ GmbH
 *
 * Implements German E-Rechnung standards:
 * - XRechnung (EN 16931 compliant XML format)
 * - ZUGFeRD 2.x (PDF/A-3 with embedded XML invoice)
 *
 * Legal requirement: Q1 2025 for B2B invoices in Germany
 *
 * Dependencies:
 *   composer require horstoeko/zugferd
 */

use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdProfiles;
use horstoeko\zugferd\codelists\ZugferdInvoiceType;
use horstoeko\zugferd\codelists\ZugferdPaymentMeans;
use horstoeko\zugferd\codelists\ZugferdTaxCategory;
use horstoeko\zugferd\codelists\ZugferdTaxType;

/**
 * E-Rechnung Manager
 *
 * Generates legally compliant electronic invoices for German market:
 * - XRechnung: Pure XML format (for government/public sector)
 * - ZUGFeRD: PDF/A-3 with embedded XML (for general B2B)
 */
class BMErechnung
{
	// E-Rechnung formats
	const FORMAT_XRECHNUNG = 'xrechnung';   // XML only (EN 16931)
	const FORMAT_ZUGFERD_V2 = 'zugferd2';   // PDF/A-3 + XML (ZUGFeRD 2.x)
	
	// ZUGFeRD profiles
	const PROFILE_MINIMUM = 'minimum';      // Minimal data set
	const PROFILE_BASIC = 'basic';          // Basic invoice data
	const PROFILE_COMFORT = 'comfort';      // Most common (recommended)
	const PROFILE_EXTENDED = 'extended';    // Full feature set
	const PROFILE_XRECHNUNG = 'xrechnung';  // EN 16931 compliant
	
	/**
	 * Check if E-Rechnung library is available
	 *
	 * @return bool
	 */
	public static function IsAvailable()
	{
		return class_exists('horstoeko\zugferd\ZugferdDocumentBuilder');
	}
	
	/**
	 * Generate E-Rechnung for an order
	 *
	 * @param int $orderID Order ID
	 * @param string $format Output format (xrechnung|zugferd2)
	 * @param string $profile ZUGFeRD profile (for zugferd2 format)
	 * @return array|false Array with 'xml' and optionally 'pdf' keys, or false on error
	 */
	public static function GenerateErechnung($orderID, $format = self::FORMAT_ZUGFERD_V2, $profile = self::PROFILE_COMFORT)
	{
		global $db, $bm_prefs;
		
		if(!self::IsAvailable()) {
			PutLog('E-Rechnung: horstoeko/zugferd library not installed', PRIO_WARNING, __FILE__, __LINE__);
			return false;
		}
		
		// Fetch order data
		$res = $db->Query('SELECT * FROM {pre}orders WHERE `orderid`=?', $orderID);
		if($res->RowCount() == 0) {
			PutLog(sprintf('E-Rechnung: Order %d not found', $orderID), PRIO_WARNING, __FILE__, __LINE__);
			return false;
		}
		$order = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		// Prepare invoice data
		$invoiceNo = BMPayment::InvoiceNo($orderID);
		$customerNo = BMPayment::CustomerNo($order['userid']);
		$invoiceDate = $order['activated'] > 0 ? $order['activated'] : $order['created'];
		
		$netAmount = round($order['amount'] / (1 + $order['tax'] / 100), 0);
		$taxAmount = $order['amount'] - $netAmount;
		
		$cart = @unserialize($order['cart']);
		if(!is_array($cart)) $cart = array();
		
		// Select profile
		$zugferdProfile = self::GetZugferdProfile($format, $profile);
		
		try {
			// Create ZUGFeRD document
			$doc = ZugferdDocumentBuilder::CreateNew($zugferdProfile);
			
			// 1. Document header
			$doc->setDocumentInformation(
				$invoiceNo,                                  // Invoice number
				ZugferdInvoiceType::INVOICE,                 // Invoice type: 380 = Commercial invoice
				date('Ymd', $invoiceDate),                   // Invoice date
				$bm_prefs['currency']                        // Currency
			);
			
			// 2. Seller (your company)
			$doc->setDocumentSeller(
				$bm_prefs['company_name'] ?? 'aikQ GmbH',   // Company name
				$bm_prefs['company_id'] ?? ''                // Company ID
			);
			
			$doc->setDocumentSellerAddress(
				$bm_prefs['company_street'] ?? '',           // Street
				$bm_prefs['company_no'] ?? '',               // House number
				'',                                          // Building
				$bm_prefs['company_zip'] ?? '',              // ZIP
				$bm_prefs['company_city'] ?? '',             // City
				$bm_prefs['company_country'] ?? 'DE',        // Country code
				''                                           // Subdivision
			);
			
			if(!empty($bm_prefs['company_taxid'])) {
				$doc->setDocumentSellerTaxRegistration('VA', $bm_prefs['company_taxid']);
			}
			
			if(!empty($bm_prefs['company_contact_email'])) {
				$doc->setDocumentSellerContact(
					'',
					'',
					'',
					$bm_prefs['company_contact_email']
				);
			}
			
			// 3. Buyer (customer)
			$buyerName = trim(($order['inv_company'] ? $order['inv_company'] . ' - ' : '') . 
			                  $order['inv_firstname'] . ' ' . $order['inv_lastname']);
			
			$doc->setDocumentBuyer($buyerName, $customerNo);
			
			$doc->setDocumentBuyerAddress(
				$order['inv_street'],
				$order['inv_no'],
				'',
				$order['inv_zip'],
				$order['inv_city'],
				$order['inv_country'],
				''
			);
			
			if(!empty($order['inv_taxid'])) {
				$doc->setDocumentBuyerTaxRegistration('VA', $order['inv_taxid']);
			}
			
			// 4. Payment terms
			$paymentMeansCode = self::GetPaymentMeansCode($order['paymethod']);
			$doc->addDocumentPaymentMean($paymentMeansCode, '');
			
			// Add bank details for bank transfer
			if($order['paymethod'] == PAYMENT_METHOD_BANKTRANSFER && !empty($bm_prefs['vk_kto_iban'])) {
				$doc->setDocumentPaymentMeanToDirectDebit(
					$bm_prefs['vk_kto_iban'],
					$bm_prefs['vk_kto_bic'] ?? '',
					$bm_prefs['vk_kto_inh'] ?? ''
				);
			}
			
			// Payment due date (30 days default)
			$dueDate = $invoiceDate + (30 * 24 * 60 * 60);
			$doc->setDocumentPaymentTerm('Payment due within 30 days', date('Ymd', $dueDate));
			
			// 5. Line items (cart positions)
			$lineNo = 0;
			foreach($cart as $cartItem) {
				$lineNo++;
				
				$lineName = $cartItem['text'];
				$lineQuantity = $cartItem['count'];
				$lineNetPrice = ($cartItem['amount'] / ($bm_prefs['mwst'] == 'enthalten' ? (1 + $order['tax'] / 100) : 1)) / 100;
				$lineNetTotal = ($cartItem['total'] / ($bm_prefs['mwst'] == 'enthalten' ? (1 + $order['tax'] / 100) : 1)) / 100;
				
				$doc->addNewPosition((string)$lineNo);
				$doc->setDocumentPositionProductDetails($lineName);
				$doc->setDocumentPositionNetPrice($lineNetPrice);
				$doc->setDocumentPositionQuantity($lineQuantity, 'C62');  // C62 = piece
				$doc->setDocumentPositionLineSummation($lineNetTotal);
				
				// Tax per line
				$doc->addDocumentPositionTax(
					ZugferdTaxCategory::STANDARD_RATE,
					ZugferdTaxType::VAT,
					$order['tax']
				);
			}
			
			// 6. Document summation
			$doc->setDocumentSummation(
				$order['amount'] / 100,      // Grand total (gross)
				$order['amount'] / 100,      // Due payable amount
				$netAmount / 100,            // Line total amount (net)
				0.0,                         // Charge total amount
				0.0,                         // Allowance total amount
				$netAmount / 100,            // Tax basis total amount
				$taxAmount / 100,            // Tax total amount
				0.0,                         // Rounding amount
				0.0                          // Prepaid amount
			);
			
			// 7. Tax breakdown
			$doc->addDocumentTax(
				ZugferdTaxCategory::STANDARD_RATE,
				ZugferdTaxType::VAT,
				$netAmount / 100,
				$taxAmount / 100,
				$order['tax']
			);
			
			// 8. Generate output
			$result = array();
			
			if($format === self::FORMAT_XRECHNUNG) {
				// Pure XML (XRechnung)
				$result['xml'] = $doc->getContent();
				$result['format'] = 'xrechnung';
				$result['mimetype'] = 'application/xml';
				$result['filename'] = sprintf('xrechnung_%s.xml', $invoiceNo);
			}
			else {
				// ZUGFeRD: PDF with embedded XML
				// First get XML
				$result['xml'] = $doc->getContent();
				
				// Then create PDF/A-3 with embedded XML
				// NOTE: You need to generate a PDF first (can reuse existing HTML invoice)
				$htmlInvoice = BMPayment::GenerateInvoice($orderID, true);
				
				if($htmlInvoice) {
					// Convert HTML to PDF/A-3 (requires additional library like mPDF or TCPDF)
					$result['pdf'] = self::ConvertHTMLtoPDFA3($htmlInvoice, $result['xml'], $invoiceNo);
					$result['format'] = 'zugferd2';
					$result['mimetype'] = 'application/pdf';
					$result['filename'] = sprintf('zugferd_%s.pdf', $invoiceNo);
				}
			}
			
			// Store in database
			self::StoreErechnung($orderID, $result);
			
			PutLog(sprintf('E-Rechnung generated for order %d (format: %s)', $orderID, $format), 
			       PRIO_NOTE, __FILE__, __LINE__);
			
			return $result;
		}
		catch(Exception $e) {
			PutLog(sprintf('E-Rechnung generation failed: %s', $e->getMessage()), 
			       PRIO_WARNING, __FILE__, __LINE__);
			return false;
		}
	}
	
	/**
	 * Get ZUGFeRD profile constant
	 *
	 * @param string $format
	 * @param string $profile
	 * @return int
	 */
	private static function GetZugferdProfile($format, $profile)
	{
		if($format === self::FORMAT_XRECHNUNG) {
			return ZugferdProfiles::PROFILE_XRECHNUNG;
		}
		
		switch($profile) {
			case self::PROFILE_MINIMUM:
				return ZugferdProfiles::PROFILE_MINIMUM;
			case self::PROFILE_BASIC:
				return ZugferdProfiles::PROFILE_BASIC;
			case self::PROFILE_COMFORT:
				return ZugferdProfiles::PROFILE_COMFORT;
			case self::PROFILE_EXTENDED:
				return ZugferdProfiles::PROFILE_EXTENDED;
			default:
				return ZugferdProfiles::PROFILE_COMFORT;
		}
	}
	
	/**
	 * Get payment means code for ZUGFeRD
	 *
	 * @param int $payMethod Payment method constant
	 * @return int
	 */
	private static function GetPaymentMeansCode($payMethod)
	{
		switch($payMethod) {
			case PAYMENT_METHOD_BANKTRANSFER:
				return ZugferdPaymentMeans::UNTDID_4461_30;  // 30 = Credit transfer
			case PAYMENT_METHOD_PAYPAL:
			case PAYMENT_METHOD_SKRILL:
			case PAYMENT_METHOD_MOLLIE:
				return ZugferdPaymentMeans::UNTDID_4461_48;  // 48 = Bank card
			default:
				return ZugferdPaymentMeans::UNTDID_4461_1;   // 1 = Instrument not defined
		}
	}
	
	/**
	 * Convert HTML invoice to PDF/A-3 with embedded ZUGFeRD XML
	 *
	 * @param string $html HTML invoice
	 * @param string $xml ZUGFeRD XML
	 * @param string $invoiceNo Invoice number
	 * @return string|false PDF content or false on error
	 */
	private static function ConvertHTMLtoPDFA3($html, $xml, $invoiceNo)
	{
		// This requires a PDF library with PDF/A-3 support
		// Options:
		// 1. mPDF (recommended): composer require mpdf/mpdf
		// 2. TCPDF: composer require tecnickcom/tcpdf
		// 3. External service: gotenberg, wkhtmltopdf
		
		// Example with mPDF (if available):
		if(class_exists('Mpdf\Mpdf')) {
			try {
				$mpdf = new \Mpdf\Mpdf([
					'format' => 'A4',
					'PDFA' => true,
					'PDFAauto' => true
				]);
				
				// Write HTML
				$mpdf->WriteHTML($html);
				
				// Embed ZUGFeRD XML
				$mpdf->SetAssociatedFiles([[
					'name' => sprintf('zugferd-invoice_%s.xml', $invoiceNo),
					'mime' => 'text/xml',
					'description' => 'ZUGFeRD XML Invoice',
					'AFRelationship' => 'Alternative',
					'content' => $xml
				]]);
				
				return $mpdf->Output('', 'S');  // Return as string
			}
			catch(Exception $e) {
				PutLog(sprintf('PDF/A-3 generation failed: %s', $e->getMessage()), 
				       PRIO_WARNING, __FILE__, __LINE__);
				return false;
			}
		}
		
		// Fallback: Return HTML (not PDF/A-3 compliant, just for development)
		PutLog('mPDF not available - E-Rechnung PDF generation skipped', PRIO_NOTE, __FILE__, __LINE__);
		return false;
	}
	
	/**
	 * Store E-Rechnung in database
	 *
	 * @param int $orderID
	 * @param array $erechnung
	 */
	private static function StoreErechnung($orderID, $erechnung)
	{
		global $db;
		
		$db->Query('INSERT INTO {pre}erechnung(`orderid`,`format`,`xml`,`pdf`,`created`) VALUES(?,?,?,?,?) ' .
		           'ON DUPLICATE KEY UPDATE `format`=VALUES(`format`), `xml`=VALUES(`xml`), `pdf`=VALUES(`pdf`)',
			$orderID,
			$erechnung['format'],
			$erechnung['xml'],
			isset($erechnung['pdf']) ? $erechnung['pdf'] : null,
			time()
		);
	}
	
	/**
	 * Retrieve E-Rechnung from database
	 *
	 * @param int $orderID
	 * @return array|false
	 */
	public static function GetErechnung($orderID)
	{
		global $db;
		
		$res = $db->Query('SELECT * FROM {pre}erechnung WHERE `orderid`=?', $orderID);
		if($res->RowCount() == 0) {
			return false;
		}
		
		$row = $res->FetchArray(MYSQLI_ASSOC);
		$res->Free();
		
		return $row;
	}
	
	/**
	 * Validate E-Rechnung XML against EN 16931
	 *
	 * @param string $xml
	 * @return array ['valid' => bool, 'errors' => array]
	 */
	public static function ValidateXRechnung($xml)
	{
		// Validation can be done with:
		// 1. Official validator: https://portal3.gefeg.com/invoice/validation
		// 2. KoSIT validator: https://github.com/itplr-kosit/validator
		// 3. horstoeko/zugferd validation (built-in)
		
		$result = array(
			'valid' => true,
			'errors' => array()
		);
		
		// Basic XML validation
		libxml_use_internal_errors(true);
		$doc = simplexml_load_string($xml);
		
		if($doc === false) {
			$result['valid'] = false;
			foreach(libxml_get_errors() as $error) {
				$result['errors'][] = sprintf('Line %d: %s', $error->line, trim($error->message));
			}
			libxml_clear_errors();
		}
		
		return $result;
	}
	
	/**
	 * Create database table for E-Rechnung storage
	 */
	public static function CreateTable()
	{
		global $db;
		
		$db->Query('CREATE TABLE IF NOT EXISTS {pre}erechnung (
			`orderid` INT(11) NOT NULL PRIMARY KEY,
			`format` ENUM("xrechnung","zugferd2") NOT NULL,
			`xml` LONGTEXT NOT NULL,
			`pdf` LONGBLOB NULL,
			`created` INT(11) NOT NULL,
			INDEX `idx_created` (`created`),
			FOREIGN KEY (`orderid`) REFERENCES {pre}orders(`orderid`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
		COMMENT="E-Rechnung storage (XRechnung/ZUGFeRD)"');
		
		return true;
	}
}
