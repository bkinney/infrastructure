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
session_start();
include "/www/canvas/sitepaths.php";

if(array_key_exists('custom_input',$_REQUEST)){
	$_SESSION['input']=$_REQUEST['custom_input'];
}else if(empty($_SESSION['input'])){
	$_SESSION['input']="paste";
}
//$filename = $_SESSION['input']=="paste" ? "common.php" : "pulldown.php";

	$include=  $canvasphp . "export_rubric_scores/common.php" ;

include $canvasphp . "all_purpose.php";

exit();
?>