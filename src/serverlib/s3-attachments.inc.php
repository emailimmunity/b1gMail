<?php
/**
 * b1gMailServer S3 Attachment Storage
 * (c) 2025 b1gMail Development
 *
 * Speichert Email-Anhänge direkt in S3/Minio statt im lokalen Dateisystem
 */

class S3AttachmentStorage
{
	private $prefs;
	private $s3Client;
	
	public function __construct($prefs)
	{
		$this->prefs = $prefs;
	}
	
	/**
	 * Check if S3 storage is enabled and configured
	 */
	public function isEnabled()
	{
		return !empty($this->prefs['sftpgo_s3_enabled']) &&
		       !empty($this->prefs['sftpgo_s3_endpoint']) &&
		       !empty($this->prefs['sftpgo_s3_bucket']);
	}
	
	/**
	 * Upload attachment to S3
	 */
	public function uploadAttachment($mailID, $attachmentData, $fileName, $mimeType)
	{
		if(!$this->isEnabled())
		{
			return array('success' => false, 'message' => 'S3 not configured');
		}
		
		$endpoint = $this->prefs['sftpgo_s3_endpoint'];
		$bucket = $this->prefs['sftpgo_s3_bucket'];
		$accessKey = $this->prefs['sftpgo_s3_access_key'];
		$secretKey = $this->prefs['sftpgo_s3_secret_key'];
		
		// Generate S3 object key
		$objectKey = 'attachments/' . date('Y/m/d') . '/' . $mailID . '/' . $fileName;
		
		// Prepare request
		$url = $endpoint . '/' . $bucket . '/' . $objectKey;
		$contentMD5 = base64_encode(md5($attachmentData, true));
		$contentLength = strlen($attachmentData);
		$date = gmdate('D, d M Y H:i:s T');
		
		// Create signature
		$stringToSign = "PUT\n$contentMD5\n$mimeType\n$date\n/$bucket/$objectKey";
		$signature = base64_encode(hash_hmac('sha1', $stringToSign, $secretKey, true));
		
		// Upload via CURL
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $attachmentData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Date: ' . $date,
			'Content-Type: ' . $mimeType,
			'Content-MD5: ' . $contentMD5,
			'Content-Length: ' . $contentLength,
			'Authorization: AWS ' . $accessKey . ':' . $signature
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode === 200 || $httpCode === 204)
		{
			return array(
				'success' => true,
				'object_key' => $objectKey,
				'url' => $url,
				'size' => $contentLength
			);
		}
		else
		{
			return array(
				'success' => false,
				'message' => "Upload failed (HTTP $httpCode)",
				'response' => $response
			);
		}
	}
	
	/**
	 * Download attachment from S3
	 */
	public function downloadAttachment($objectKey)
	{
		if(!$this->isEnabled())
		{
			return false;
		}
		
		$endpoint = $this->prefs['sftpgo_s3_endpoint'];
		$bucket = $this->prefs['sftpgo_s3_bucket'];
		$accessKey = $this->prefs['sftpgo_s3_access_key'];
		$secretKey = $this->prefs['sftpgo_s3_secret_key'];
		
		$url = $endpoint . '/' . $bucket . '/' . $objectKey;
		$date = gmdate('D, d M Y H:i:s T');
		
		// Create signature
		$stringToSign = "GET\n\n\n$date\n/$bucket/$objectKey";
		$signature = base64_encode(hash_hmac('sha1', $stringToSign, $secretKey, true));
		
		// Download via CURL
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Date: ' . $date,
			'Authorization: AWS ' . $accessKey . ':' . $signature
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$data = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		if($httpCode === 200)
		{
			return $data;
		}
		
		return false;
	}
	
	/**
	 * Delete attachment from S3
	 */
	public function deleteAttachment($objectKey)
	{
		if(!$this->isEnabled())
		{
			return false;
		}
		
		$endpoint = $this->prefs['sftpgo_s3_endpoint'];
		$bucket = $this->prefs['sftpgo_s3_bucket'];
		$accessKey = $this->prefs['sftpgo_s3_access_key'];
		$secretKey = $this->prefs['sftpgo_s3_secret_key'];
		
		$url = $endpoint . '/' . $bucket . '/' . $objectKey;
		$date = gmdate('D, d M Y H:i:s T');
		
		// Create signature
		$stringToSign = "DELETE\n\n\n$date\n/$bucket/$objectKey";
		$signature = base64_encode(hash_hmac('sha1', $stringToSign, $secretKey, true));
		
		// Delete via CURL
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Date: ' . $date,
			'Authorization: AWS ' . $accessKey . ':' . $signature
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		return ($httpCode === 204 || $httpCode === 200);
	}
	
	/**
	 * Get public URL for attachment (with presigned URL for private buckets)
	 */
	public function getAttachmentURL($objectKey, $expiresIn = 3600)
	{
		if(!$this->isEnabled())
		{
			return false;
		}
		
		$endpoint = $this->prefs['sftpgo_s3_endpoint'];
		$bucket = $this->prefs['sftpgo_s3_bucket'];
		$accessKey = $this->prefs['sftpgo_s3_access_key'];
		$secretKey = $this->prefs['sftpgo_s3_secret_key'];
		
		$expires = time() + $expiresIn;
		
		// Create presigned URL
		$stringToSign = "GET\n\n\n$expires\n/$bucket/$objectKey";
		$signature = urlencode(base64_encode(hash_hmac('sha1', $stringToSign, $secretKey, true)));
		
		$url = $endpoint . '/' . $bucket . '/' . $objectKey;
		$url .= '?AWSAccessKeyId=' . $accessKey;
		$url .= '&Expires=' . $expires;
		$url .= '&Signature=' . $signature;
		
		return $url;
	}
}
