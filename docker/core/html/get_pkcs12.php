<?php
include_once __DIR__ .'/vendor/owasp/csrf-protector-php/libs/csrf/csrfprotector.php';
//Initialise CSRFGuard library
csrfProtector::init();
?>

<?php
try {
	require_once 'include/db.php';
	
	// Read POSTed data
	$uid = $_POST['uid'];

	// Check that user has valid certificate
	if ($pkcs12 = get_pkcs12($uid)) {
		// Send PKCS12
		http_response_code(200);
		header('Content-Type: ' . 'application/x-pkcs12');
		echo $pkcs12->pkcs12;
	} else {
		http_response_code(400);
	}
} catch (Error $e) {
	http_response_code(500);
} finally {
	exit;
}
?>
