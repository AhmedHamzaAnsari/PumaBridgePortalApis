<?php
//fetch.php  
include("../../config.php");

$access_key = '12345';
$pass = $_GET["accesskey"];

if ($pass != '') {
    $id = $_GET["id"]; // user id
    $from = $_GET['from'];
    $to = $_GET['to'];

    if ($pass == $access_key) {
        // If user is admin
        if ($id == '1') {
            // vehicles assigned to users (excluding admin) + unassigned vehicles
            $sql_query1 = "SELECT DISTINCT dc.name, dc.tracker as vehicle_make, dc.time, dc.speed, dc.location as vlocation, dc.lat as latitude, dc.lng as longitude, us.name as username
                FROM users_devices_new as ud
                JOIN devicesnew as dc ON dc.id = ud.devices_id
                JOIN users as us ON us.id = ud.users_id
                WHERE ud.users_id != '1' AND us.privilege != 'tracker'

                UNION

                SELECT DISTINCT dc.name, dc.tracker as vehicle_make, dc.time, dc.speed, dc.location as vlocation, dc.lat as latitude, dc.lng as longitude, us.name as username
                FROM users_devices_new AS ud
                JOIN devicesnew as dc ON dc.id = ud.devices_id
                JOIN users as us ON us.id = ud.users_id
                WHERE ud.users_id = 1
                AND ud.devices_id NOT IN (
                    SELECT DISTINCT ud2.devices_id 
                    FROM users_devices_new AS ud2
                    JOIN users AS us ON us.id = ud2.users_id
                    WHERE us.privilege NOT IN ('tracker', 'admin')
                    AND ud2.users_id != 1
                )
            ";
        } else {
            // For normal user: only their assigned vehicles
            $sql_query1 = "SELECT DISTINCT dc.name, dc.tracker as vehicle_make, dc.time, dc.speed, dc.location as vlocation, dc.lat as latitude, dc.lng as longitude, us.name as username
                FROM users_devices_new as ud
                JOIN devicesnew as dc ON dc.id = ud.devices_id
                JOIN users as us ON us.id = ud.users_id
                WHERE ud.users_id = '$id' 
            ";
        }

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