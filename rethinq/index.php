<?php
session_start();
include "/www/canvas/sitepaths.php";
$include=$canvasphp ."rethinq/common.php";
$tokentype="domain";
//$tokentype="context";	 
	 $secret = array("table"=>"blti_keys","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");
	 $testing=false;
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 1);

//-----------all this will move to general include -----------------

include $canvasphp . "all_purpose.php";

exit();
?>