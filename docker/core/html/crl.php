<?php
header('Content-Type: application/pkix-crl');
echo file_get_contents('./crl.pem');
exit;
?>
