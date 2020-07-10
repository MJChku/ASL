<?php
function console_log($s) {
  ?><script>console.log(<?php echo $s; ?>);</script><?php
}


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
    $uid = $_POST['uid'];
    $file_string=null;
    $results = db_exec('SELECT expiredate, serial, revoked FROM certificates WHERE uid=".'".$uid."'');
    $row = $results->fetch_array();
    if($row['revoked']){
      throw new certAlreadyRevokedException();
    }
    $date = gmDate("ymdHis\Z");
    $file_string="R"."\t".$row['expiredate']."\t".$date."\t".$row['serial']."\t\\t\\n";
    }
    $index_handle = fopen("./index.txt","a+");
    fwrite($index_handle,$file_string);
    fclose($index_handle);
    $cmd="openssl ca -gencrl -passin pass:\"client pass phrase\" -config \"./openssl.conf -out ./crl/crl.pem \"";
    exec($cmd,$output_array, $retval);
    if($retval){
      throw_err();
    }
    do_exec("UPDATE certifificates SET revokedate=".$date." revoked=TRUE WHRER uid=".$uid); 
    
  }catch (certAlreadyExistsException $e) {
    http_response_code(400);
  } catch (Error $e) {
    http_response_code(500);
    // echo $e;
  } finally {
    exit;
  }
  ?>
  
  
