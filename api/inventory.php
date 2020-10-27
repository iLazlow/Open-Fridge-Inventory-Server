<?php
header('content-type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include("inc/config.php");

if($apikey){
	$row = $con->query("SELECT * FROM api_keys WHERE api_key = '$apikey'")->fetch_assoc();
	
	if($row["id"]){
		if($action == "list"){
			$inventory = array();
			if ($result = $con->query("SELECT * FROM inventory WHERE fridge='$id' ORDER by id DESC LIMIT 5000")) {
				while ($row = $result->fetch_assoc()) {
					$openFoodFactsApi = json_decode(file_get_contents("https://world.openfoodfacts.org/api/v0/product/".$row["ean"].".json" ,false, stream_context_create($arrContextOptions)), true);
					
					$row["name"] = $openFoodFactsApi["product"]["product_name"];
					$row["image_thumb_url"] = $openFoodFactsApi["product"]["image_thumb_url"];
					$row["rec_value"] = 0;
					//$row["openfooddata"] = $openFoodFactsApi["product"];
					$inventory[] = $row;
				}
				$result->free();
			}
			$arr = array("time" => time(), "success" => true, "inventory" => $inventory);
			echo json_encode($arr, JSON_UNESCAPED_UNICODE);
		}elseif($action == "put"){
			if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
				$putdata = json_decode(file_get_contents("php://input"), true);
				$ean = protect($putdata["ean"]);
				
				if($ean != ""){
					$row = $con->query("SELECT * FROM inventory WHERE ean = '$ean' AND fridge='$id'")->fetch_assoc();
					
					if($row["id"]){
						$con->query("UPDATE inventory SET value = '".($row["value"]+1)."', timestamp_update = '".time()."' WHERE ean = '$ean' AND fridge = '$id'");
					}else{
						$con->query("INSERT INTO inventory (ean, value, timestamp, timestamp_update, fridge) VALUES ('$ean', '1', '".time()."', '".time()."', '$id')");
					}
					
					$arr = array("time" => time(), "success" => true, "message" => "Your Item was saved.");
					echo json_encode($arr, JSON_UNESCAPED_UNICODE);
				}else{
					header("HTTP/1.0 412 Precondition Failed");
		
					$status = array(
						'status' => "error",
						'title' => "Precondition Failed",
						'message' => "Your request data was empty."
					);
					$errorArray[] = $status;
					
					$arr = array("time" => time(), "success" => false, "error" => $errorArray);
					echo json_encode($arr, JSON_UNESCAPED_UNICODE);
				}
			}else{
				header("HTTP/1.0 400 Bad Request");
		
				$status = array(
					'status' => "error",
					'title' => "Bad Request",
					'message' => "Your request didn't matched with the expected request method."
				);
				$errorArray[] = $status;
				
				$arr = array("time" => time(), "success" => false, "error" => $errorArray);
				echo json_encode($arr, JSON_UNESCAPED_UNICODE);
			}
		}elseif($action == "delete"){
			if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
				$putdata = json_decode(file_get_contents("php://input"), true);
				$ean = protect($putdata["ean"]);
				
				if($ean != ""){
					$row = $con->query("SELECT * FROM inventory WHERE ean = '$ean' AND fridge='$id'")->fetch_assoc();
					
					if($row["id"] AND $row["value"] > 1){
						$con->query("UPDATE inventory SET value = '".($row["value"]-1)."', timestamp_update = '".time()."' WHERE ean = '$ean' AND fridge = '$id'");
					}else{
						$con->query("DELETE FROM inventory WHERE ean = '$ean' AND fridge = '$id'");
					}
					
					$arr = array("time" => time(), "success" => true, "message" => "Your Item was removed.");
					echo json_encode($arr, JSON_UNESCAPED_UNICODE);
				}else{
					header("HTTP/1.0 412 Precondition Failed");
		
					$status = array(
						'status' => "error",
						'title' => "Precondition Failed",
						'message' => "Your request data was empty."
					);
					$errorArray[] = $status;
					
					$arr = array("time" => time(), "success" => false, "error" => $errorArray);
					echo json_encode($arr, JSON_UNESCAPED_UNICODE);
				}
			}else{
				header("HTTP/1.0 400 Bad Request");
		
				$status = array(
					'status' => "error",
					'title' => "Bad Request",
					'message' => "Your request didn't matched with the expected request method."
				);
				$errorArray[] = $status;
				
				$arr = array("time" => time(), "success" => false, "error" => $errorArray);
				echo json_encode($arr, JSON_UNESCAPED_UNICODE);
			}
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