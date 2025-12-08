<?php
/*
 * SSL Manager Test Script
 */

define('SKIP_SESSION_CHECK', true);
require '../serverlib/init.inc.php';

echo "<pre>\n";
echo "=== SSL MANAGER TEST ===\n\n";

try {
	echo "Loading SSL Manager...\n";
	require_once(B1GMAIL_DIR . 'serverlib/ssl-manager.inc.php');
	echo "✓ SSL Manager loaded\n\n";
	
	echo "Creating SSL Manager instance...\n";
	$manager = new SSLManager();
	echo "✓ SSL Manager instance created\n\n";
	
	echo "Testing discoverDomainsFromProtocols()...\n";
	$domains = $manager->discoverDomainsFromProtocols();
	echo "✓ Found " . count($domains) . " domain(s)\n";
	foreach($domains as $domain => $data) {
		echo "  - $domain: " . count($data['subdomains']) . " subdomain(s)\n";
	}
	echo "\n";
	
	echo "Testing getAllCertificates()...\n";
	$certs = $manager->getAllCertificates();
	echo "✓ Found " . count($certs) . " certificate(s)\n\n";
	
	echo "Testing suggestWildcard()...\n";
	if(count($domains) > 0) {
		$firstDomain = array_key_first($domains);
		$suggestion = $manager->suggestWildcard($firstDomain);
		echo "✓ Suggestion for $firstDomain:\n";
		echo "  Suggest: " . ($suggestion['suggest'] ? 'YES' : 'NO') . "\n";
		echo "  Reason: " . $suggestion['reason'] . "\n";
	}
	echo "\n";
	
	echo "=== ALL TESTS PASSED ===\n";
} catch(Exception $e) {
	echo "✗ ERROR: " . $e->getMessage() . "\n";
	echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
	echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
