<?php
header("Content-Type: application/json;charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

try {
	
	function errorhandler($errno, $errstr, $errfile, $errline) {
		throw new Exception($errstr);
	}
	
	set_error_handler('errorhandler', E_ALL);
	$postdata = file_get_contents('php://input');

	mail('trev@tprinn.co.uk', 'Jazz Leeds Book Request', $postdata, 'From: noreply@jazzleedsbooks.org.uk');
	
	$result['success'] = true;
	echo json_encode($result);
} catch (Exception $e) {
	$result['success'] = false;
	$result['error'] = $e;
	echo json_encode($result);
}

?>