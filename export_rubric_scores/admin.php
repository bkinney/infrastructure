<?php
//I don't think I'm using this in the canvas2 version, redirecting to shared

$tokentype="domain";
$tempenabled=false;

ob_start();
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);
session_start();
$_SESSION['input']="paste";

//these are the three lines that each launching file needs - remove other lines setting the value of $include
include "/www/canvas/sitepaths.php";
$include=$canvasphp . "export_rubric_scores/common.php";
include $canvasphp . "all_purpose.php";

exit();
?>