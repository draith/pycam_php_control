<?php
$ipFile = fopen("lastip.txt", "r");
$addr = fgets($ipFile);
//header("Location: ssh://$addr");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<body>
<?php
echo "<p style='font-size: 60px'>$addr <a href='ssh://$addr'>SSH Link</a>";
echo " <a href='http://$addr:8787'>Temp Logger</a></p>\n";
?>
</body>
</html>