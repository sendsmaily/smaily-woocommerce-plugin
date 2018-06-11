<?php
require_once '../../../wp-load.php';
	$smailey_front =  Smailey_Front::instance();
	$response = $smailey_front->cronSubscribeAll(getList());
	logToFile('smailey_cron',$response['message']);
function logToFile($filename, $msg)
{ 
    $fd = fopen($filename, "a");
    $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
    fwrite($fd, $str . "\n");
    fclose($fd);
} 
?>