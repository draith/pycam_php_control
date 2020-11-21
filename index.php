<?php
date_default_timezone_set("Europe/London");
error_reporting(E_ALL);
// Ensure https connection...
// if ($_SERVER["SERVER_PORT"] != 443) {
//     $redir = "Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
//     header($redir);
//     exit();
// }
session_save_path(dirname($_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF']) . "/sessions");
session_start();
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
  echo "<h2>Status: <span id=\"camStatus\"></span> ";
  echo "<button id=\"startStop\"></button></h2>\n";

  $image = "snapshot.jpg";
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

  ?>
    <script> 
      var statusSpan = document.getElementById("camStatus");
      statusSpan.textContent = "Connecting..."

      var camButton = document.getElementById("startStop");
      camButton.textContent = "Start";
      camButton.disabled = true;

      // Open a socket to the pi.
      <?php
      echo "var ws = new WebSocket('ws://" . file_get_contents('lastip.txt') . ":8998');\n"
      ?>

      // On Start/Stop button click, send Start/Stop command to pi.
      camButton.onclick = function() {
        var commandObj = { command: camButton.textContent };
        ws.send(JSON.stringify(commandObj));
      }

      // On status message from pi, update status.
      ws.onmessage = function (evt) { 
        var data = JSON.parse(evt.data);
        var status = data.status;
        // alert(evt.data);
        // alert(data.status);
        statusSpan.textContent = status;
        statusSpan.style.color = (status == "Running" ? "green" : "red");
        camButton.textContent = (status == "Running" ? "Stop" : "Start");
        camButton.disabled = false;
      };

    </script>
  <?php
}
?>
</body>
</html>