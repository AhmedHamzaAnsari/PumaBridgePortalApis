<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
$id = $_GET["id"];
if ($pass != '') {
    if ($pass == $access_key) {
        $sql_query1 = "SELECT od.*,geo.name,dp.name as product_name ,
        (SELECT CONCAT(geo.code, '-', geo.consignee_name) FROM puma_sap_data_trips  as tt
                    join puma_sap_data as sd on sd.id=tt.main_id
                    join geofenceing as geo on geo.code=sd.depo
                    where salesapNo=om.SaleOrder group by tt.salesapNo) as consignee_name
        FROM order_detail as od 
        join order_main as om on om.id=od.main_id
                join dealers as geo on geo.id = od.cus_id 
                join dealers_products as dp on dp.id=od.product_type
        where od.main_id = $id  order by od.id desc";

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