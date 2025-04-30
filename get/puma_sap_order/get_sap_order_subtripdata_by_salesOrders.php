<?php
//fetch.php  
include("../../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
// $id=$_GET["id"];
if ($pass != '') {
    if ($pass == $access_key) {
        $salesOrders = $_GET["salesOrders"];


        $sql_query1 = "SELECT dt.*,dl.name,ps.vehicle,ps.driver_name,ps.driver_contact,ps.driver_cnic,ap.name as product_name,dc.id as vehicle_id,dt.created_at as start_time,dl.`co-ordinates` as dealer_co,
        CASE
                           WHEN dt.status = 0 THEN 'Pending'
                           WHEN dt.status = 1 THEN 'Start'
                           WHEN dt.status = 2 THEN 'Complete'
                           END AS current_status,IF(dc.name IS NOT NULL, 'With-Tracker', 'Without-Tracker') AS tracker_status,geo.consignee_name,geo.Coordinates as depo_co
       FROM puma_sap_data_trips as dt 
       left join dealers as dl on dl.sap_no=CAST(TRIM(LEADING '0' FROM dt.dealer_sap ) AS UNSIGNED)
       join all_products as ap on CAST(TRIM(LEADING '0' FROM ap.sap_no ) AS UNSIGNED)=dt.material
       join puma_sap_data as ps on ps.id=dt.main_id
       left join devicesnew as dc on dc.name=ps.vehicle
       join geofenceing as geo on geo.code=ps.depo where dt.salesapNo='$salesOrders'";

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