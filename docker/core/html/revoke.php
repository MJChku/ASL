<?php
class certAlreadyRevokedException extends Exception {
	public function errorMessage() {
		return 'User already revoked the certificate. ';
	}
}

function throw_err($error_string) {
	throw new Exception($error_string);
	return TRUE;
}

try { 
	$config = include 'include/ca.php';
	require_once 'include/db.php';

	$uid = $_POST['uid'];

	$obj = db_exec('SELECT serial FROM certificates WHERE uid = ? AND revoked IS FALSE', 's', [$uid])->fetch_object();
	if (!$obj || !$obj->serial) {
		throw new CertAlreadyRevokedException();
	}
	$serial = $obj->serial;

	// Revocation date is current date
	$curr_date = new DateTime();
	$new_rev_date = $curr_date->format('ymdHis');

	// Build index
	$rows = db_exec('SELECT serial, revoked, exp_date, rev_date FROM certificates;'); 
	$index = '';
	while ($row = $rows->fetch_object()) {
		$exp_date = DateTime::createFromFormat('ymdHis', $row->exp_date);

		if ($row->revoked || $row->serial === $serial) { $res = 'R'; }
		elseif ($exp_date < $curr_date) { $res = 'E'; }
		else { $res = 'V'; }

		$res = $res . "\t" . $row->exp_date . 'Z';
		$res = $res . "\t";
		if ($row->rev_date) { $res = $res . $row->rev_date . 'Z'; }
		if ($row->serial === $serial) { $res = $res . $new_rev_date . 'Z'; }

		$serial_str = strval(dechex($row->serial));
		// OpenSSL requires an odd number of characters in the serial column
		if(strlen($serial_str) % 2){
			$serial_str = '0' . $serial_str;
		} 

		$res = $res . "\t" . $serial_str; 
		$res = $res . "\t" . 'unknown';
		$res = $res . "\t" . 'OMITTED';

		$index = $index . $res . "\n"; 
	}
	
	// Write index to index file
	file_put_contents($config['index'], $index);

	// Load client CA passphrase
	$pass_file = fopen($config['pass'], 'r');
	$passphrase = fread($pass_file, filesize($config['pass']));
	fclose($pass_file);
	$passphrase = preg_replace('/\n$/','',$passphrase);	 

	$cmd = 'openssl ca -gencrl -passin pass:"' . $passphrase . '" -config ' . $config['config'] . ' -out "./crl.pem" 2>&1';
	
	exec($cmd, $output_array, $retval);

	if($retval){
		throw_err('Error while generating CRL');
	}
	
	// Once we're sure the CRL has been generated without errors, revoke in DB
	db_exec('UPDATE certificates SET revoked = TRUE, rev_date = ? WHERE serial = ?' ,'si',[$new_rev_date, $serial]); 
		
	http_response_code(200);
} catch (certAlreadyRevokedException $e) {
	http_response_code(400);
} catch (Exception $e) {
	http_response_code(500);
} catch (Error $e) {
	http_response_code(500);
} finally {
	exit;
}
?>
