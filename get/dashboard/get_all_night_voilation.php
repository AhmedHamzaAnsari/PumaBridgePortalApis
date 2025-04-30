<?php
//fetch.php  
include ("../../config.php");


$access_key = '12345';

$pass = $_GET["accesskey"];
if ($pass != '') {
    $id = $_GET["id"];
    $from = $_GET['from'];
    $to = $_GET['to'];
    if ($pass == $access_key) {
        $todate = date("Y-m-d H:i:s", time());
        $prev_date = date("Y-m-d H:i:s", strtotime($todate . ' -1 day'));
        // $sql_query1 = "SELECT da.*,dc.name FROM driving_alerts as da join devicesnew as dc on dc.id=da.device_id 
        // where da.type='Un-Authorized Stop' and da.created_at>='$from' and da.created_at<='$to' and da.created_by='$id';";



        $sql_query1 = "SELECT 
    da.in_time,
    da.out_time,
    da.time,
    dc.name,
    da.location AS vlocation,
    da.start_co AS latitude,
    da.end_co AS longitude,
    da.speed,
    dc.tracker AS vehicle_make,
    us.name AS username
FROM 
    driving_alerts_new AS da
JOIN 
    devicesnew AS dc ON dc.id = da.device_id
JOIN 
    users_devices_new AS ud ON ud.devices_id = dc.id
JOIN 
    users AS us ON us.id = ud.users_id
WHERE 
    da.type = 'Night time violations' 
    AND da.created_at >= '$from' 
    AND da.created_at <= '$to' 
    AND da.created_by = '$id';";


        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

        $thread = array();
        while ($user = $result1->fetch_assoc()) {
            $thread[] = $user;
        }
        echo json_encode($thread);

    } else {
        echo 'Wrong Key...';
    }

} else {
    echo 'Key is Required';
}


?>