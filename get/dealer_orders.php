<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
$id=$_GET["id"];
if ($pass != '') {
    if ($pass == $access_key) {
        $sql_query1 = "SELECT om.*,geo.consignee_name,dl.name ,pd.status as sap_status,
        CASE
        WHEN pd.status = 0 THEN 'Pending'
        WHEN pd.status = 1 THEN 'Start'
        WHEN pd.status = 2 THEN 'Complete'
        END AS current_status
        FROM order_main as om 
        left join geofenceing as geo on geo.id=om.depot
        join dealers as dl on dl.id=om.created_by
        left join puma_sap_data_trips as pd on pd.salesapNo=om.SaleOrder where om.created_by='$id' and om.total_amount>0 and om.created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01') group by om.SaleOrder order by om.id desc";

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