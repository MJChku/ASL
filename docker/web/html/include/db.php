<?php

global $DB_ERROR;

function db_exec($query, $types = '', $values = []) {
	global $DB_ERROR;
	if (strlen($types) != count($values)) {
		return NULL;
	}

	$config = json_decode(file_get_contents('../config/mysql.json'));
	$con = new mysqli(NULL, $config->db_user, $config->db_pass, 'imovies', NULL, $config->db_socket);
	if ($con->connect_errno) {
		$DB_ERROR = 'Connection to database failed: '.$con->connect_errno.' '.$con->connect_error;
		echo $DB_ERROR;
		return NULL;
	}
	if ($stmt = $con->prepare($query)) {
		if (count($values) > 0) {
			$stmt->bind_param($types, ...$values);
			$prev_i = 0;
			while ($i = strpos($types, 'b', $prev_i)) {
				$stmt->send_long_data($i, $values[$i]);
				$prev_i = $i + 1;
			}
		}
		
		$stmt->execute();
		$result = $stmt->get_result();
		return $result;
	} else {
		$DB_ERROR = 'Error during statement preparation: ' . $con->errno . ' ' . $con->error;
		echo $DB_ERROR;
		return NULL;
	}
}

function db_error() {
	global $DB_ERROR;
	return $DB_ERROR;
}


function get_user($uid) {
	return db_exec('SELECT * FROM users WHERE uid = ?', 's', [$uid])->fetch_object();
}

function get_certificate($uid) {
	return db_exec('SELECT pkcs12 FROM certificates WHERE uid = ? AND revoked IS FALSE ORDER BY serial DESCENDING LIMIT 1', 's', [$uid])->fetch_object();
}
?>
