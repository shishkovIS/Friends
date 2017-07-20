<?php
	function log_message($message) {
	
	date_default_timezone_set('Europe/Moscow');	
	$date = date('m/d/Y h:i:s a', time());
	$message = "[".$date."] ".$message."\t\n";
    
	
	error_log($message, 3, 'log.txt');   
}
?>