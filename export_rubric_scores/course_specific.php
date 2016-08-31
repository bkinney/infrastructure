<?php
if(array_key_exists('tokentype',$_GET)){
	$tokentype=$_GET['tokentype'];
}else{
	$tokentype="temp";
}
$tempenabled=true;
error_reporting(E_ALL & ~E_NOTICE);
$testing=false;

ob_start();//need this so I can clean the buffer before printing the csv. not sure where the <html>stuff is being introduced. strange.
include "/www/canvas2/sitepaths.php";

$include=$canvasphp . "export_rubric_scores/pulldown.php";

include $canvasphp . "all_purpose.php";

exit();
?>