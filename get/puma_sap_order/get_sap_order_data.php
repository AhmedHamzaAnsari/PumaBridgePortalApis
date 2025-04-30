<?php
//fetch.php  
include("../../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
// $id=$_GET["id"];
if ($pass != '') {
    if ($pass == $access_key) {
        $from = $_GET["from"];
        $to = $_GET["to"];


        $sql_query1 = "SELECT pd.*,dc.name as vehiclenames,geo.consignee_name as depot,dc.id as uniqueId,'' as end_time,dc.name as vehiclename ,dc.lat,dc.lng,
        IF(dc.name IS NOT NULL, 'With-Tracker', 'Without-Tracker') AS tracker_status FROM puma_sap_data as pd
 left join devicesnew as dc on dc.name=pd.vehicle
 left join geofenceing as geo on geo.id=pd.depo
 join puma_sap_data_trips as spd on spd.main_id=pd.id 
 left join order_main as om on om.SaleOrder=spd.salesapNo where  pd.created_at>='$from' and pd.created_at<='$to' group by om.SaleOrder order by pd.id desc ";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error());

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