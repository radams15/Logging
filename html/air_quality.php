<?php

function http_get($url){
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

	// $output contains the output string
	$output = curl_exec($ch);

	// close curl resource to free up system resources
	curl_close($ch); 
	
	return $output;
}

$data = http_get("https://weather.com/en-GB/forecast/allergy/l/ee4f8d6de99562d6c2e4c093ecde0c9c3b834521790c376cae79b337a7a8f488");

preg_match_all("/(\"(?:(grass|tree|ragweed)Pollen)|(?:breathing))Index\":\[((?:\d,?)*)\]/", $data, $first);

$info = [];

for($x=count($first[0])-1 ; $x>0 ; $x--){
	preg_match_all("/\"?(.*)Index\":\[((?:\d,?)*)\]/", $first[0][$x], $out);
	$type = $out[1][0];
	$vals = explode(",", $out[2][0]);
	if($vals and $type){
		$vals = array_slice($vals, 0, 7)
		$info[$type] = intVal($vals[0]);
	}
}

preg_match_all("/\"(.*)Index\":\[((?:\d,?)*)\]/", $first[0], $info);


$json = json_encode($info);

header('Content-Type: application/json');
echo $json;
