<?php include 'include/authenticate.php'; ?>
<?php require_once 'include/db.php'; ?>
<?php
$backend = 'imovies-core/get_pkcs12.php';

$user = get_user($_SESSION['uid']);

$fields = ['uid' => $user->uid];

$ch = curl_init($backend);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($http_code  === 200 && $response) {
	// Download stored certificate
	$filename = $user->firstname . '_' . $user->lastname . '.p12';
	downloadPKCS12($response, $filename);
} elseif ($http_code === 400) {
	// User does not have valid certificate
	noCert();	
} else {
	var_dump($http_code);
	var_dump($response);
	// Server error
	serverError();
}

curl_close ($ch);
exit;
?>
<?php function downloadPKCS12($pkcs12, $filename) {
	header('Cache-Control: cache, must-revalidate');
	header('Pragma: public');
	header('Content-Type: ' . 'application/x-pkcs12');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	echo $pkcs12;
} ?>
<?php function serverError() {?>
<!doctype html>
<html>
	<head>
		<title>Download Certificate</title>
	</head>
	<body>
		<p>The CA server experienced an internal error. Please contact the system administrator. </p>
	</body>
</html>
<?php } ?>
<?php function noCert() {?>
<!doctype html>
<html>
	<head>
		<title>Download Certificate</title>
	</head>
	<body>
		<p>You do not have a valid certificate. You can <a href="/gen_pkcs12.php">generate a certificate here</a>. </p>
	</body>
</html>
<?php } ?>
