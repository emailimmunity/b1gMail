<?php
/**
 * ACME v2 Client for Let's Encrypt
 * RFC 8555: https://tools.ietf.org/html/rfc8555
 * (c) 2025 b1gMail Development
 */

class AcmeClient
{
	private $db;
	private $directoryUrl;
	private $directory;
	private $accountKey;
	private $accountUrl;
	private $nonce;
	
	// Let's Encrypt URLs
	const LE_PRODUCTION = 'https://acme-v02.api.letsencrypt.org/directory';
	const LE_STAGING = 'https://acme-staging-v02.api.letsencrypt.org/directory';
	
	public function __construct($useProduction = false, $accountId = null)
	{
		global $db;
		$this->db = $db;
		
		$this->directoryUrl = $useProduction ? self::LE_PRODUCTION : self::LE_STAGING;
		$this->directory = $this->getDirectory();
		
		if($accountId)
		{
			$this->loadAccount($accountId);
		}
	}
	
	/**
	 * Hole ACME Directory (Endpoints)
	 */
	private function getDirectory()
	{
		$response = $this->httpRequest('GET', $this->directoryUrl);
		return json_decode($response['body'], true);
	}
	
	/**
	 * Lade existierenden Account
	 */
	private function loadAccount($accountId)
	{
		$res = $this->db->Query('SELECT * FROM {pre}ssl_acme_accounts WHERE id=?', (int)$accountId);
		if($account = $res->FetchArray(MYSQLI_ASSOC))
		{
			$this->accountKey = $account['account_key'];
			$this->accountUrl = $account['account_url'];
			$res->Free();
			return true;
		}
		$res->Free();
		return false;
	}
	
	/**
	 * Erstelle neuen ACME Account
	 */
	public function createAccount($email)
	{
		// Generate account key (RSA 4096)
		$keyResource = openssl_pkey_new(array(
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
			'private_key_bits' => 4096
		));
		
		openssl_pkey_export($keyResource, $accountKey);
		$this->accountKey = $accountKey;
		
		// Register account
		$payload = array(
			'termsOfServiceAgreed' => true,
			'contact' => array('mailto:' . $email)
		);
		
		$response = $this->signedRequest($this->directory['newAccount'], $payload);
		
		if($response['code'] === 201 || $response['code'] === 200)
		{
			$this->accountUrl = $response['headers']['location'];
			
			// Save to database
			$this->db->Query('INSERT INTO {pre}ssl_acme_accounts SET
				email=?,
				directory_url=?,
				account_key=?,
				account_url=?,
				status=?',
				$email,
				$this->directoryUrl,
				$accountKey,
				$this->accountUrl,
				'active'
			);
			
			return array(
				'success' => true,
				'account_id' => $this->db->InsertId(),
				'account_url' => $this->accountUrl
			);
		}
		
		return array(
			'success' => false,
			'message' => 'Account creation failed: ' . $response['body']
		);
	}
	
	/**
	 * Bestelle neues Zertifikat
	 */
	public function orderCertificate($domains)
	{
		// 1. Create order
		$identifiers = array();
		foreach($domains as $domain)
		{
			$identifiers[] = array(
				'type' => 'dns',
				'value' => $domain
			);
		}
		
		$payload = array('identifiers' => $identifiers);
		$response = $this->signedRequest($this->directory['newOrder'], $payload);
		
		if($response['code'] !== 201)
		{
			return array(
				'success' => false,
				'message' => 'Order creation failed: ' . $response['body']
			);
		}
		
		$order = json_decode($response['body'], true);
		$orderUrl = $response['headers']['location'];
		
		return array(
			'success' => true,
			'order' => $order,
			'order_url' => $orderUrl,
			'authorizations' => $order['authorizations'],
			'finalize' => $order['finalize']
		);
	}
	
	/**
	 * Hole Challenge-Daten für Domain
	 */
	public function getChallenge($authorizationUrl, $challengeType = 'http-01')
	{
		$response = $this->signedRequest($authorizationUrl, '');
		$authorization = json_decode($response['body'], true);
		
		// Find desired challenge
		foreach($authorization['challenges'] as $challenge)
		{
			if($challenge['type'] === $challengeType)
			{
				return array(
					'success' => true,
					'domain' => $authorization['identifier']['value'],
					'token' => $challenge['token'],
					'url' => $challenge['url'],
					'validation' => $this->getKeyAuthorization($challenge['token'])
				);
			}
		}
		
		return array(
			'success' => false,
			'message' => 'Challenge type not available'
		);
	}
	
	/**
	 * Validiere Challenge
	 */
	public function validateChallenge($challengeUrl)
	{
		$payload = new stdClass(); // Empty object
		$response = $this->signedRequest($challengeUrl, $payload);
		
		return array(
			'success' => $response['code'] === 200,
			'response' => json_decode($response['body'], true)
		);
	}
	
	/**
	 * Finalisiere Order mit CSR
	 */
	public function finalizeOrder($finalizeUrl, $csr)
	{
		$payload = array(
			'csr' => $this->base64url(base64_decode($csr))
		);
		
		$response = $this->signedRequest($finalizeUrl, $payload);
		
		if($response['code'] !== 200)
		{
			return array(
				'success' => false,
				'message' => 'Finalization failed'
			);
		}
		
		$order = json_decode($response['body'], true);
		
		// Wait for certificate
		$maxAttempts = 10;
		$attempt = 0;
		
		while($order['status'] === 'processing' && $attempt < $maxAttempts)
		{
			sleep(2);
			$response = $this->signedRequest($response['headers']['location'], '');
			$order = json_decode($response['body'], true);
			$attempt++;
		}
		
		if($order['status'] === 'valid' && isset($order['certificate']))
		{
			// Download certificate
			$certResponse = $this->signedRequest($order['certificate'], '');
			
			return array(
				'success' => true,
				'certificate' => $certResponse['body']
			);
		}
		
		return array(
			'success' => false,
			'message' => 'Certificate not ready'
		);
	}
	
	/**
	 * Generiere CSR (Certificate Signing Request)
	 */
	public function generateCSR($domains)
	{
		// Generate private key
		$keyResource = openssl_pkey_new(array(
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
			'private_key_bits' => 4096
		));
		
		openssl_pkey_export($keyResource, $privateKey);
		
		// Generate CSR
		$dn = array(
			'CN' => $domains[0], // Common Name
			'O' => 'b1gMail',
			'C' => 'DE'
		);
		
		$csrResource = openssl_csr_new($dn, $keyResource, array(
			'digest_alg' => 'sha256'
		));
		
		openssl_csr_export($csrResource, $csrPem);
		
		// Extract CSR DER
		preg_match('~-----BEGIN CERTIFICATE REQUEST-----(.+)-----END CERTIFICATE REQUEST-----~s', $csrPem, $matches);
		$csrDer = base64_encode(base64_decode($matches[1]));
		
		return array(
			'csr' => $csrDer,
			'private_key' => $privateKey
		);
	}
	
	/**
	 * HTTP-01 Challenge: Erstelle Validation File
	 */
	public function setupHttp01Validation($token, $validation)
	{
		$wellKnownPath = B1GMAIL_DIR . '.well-known/acme-challenge';
		
		if(!file_exists($wellKnownPath))
		{
			mkdir($wellKnownPath, 0755, true);
		}
		
		$filePath = $wellKnownPath . '/' . $token;
		file_put_contents($filePath, $validation);
		
		return $filePath;
	}
	
	/**
	 * Get Key Authorization (für HTTP-01)
	 */
	private function getKeyAuthorization($token)
	{
		$details = openssl_pkey_get_details(openssl_pkey_get_private($this->accountKey));
		$jwk = array(
			'e' => $this->base64url($details['rsa']['e']),
			'kty' => 'RSA',
			'n' => $this->base64url($details['rsa']['n'])
		);
		
		$thumbprint = $this->base64url(hash('sha256', json_encode($jwk), true));
		
		return $token . '.' . $thumbprint;
	}
	
	/**
	 * Signed Request (JWS)
	 */
	private function signedRequest($url, $payload)
	{
		$details = openssl_pkey_get_details(openssl_pkey_get_private($this->accountKey));
		
		$protected = array(
			'alg' => 'RS256',
			'nonce' => $this->getNonce(),
			'url' => $url
		);
		
		if($this->accountUrl)
		{
			$protected['kid'] = $this->accountUrl;
		}
		else
		{
			$protected['jwk'] = array(
				'e' => $this->base64url($details['rsa']['e']),
				'kty' => 'RSA',
				'n' => $this->base64url($details['rsa']['n'])
			);
		}
		
		$protected64 = $this->base64url(json_encode($protected));
		$payload64 = $this->base64url(is_string($payload) ? $payload : json_encode($payload));
		
		openssl_sign($protected64 . '.' . $payload64, $signature, $this->accountKey, OPENSSL_ALGO_SHA256);
		
		$data = array(
			'protected' => $protected64,
			'payload' => $payload64,
			'signature' => $this->base64url($signature)
		);
		
		return $this->httpRequest('POST', $url, json_encode($data));
	}
	
	/**
	 * Get new nonce
	 */
	private function getNonce()
	{
		if($this->nonce) return $this->nonce;
		
		$response = $this->httpRequest('HEAD', $this->directory['newNonce']);
		$this->nonce = $response['headers']['replay-nonce'];
		
		return $this->nonce;
	}
	
	/**
	 * HTTP Request
	 */
	private function httpRequest($method, $url, $data = null)
	{
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		
		if($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/jose+json',
				'Content-Length: ' . strlen($data)
			));
		}
		elseif($method === 'HEAD')
		{
			curl_setopt($ch, CURLOPT_NOBODY, true);
		}
		
		$response = curl_exec($ch);
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		$headerText = substr($response, 0, $headerSize);
		$body = substr($response, $headerSize);
		
		curl_close($ch);
		
		// Parse headers
		$headers = array();
		foreach(explode("\r\n", $headerText) as $line)
		{
			if(strpos($line, ':') !== false)
			{
				list($key, $value) = explode(':', $line, 2);
				$headers[strtolower(trim($key))] = trim($value);
			}
		}
		
		// Update nonce
		if(isset($headers['replay-nonce']))
		{
			$this->nonce = $headers['replay-nonce'];
		}
		
		return array(
			'code' => $code,
			'headers' => $headers,
			'body' => $body
		);
	}
	
	/**
	 * Base64 URL encoding
	 */
	private function base64url($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
}
