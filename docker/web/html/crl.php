<?php
$backend = 'imovies-core/crl.php';

$ch = curl_init($backend);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code  === 200 && $response) {
	header('Content-Type: ' . 'application/pkix-crl');
	header('Content-Disposition: attachment; filename="crl.pem"');
	echo $response;
} else {
	// Server error
	serverError();
}
curl_close ($ch);
exit;
?>
<?php function serverError() {?>
<!doctype html>
<html>
	<head>
		<title>Download CRL</title>
	</head>
	<body>
		<p>The CA server experienced an internal error. Please contact the system administrator. </p>
	</body>
</html>
<?php } ?>
