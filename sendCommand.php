<?php
$key = file_get_contents('key.pub');
$pi_address = file_get_contents("lastip.txt");
$command = $_GET['command'] . "/" . time();
openssl_public_encrypt($command, $crypted, $key, OPENSSL_PKCS1_OAEP_PADDING);
$encoded = base64_encode($crypted);
header("Location: http://$pi_address:8998/$encoded");
?>