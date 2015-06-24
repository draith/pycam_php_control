<?php
/*
Save the file in a folder that's not accessible through Apache, then use a
PHP script for file downloading:

<a href="download.php?file=whatsup.doc"> ... </a>

download.php:
*/
$fname = $_GET['filename'];
$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
$mimeType = finfo_file($finfo, $fname);
header("Content-type: $mimeType");
header("Content-Disposition: attachment; filename=\"$fname\"");
session_write_close();
readfile($fname);
?>