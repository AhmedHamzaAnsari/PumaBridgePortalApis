<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://mytrakker.tpltrakker.com/TrakkerServices/Api/Home/GetVLL/00903315646/3156',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bmlxdWVfbmFtZSI6IjhaWVhYOFoiLCJuYmYiOjE3MzIxNzQyNTAsImV4cCI6MTczMjE3NDMxMCwiaWF0IjoxNzMyMTc0MjUwfQ.9aY3X5ULsRgw-StCyAKHHhkm1oD59p0ISx7tKVEVcDw'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;