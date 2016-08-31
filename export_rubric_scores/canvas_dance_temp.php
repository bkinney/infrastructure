<?php
//include this instead of blti if you need api access!
session_start();
//echo "test version, where am I";
// if we want to issue a token and not store it - set in the primary page

$token = $_SESSION['temptoken'];
//custom var I added
$domain = $_REQUEST['custom_domain_url'];
//
if(empty($domain))$domain=$_REQUEST['custom_canvas_api_domain'];
//echo 'empty=' . $domain;
if(empty($domain)){
	//echo "from cookie";
	$domain = $_COOKIE['domain'];
}else{//lost the POST vars when we redirected
	setcookie('domain',$domain,0,'/');
}
//echo "cookie=" . $_COOKIE['domain'];
//$testing = $domain=="udel.test.instructure.com";
if(empty($_COOKIE['context_id']))setcookie('context_id',$_REQUEST['context_id'],0,'/');

require_once '../ims-blti/blti.php';//this is the beta version
include '../canvasapi.php';//this is the beta version
include "/home/bkinney/includes/lti_db.php";

//'/home/bkinney/includes/get_ud_canvas_endpoint_paginate.php';
//change this to look up in db

//changelog - use same secret for all
$secret =array("table"=>"blti_keys","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");
$context = new BLTI($secret, true, false);//try redirect

	if($context->valid){
			//set some session variables
	//echo "valid";
		
		//die();
	
		$context_id = $context->info['context_id'];
	//echo $context_id;
		//these are used to update db by get_token_domain
				  $isAdmin = $context->isAdministrator();
			  
				setcookie("context",$context_id,0,'/');
				setcookie("isAdmin",$isAdmin,0,'/');
				setcookie("lti_url","https://apps.ats.udel.edu" .$_SERVER['PHP_SELF'],0,'/');
	//changelog - get token from token table
	$shared = array_key_exists('custom_shared',$context->info);
	if($temptoken && !$_SESSION['temptoken']){//go get a token
	//die("trying to score a temp token");
	setcookie("lti_url","https://apps.ats.udel.edu" .$_SERVER['PHP_SELF'],0,'/');//once token is acquired, get_temp_token.php will redirect
	
	$state=rand(100,999);//security stuff
		  setcookie('state',$state,0,'/');
				header("Location: https://" .$domain."/login/oauth2/auth?client_id=10000000000369&redirect_uri=https://apps.ats.udel.edu/canvas/get_temp_token.php&response_type=code&purpose=Single_Use_Rubric_Export&state=" . $state);
}
	//this is a Haywood J token. He is an instructor in Becky Test
	//if($asinstructor)$_SESSION['token']='25~18Dk6lWaa44bnPTWe1xbRydhRJ9zKOc3g3mfbqazxPpgw7MOJ4qGCSRGjJSKQewq';
	if($_SESSION['temptoken']){//use whatever I've already got from previous trips
		
		$api = new CanvasAPI($_SESSION['temptoken'],$domain);
		$valid = $api->ready;
		
		$tokenstatus="found in session" . $api->status . "," . $api->is_valid_token();
	
		
	}

}else{
	echo "invalid context";
		
		//print_r($_REQUEST);
}//end if context

?>