<?php

$curl = curl_init();

// Set SSL verification options
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://103.111.160.120:44300/sap/opu/odata/sap/ZP2P_TRACK_PROJ_SRV/InitialSet4(CustomerId=\'203000044\',OrderType=\'EX\',LineItem=\'000010\',Material=\'2\',Quantity=10)',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Basic dG1jX2FzaW06dG1jQDEyMw==',
    'Cookie: SAP_SESSIONID_DEV_200=BuAjI9hdAhLBCEmDfya0cjQONsHMwxHut-IAUFa6fv0%3d; sap-usercontext=sap-client=200'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

header('Content-Type: application/xml');

// Echo the XML response
echo $response;


?>
