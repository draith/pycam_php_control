<?php
putenv("TZ=Europe/London");
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
   <title>Pycam</title>
   <style>
   iframe {
	   width: 220px;
	   height: 60px;
	   position: relative;
	   top: 10px;
	   overflow: hidden; 
	}
	h2,button { font-size: 40px; margin: 5px; }
	div { font-size: 30px }
	div.hidden { display: none; position: relative; left: 50px; }
   </style>
   <script>
   function switchOnOff()
   {
	   top.document.getElementById('iframe').setAttribute('src','sendCommand.php?command=Switch');
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
echo "<h2>Status: <iframe height='35' width='80' src='sendCommand.php?command=Status' id='iframe'></iframe>";
echo " <button onclick = 'switchOnOff()'>Start/Stop</button></h2>\n";
$image = "snapshot.jpg";
echo "<div>Snapshot uploaded " . date('D, d M, H:i:s', filemtime($image));
echo "<br><img src=\"$image\" style='max-width:50%; height:auto'>" ; 

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