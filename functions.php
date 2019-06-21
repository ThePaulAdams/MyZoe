<?php

function getToken($femail,$fpassword) {
	global $token, $VIN, $authorization, $name;

	#Let's start by getting the token etc
	$data_string = '{"username":"'.$femail.'","password":"'$fpassword.'"}';


	$ch = curl_init('https://www.services.renault-ze.com/api/user/login');

	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);    // you currently have http


	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: '.strlen($data_string))
	);

	$result = curl_exec($ch);
	curl_close($ch);
	$data = json_decode($result, true);
	$token = $data['token'];
	$authorization = "Authorization: Bearer " .$data['token']; // Prepare the authorisation token
	$VIN = $data['user']['vehicle_details']['VIN'];
	$name = $data['user']['first_name'];

}

function checkAccount($femail,$fpassword) {
	global $token, $VIN, $authorization, $name;

	#Let's start by getting the token etc
	$data_string = '{"username":"'.$femail.'","password":"'.$fpassword.'"}';

	$ch = curl_init('https://www.services.renault-ze.com/api/user/login');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	# Return response instead of printing.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);    // you currently have http
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: '.strlen($data_string))
	);

	$result = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if($httpCode == 503) {
		$VIN = "503";
	}else {
		curl_close($ch);
		$data = json_decode($result, true);
		if(isset($data['user']['vehicle_details']['VIN'])){
			$VIN = $data['user']['vehicle_details']['VIN'];
			return $VIN;
		}
		else {
			return false;
		}
	}
	return $VIN;
}


#now we have a token and VIN we can start making requests.
function getBattery(){
	global $token, $VIN, $authorization, $mkm, $kmtom;
	global $translate_Yes,$translate_No,$translate_Unknown, $translate_Battery_Information, $lang,$translate_Battery_Information,$translate_Charge_Type,$translate_Complete,$translate_Charging,$translate_Last_Update,$translate_Plugged_In,$translate_Power_Level,$translate_Remaining_Charge_Time,$translate_Remaining_Range;


	$url = "https://www.services.renault-ze.com/api/vehicle/$VIN/battery";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization)); // Inject the token into the header
	$result = curl_exec($ch);
	$battery = json_decode($result, true);



	print(''.$battery['last_update'].'
		   '.$battery['plugged'].'
		   '.$battery['charge_level'].'
		   '.ceil(($battery['remaining_range'] * $kmtom)).'
		   '.$battery['charging'].'
		   '. date('H:i', mktime(0,$battery['remaining_time'])).'
		   '.$battery['charging_point'].'
		   '.(((int)$batKWH/100) * (int)$battery['charge_level']).' / '.(int)$batKWH.' kWh'	);
}

function preCon(){
	global $token, $VIN, $authorization;
	$url = "https://www.services.renault-ze.com/api/vehicle/$VIN/air-conditioning";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization)); // Inject the token into the header
	$result = curl_exec($ch);
	error_log("precon: " . $VIN . " " . $result);
}

function checkPrecon(){

    global $token, $VIN, $authorization, $translate_Last_Start_Time, $lang;
	$url = "https://www.services.renault-ze.com/api/vehicle/$VIN/air-conditioning/last";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization)); // Inject the token into the header
	$result = json_decode(curl_exec($ch),true);

	if(!isset($timezone)) $timezone = null;
	$convertLastUpdate = changeTimeZone("d-M-Y H:i",  date("d-M-Y H:i", substr($result["date"],0,-3)) , "Europe/London", $timezone);

	return $convertLastUpdate;

}

function startCharge(){

	global $token, $VIN, $authorization;
	$url = "https://www.services.renault-ze.com/api/vehicle/$VIN/charge";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization));
	$result = curl_exec($ch);

}

?>
