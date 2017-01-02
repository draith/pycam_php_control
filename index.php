<?php
putenv("TZ=Europe/London");
error_reporting(E_ALL);
// Ensure https connection...
if ($_SERVER["SERVER_PORT"] != 443) {
    $redir = "Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header($redir);
    exit();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <style>
    h2, button { font-size: 40px; margin: 5px;}
    div { font-size: 30px }
    div.hidden { display: none; position: relative; left: 50px; }
  </style>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
   <title>Pycam</title>
   <script>
   function switchOnOff()
   {
	   window.location.href = ".?command=Switch";
   }
   function toggleVis(id)
   {
	   var div = top.document.getElementById(id);
	   div.style.display = (div.style.display == 'block' ? 'none' : 'block');
   }
   function checkDelete(fname)
   {
	   if (confirm('Delete ' + fname + '?'))
	   {
		   window.location.href = ".?del=" + fname;
	   }
   }
   </script>
</head>
<body>
<?php
if (isset($_GET['del']))
{
	// Delete files matching wildcard string.
	array_map('unlink', glob($_GET['del']));
}

// Get camera status, with switching if command is set.
$key = file_get_contents('key.pub');
$pi_address = file_get_contents("lastip.txt");
$command = isset($_GET['command']) ? $_GET['command'] : "Status";
openssl_public_encrypt($command  . "/" . time(), $crypted, $key, OPENSSL_PKCS1_OAEP_PADDING);
$encoded = base64_encode($crypted);
$curl = curl_init("http://$pi_address/$encoded");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$curl_response = curl_exec($curl);
if ($curl_response === false) {
  $info = curl_getinfo($curl);
  curl_close($curl);
  echo 'error occured during curl exec. Additional info: ' . var_export($info);
}
curl_close($curl);
$running = (strcmp($curl_response, "Running") == 0);
// Display status and toggle button.
echo "<h2>Status: <span style='color:" . ($running ? "green" : "red") . "'>$curl_response</span> ";
echo "<button onclick = 'switchOnOff()'>" . ($running ? "Stop" : "Start") . "</button></h2>\n";

$image = "snapshot.jpg";
echo "<div>Snapshot uploaded " . date('D, d M, H:i:s', filemtime($image));
echo "<br><img src=\"$image\" style='max-width:50%; height:auto'>";

// get all videos to list
$videos = glob('*.mp4', GLOB_BRACE);
if (is_array($videos) && count($videos) > 0)
{
	// sort in reverse alphabetical order => newest first
	krsort($videos);
	$lastDate = '';
	foreach($videos as $vid)
	{
		$dateString = date('D d M', filemtime($vid));
		if ($dateString != $lastDate)
		{
			// Generate a heading per date
			$dayFilenameExpr = 'motion-' . date('Ymd',filemtime($vid)) . '*.mp4';
			$daysVideos = glob($dayFilenameExpr, GLOB_BRACE);
			echo "</div>";
			echo "<div><a href=\"javascript:toggleVis('$dateString');\">$dateString";
			echo " (" . count($daysVideos) . " videos)</a>";
			echo "   <a href=\"javascript:checkDelete('" . $dayFilenameExpr . "')\">[Delete]</a></div>";
			echo "<div class='hidden' id='$dateString'>";
			$lastDate = $dateString;
		}
		echo " <a href='$vid' target='_blank'>$vid</a>";
		echo " Uploaded " . date('H:i:s', filemtime($vid)); 
		echo " <a href='download.php?filename=$vid'>Download</a>";
		echo "   <a href=\"javascript:checkDelete('$vid')\">[Delete]</a></br>";
	}
}
echo "</div>";
?>
</body>
</html>