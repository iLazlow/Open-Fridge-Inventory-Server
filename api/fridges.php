<?php
header('content-type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include("inc/config.php");

$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer" => false,
        "verify_peer_name" => false,
    ),
	'http' => array(
		'header' => 'Connection: close\r\nHost: www.google.com\r\n',
		'timeout' => .5
	),
); 

if($apikey){
	$row = $con->query("SELECT * FROM api_keys WHERE api_key = '$apikey'")->fetch_assoc();
	
	if($row["id"]){
		if($action == "byid"){
			$row = $con->query("SELECT * FROM fridges WHERE id = '$id' ORDER by id DESC LIMIT 500")->fetch_assoc();

			$arr = array("time" => time(), "success" => true, "fridge" => $row);
			echo json_encode($arr, JSON_UNESCAPED_UNICODE);
		}else{
			$fridges = array();
			if ($result = $con->query("SELECT * FROM fridges ORDER by id DESC LIMIT 500")) {
				while ($row = $result->fetch_assoc()) {
					$fridges[] = $row;
				}
				$result->free();
			}

			$arr = array("time" => time(), "success" => true, "fridges" => $fridges);
			echo json_encode($arr, JSON_UNESCAPED_UNICODE);
		}
	}else{
		header("HTTP/1.0 403 Forbidden");
		
		$status = array(
			'status' => "error",
			'title' => "API Key invalid",
			'message' => "Your provided API Key is invalid."
		);
		$errorArray[] = $status;
		
		$arr = array("time" => time(), "success" => false, "error" => $errorArray);
		echo json_encode($arr, JSON_UNESCAPED_UNICODE);
	}
}else{
	header("HTTP/1.0 403 Forbidden");
	
	$status = array(
		'status' => "error",
		'title' => "No API Key",
		'message' => "There was no API Key provided."
	);
	$errorArray[] = $status;
	
	$arr = array("time" => time(), "success" => false, "error" => $errorArray);
	echo json_encode($arr, JSON_UNESCAPED_UNICODE);
}
?>