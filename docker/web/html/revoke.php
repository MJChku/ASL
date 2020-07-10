<?php
include_once __DIR__ .'/vendor/owasp/csrf-protector-php/libs/csrf/csrfprotector.php';
//Initialise CSRFGuard library
csrfProtector::init();
?>
<?php include 'include/authenticate.php'; ?>
<?php require_once 'include/db.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$backend = 'imovies-core/revoke.php';
		
		$user = get_user($_SESSION['uid']);
		
		$fields = [
			'uid'=>$user->uid,
		];

		$ch = curl_init($backend);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code  === 200 ) {
			revoked();
			exit;
		} elseif ($http_code === 400) {
			// User already has certificate
			certAlreadyRevoked();
			exit;
		} else {
			// Server error
			serverError();
			exit;
		}
		curl_close ($ch);

} else {
	showForm();
	exit;
}
?>
<?php function showForm() {?>
<!doctype html>
<html>
	<head>
		<title>Revoke Certificate</title>
	</head>
	<body>
                <form method='post'>
			<input type='submit' value='Revoke my certificate'>
		</form>
	</body>
</html>
<?php } ?>
<?php function revoked() {?>
<!doctype html>
<html>
	<head>
		<title>Revoke Certificate</title>
	</head>
	<body>
		<p>You certificate has been successfully revoked. An up-to-date Certificate Revocation List (CRL) can be found <a href="/crl.php">here</a>. You can <a href="/gen_pkcs12.php">generate a new certificate here</a>. </p>
        <body>
</html>
<?php } ?>
<?php function serverError() {?>
<!doctype html>
<html>
	<head>
		<title>Revoke Certificate</title>
	</head>
	<body>
		<p>The CA server experienced an internal error. Please contact the system administrator. </p>
	</body>
</html>
<?php } ?>
<?php function certAlreadyRevoked() {?>
<!doctype html>
<html>
	<head>
		<title>Revoke Certificate</title>
	</head>
	<body>
		<p>You do not have a valid certificate to revoke. You can <a href="/gen_pkcs12.php">generate a new certificate here</a>. </p>
        <body>
</html>
<?php } ?>
