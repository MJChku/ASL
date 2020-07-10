<?php
include_once __DIR__ .'/vendor/owasp/csrf-protector-php/libs/csrf/csrfprotector.php';
//Initialise CSRFGuard library
csrfProtector::init();
?>

<?php
class certAlreadyExistsException extends Exception {
	public function errorMessage() {
		return 'User already has a valid certificate. ';
	}
}

function throw_err($error_string) {
	throw new Exception($error_string);
	return TRUE;
}

try {
	$config = include 'include/ca.php';
	require_once 'include/db.php';
	
	// Read POSTed data
	$dn = array();
	$ATTRIBUTES = ['countryName', 'stateOrProvinceName', 'localityName', 'organizationName', 'organizationalUnitName', 'commonName', 'emailAddress'];
	
	foreach($ATTRIBUTES as $key) {
		if (array_key_exists($key, $_POST)) { $dn[$key] = $_POST[$key];	} 
	}

	$keySize = $_POST['key_size'];
	$passphrase = $_POST['passphrase'];
	$uid = $_POST['uid'];

	assert (strlen($passphrase) < 128);
	
	// Check that user has no other valid certificate
	$num_certs = db_exec('SELECT COUNT(*) as cert_count FROM certificates WHERE uid = ? AND revoked IS FALSE', 's', [$uid]);
	if ($num_certs->fetch_object()->cert_count > 0) {
		throw new certAlreadyExistsException();
	}

	// Create keys
	$privKey = openssl_pkey_new([
		'private_key_bits' => $keySize,
		'private_key_type' => OPENSSL_KEYTYPE_RSA
		]) or throw_err(openssl_error_string());


	// Create CSR
	$configargs = ['config' => $config['config'], 
			'x509_extensions' => ['v3_intermediate_ca'], 
			'req_extensions' => ['usr_cert']];
	$csr = openssl_csr_new($dn, $privKey, $configargs) or throw_err(openssl_error_string());
	
	// Sign CSR
	$CAPassPhrase = 'client pass phrase';
	$CACert = 'file://' . $config['cacert'];
	$CAKey = ['file://' . $config['cakey'], $CAPassPhrase];
	$days = 365;

	$RootCACert = 'file://' . $config['rootcacert'];
	assert(strlen(file_get_contents($CACert)) > 0);
	assert(file_exists($RootCACert));
	assert(strlen(file_get_contents($RootCACert)) > 0);
	

	$max_serial = db_exec('SELECT COALESCE(MAX(serial), 0) as max_serial FROM certificates');
	if (!$max_serial) {
		throw_err('Error while getting serial number: ' . db_error());
	}

	$serial = $max_serial->fetch_object()->max_serial;

	$x509 = openssl_csr_sign($csr, $CACert, $CAKey, $days, ['config' => $config['config'], 'x509_extensions' => 'usr_cert'], $serial+1) or throw_err(openssl_error_string());

	// Get expiration date
	$validTo = substr(openssl_x509_parse($x509)['validTo'], 0, -1);

	// Create PKCS12
	if (isset($dn['commonName'])) { $friendlyName = $dn['commonName']; }
	else { $friendlyName = 'certificate'; }
	openssl_pkcs12_export($x509, $pkcs12, $privKey, $passphrase, ['friendlyname' => $friendlyName]) or throw_err(openssl_error_string());

	// Encrypt passphrase with public key
	$pubKey = openssl_pkey_get_public('file://' . '../emergency.pub.pem');
	openssl_public_encrypt($passphrase, $cryptedPassphrase, $pubKey) or throw_err(openssl_error_string());

	// Save to DB
	db_exec('INSERT INTO certificates (uid, pkcs12, serial, revoked, pass, exp_date) VALUES (?, ?, ?, ?, ?, ?)', 'sbiibs', [$uid, $pkcs12, $serial+1, FALSE, $cryptedPassphrase, $validTo]);
	
	// All OK
	http_response_code(200);
} catch (certAlreadyExistsException $e) {
	http_response_code(400);
} catch (Exception $e) {
	http_response_code(500);
} catch (Error $e) {
	http_response_code(500);
} finally {
	exit;
}
?>
