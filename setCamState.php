<?php
header("Content-Type: text/plain");
if ($_GET["id"] === "mypicam") // TODO: hide/encrypt?
{
    file_put_contents("camState.txt", $_GET["state"]);    
    echo "OK";
}
else
{
    echo "NOK";
}
?>
