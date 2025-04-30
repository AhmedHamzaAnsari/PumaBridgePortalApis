<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    $id = $_GET["id"];
    if ($pass == $access_key) {
        $sql_query1 = "SELECT ud.*,us.name as user_name,dc.name device_name FROM users_devices_new as ud
        join users as us on us.id=ud.users_id
        join devicesnew as dc on dc.id=ud.devices_id where us.privilege='Cartraige';";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

        $thread = array();
        while ($user = $result1->fetch_assoc()) {
            $thread[] = $user;
        }
        echo json_encode($thread);

    } else {
        echo 'Wrong Key...';
    }

} 
else 
{
    echo 'Key is Required';
}


?>