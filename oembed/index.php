<?php
header("content-type: application/json");
$url = $_GET['url'];
$arr = explode("html=",$url,2);

$html = urldecode($arr[1]);
$html = str_replace('href="tabs-','href="#tabs-',$html);//weird hack for some reason the hash tags area breaking this

$str = preg_replace('/(?<=>)\s+|\s+(?=<)/', '',$html);
 $str = trim($str);


?>
{"version":"1.0","type":"rich","width":240,"height":400,"html":"<p style='display:none'>-------COMMENT--start widget content do not delete. Users will not see this------</p><?php echo addslashes($str) ?><p style='display:none'>-------end widget content do not delete------</p>"}