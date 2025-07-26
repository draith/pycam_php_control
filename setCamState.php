<?php
header("Content-Type: text/plain");
if ($_GET["id"] === "mypicam") // TODO: hide/encrypt?
{
    $file = fopen("camState.txt", "w");
    fwrite($file, $_GET["state"]);    
    echo "OK";
}
else
{
    echo "NOK";
}
?>
