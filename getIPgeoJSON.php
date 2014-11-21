<?php

$ipArray = [];

if(($fd = fopen("../../ipget.csv", "r" )) !== FALSE)
{
	

	while(($result = fgetcsv($fd)) !== FALSE)
	{
		//print_r($result);

		if($result[3] == "success")
		{
			$ipArray[] = array(
			"ip"=>$result[0],
			"attempts"=>$result[1],
			"url"=>$result[2],
			"success" => $result[3],
			"country" => $result[4],
			"country_code" => $result[5],
			"region_code" => $result[6],
			"region_name" => $result[7],
			"city" => $result[8],
			"zip_code" => $result[9],
			"latitude" => $result[10],
			"longitude" => $result[11],
			"time_zone" => $result[12],
			"isp_name" => $result[13],
			"org_name" => $result[14],
			"as_num_name" => $result[15],
			"ip_address_used" => $result[16]);
		}
		
	}
	

	
}

$ipArray = array("ipsGeoloc" => $ipArray);
echo json_encode($ipArray);

?>