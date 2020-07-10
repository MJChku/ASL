<?php include 'include/authenticate.php'; ?>
<?php require_once 'include/db.php'; ?>
<?php
if (!empty($_POST)) {
	if (isset($_POST['passphrase'])) {
		$backend = 'imovies-core/gen_pkcs12.php';
		
		$user = get_user($_SESSION['uid']);
		
		$passphrase = $_POST['passphrase'];
		if (strlen($passphrase) < 4 || strlen($passphrase) > 128) {
			$_SESSION['error'] = 'Passphrase must be between 4 and 128 characters long';
			showForm();
			exit;
		}
		$fields = [
			'uid'=>$user->uid,
			'commonName'=>$user->firstname . ' ' . $user->lastname,
			'emailAddress'=>$user->email,
			'organizationName'=>'iMovies',
			'key_size'=>4096,
			'passphrase'=>$passphrase
		];

		$ch = curl_init($backend);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code  === 200) {
			// Download generated certificate
			header('Location: ' . '/get_pkcs12.php');
		} elseif ($http_code === 400) {
			// User already has certificate
			certAlreadyExists();
		} else {
			// Server error
			serverError();
		}
		curl_close ($ch);
		exit;
	}
} else {
	showForm();
	exit;
}
?>
<?php function showForm() {?>
<!doctype html>
<html>
	<head>
		<title>Generate Certificate</title>
	</head>
	<body>
		<?php 
		if (isset($_SESSION['error'])) {
			echo '<p>' . htmlspecialchars($_SESSION['error']) . '</p>';
			unset($_SESSION['error']);
		}
		?>
		<form method='post'>
			Pass Phrase:<br>
			<input type='password' name='passphrase'><br>
			<input type='submit' value='Submit'>
		</form>
	</body>
</html>
<?php 
}
exit;
?>
<?php function serverError() {?>
<!doctype html>
<html>
	<head>
		<title>Generate Certificate</title>
	</head>
	<body>
		<p>The CA server experienced an internal error. Please contact the system administrator. </p>
	</body>
</html>
<?php
}
exit;
?>
<?php function certAlreadyExists() {?>
<!doctype html>
<html>
	<head>
		<title>Generate Certificate</title>
	</head>
	<body>
		<p>You already have a valid certificate. You can <a href="/get_pkcs12.php">download your certificate</a> or <a href="/revoke.php">revoke your certificate</a></p>
	</body>
</html>
<?php
}
exit;?>
