<?php

$contact = $_GET['contact'];
$msg = $_GET['msg'];

$curl = curl_init();

curl_setopt_array(
    $curl,
    array(
        CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03022026441&Password=Jazz%40123&From=PUMA&To=' . $contact . '&Message=' . $msg . '',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    )
);

$response = curl_exec($curl);

curl_close($curl);
echo $response;

?>