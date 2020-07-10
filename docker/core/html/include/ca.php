<?php
include_once __DIR__ .'/vendor/owasp/csrf-protector-php/libs/csrf/csrfprotector.php';
//Initialise CSRFGuard library
csrfProtector::init();
?>

<?php
$config = [];
$config['certstore_path']='/var/www/ca/';

$config['rootca_path'] = $config['certstore_path'].'certs/';
$config['rootcacert'] = $config['rootca_path'] . 'ca.cert.pem';

$config['ca_path'] = $config['certstore_path'].'client/';
$config['key_path']=$config['ca_path'].'private/';
$config['cert_path']=$config['ca_path'].'certs/';
$config['crl_path']=$config['ca_path'].'crl/';
$config['newcert_path']=$config['ca_path'].'newcerts/';
$config['config'] = $config['ca_path'].'openssl.cnf';
$config['cacert'] = $config['cert_path'] . 'client.cert.pem';
$config['cakey'] = $config['key_path'] . 'client.key.pem';
$config['cacrl'] = $config['crl_path'] . 'crl.pem';
$config['index'] = $config['ca_path'] . 'index.txt';
$config['pass'] = $config['ca_path'] . 'pass.txt';

return $config;
?>
