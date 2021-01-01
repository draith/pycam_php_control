<?php
date_default_timezone_set("Europe/London");
error_reporting(E_ALL);
// Ensure https connection...
if ($_SERVER["SERVER_PORT"] != 443) {
    $redir = "Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header($redir);
    exit();
}
session_save_path(dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . "/sessions");
session_start();

// Get camera status, with switching if command is set.
$cam_status = file_get_contents("camState.txt");
$running = !strcmp($cam_status, "Running");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <style>
    h2, button { font-size: 30px; margin: 5px;}
    div { font-size: 20px }
    div.hidden { display: none; position: relative; left: 50px; }
  </style>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta name="viewport" content="initial-scale=1">
   <title>Pycam</title>
   <script>
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

   function switchCam(command)
   {
     var button = document.getElementById('camSwitch');
     
     button.disabled = true;
     button.style.cursor = 'wait';

     <?php
      $command = ($running ? "Stop" : "Start");

      // Generate encrypted Start and Stop commands using timestamp at page load.
      $key = file_get_contents('key.pub');
      $commandPrefix = time() . '/';

      openssl_public_encrypt($commandPrefix . $command, $crypted, $key, OPENSSL_PKCS1_OAEP_PADDING);
      $encoded = base64_encode($crypted);
      $piAddress = 'http://' . file_get_contents('lastip.txt') . ':8998';

      // Send Start/Stop command to pi
      echo "window.location = \"$piAddress/$encoded\";\n";
    ?>
   }
   </script>
</head>
<body>
<?php
if (isset($_POST['btnLogin']))
{
  if (md5($_POST['txtPassword']) == 'f81150487534a6523e42bf0baacaeb2c')
  {
    $_SESSION['logged in'] = True;
  }
}
if (!isset($_SESSION['logged in']))
{
  echo '<form action="" method="post" name="frmLogin">';
  echo '<input name="txtPassword" value="" type="password">';
  echo '<input name="btnLogin" type="submit" value="Submit"></form>';
}
else
{
  if (isset($_GET['del']))
  {
    // Delete files matching wildcard string.
    array_map('unlink', glob($_GET['del']));
  }

  // Display status and toggle button.
  $lastSwitch = filemtime("camState.txt");

  if (!strcmp(date("z", $lastSwitch), date("z")))
  {
    $switchString = date("H:i", $lastSwitch);
  }
  else
  {
    $switchString = date("l, jS F, H:i", $lastSwitch);
  }
  
  // Display status and toggle button.
  echo "<h2>Status: <span style='color:" . ($running ? "green" : "red") . "'>$cam_status</span> since $switchString ";
  echo "<button id=\"camSwitch\" onclick = 'switchCam(\"$command\")'>$command</button></h2>\n";

  $image = "./snapshot.jpg";
  echo "<div>Snapshot uploaded " . date('D, d M, H:i:s', filemtime($image));
  echo "<br><img src=\"$image\" style='max-width:75%; height:auto'>";

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
}
?>
</body>
</html>