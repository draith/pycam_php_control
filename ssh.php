<?php
$ipFile = fopen("lastip.txt", "r");
$addr = fgets($ipFile);
header("Location: ssh://$addr");
?>