<?php
/*
 * b1gMail - Payment API Test Tool
 * (c) 2025 b1gMail.eu
 *
 * Tests Payment Provider API Connections
 */

include('../serverlib/admin.inc.php');
RequestPrivileges(PRIVILEGES_ADMIN);
AdminRequirePrivilege('prefs.payments');

header('Content-Type: application/json');

$provider = $_GET['provider'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

$result = array(
	'success' => false,
	'error' => 'Unknown provider'
);

//
// PayPal REST API v2 Test
//
if($provider === 'paypal')
{
	$clientId = $input['client_id'] ?? '';
	$clientSecret = $input['client_secret'] ?? '';
	$apiUrl = $input['api_url'] ?? 'https://api.paypal.com';
	
	if(empty($clientId) || empty($clientSecret))
	{
		$result['error'] = 'Client ID und Client Secret erforderlich';
	}
	else
	{
		// Get OAuth Token
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiUrl . '/v1/oauth2/token');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Accept-Language: en_US',
			'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret)
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode === 200)
		{
			$data = json_decode($response, true);
			$result['success'] = true;
			$result['token_type'] = $data['token_type'] ?? 'Bearer';
			$result['expires_in'] = $data['expires_in'] ?? 0;
			$result['message'] = 'PayPal OAuth2 Token erfolgreich erhalten';
		}
		else
		{
			$error = json_decode($response, true);
			$result['error'] = sprintf('HTTP %d: %s', $httpCode, $error['error_description'] ?? $error['error'] ?? 'Unknown error');
		}
	}
}

//
// Mollie API Test
//
else if($provider === 'mollie')
{
	$apiKey = $input['api_key'] ?? '';
	
	if(empty($apiKey))
	{
		$result['error'] = 'Mollie API Key erforderlich';
	}
	else
	{
		// Test API Connection + Get Payment Methods
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.mollie.com/v2/methods');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $apiKey,
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode === 200)
		{
			$data = json_decode($response, true);
			$result['success'] = true;
			$result['mode'] = strpos($apiKey, 'test_') === 0 ? 'TEST' : 'LIVE';
			
			// Extract payment method names
			$methods = array();
			if(isset($data['_embedded']['methods']))
			{
				foreach($data['_embedded']['methods'] as $method)
				{
					$methods[] = $method['description'] ?? $method['id'];
				}
			}
			$result['methods'] = $methods;
			$result['message'] = 'Mollie API erfolgreich verbunden';
		}
		else if($httpCode === 401)
		{
			$result['error'] = 'Ungültiger API Key (401 Unauthorized)';
		}
		else
		{
			$error = json_decode($response, true);
			$result['error'] = sprintf('HTTP %d: %s', $httpCode, $error['detail'] ?? $error['title'] ?? 'Unknown error');
		}
	}
}

//
// Skrill Test (Limited - no public test API)
//
else if($provider === 'skrill')
{
	$email = $input['email'] ?? '';
	$secret = $input['secret'] ?? '';
	
	if(empty($email) || empty($secret))
	{
		$result['error'] = 'Skrill E-Mail und Secret erforderlich';
	}
	else
	{
		// Skrill has no public test API
		// We can only validate the format
		if(filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$result['success'] = true;
			$result['message'] = 'Skrill-Konfiguration überprüft (E-Mail valide)';
			$result['note'] = 'Skrill hat keine öffentliche Test-API. Führen Sie eine echte Test-Zahlung durch.';
		}
		else
		{
			$result['error'] = 'Ungültige E-Mail-Adresse';
		}
	}
}

echo json_encode($result);
?>
