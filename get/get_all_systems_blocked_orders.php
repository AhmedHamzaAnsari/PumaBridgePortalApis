<?php
//fetch.php  
include ("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
$pre = $_GET["pre"];
$id = $_GET["user_id"];
$from = $_GET['from'];
            $to = $_GET['to'];
if ($pass != '') {
    if ($pass == $access_key) {

        if ($pre == 'ZM') {

            $sql_query1 = "SELECT om.*,dl.name ,dl.zm,dl.tm,dl.asm,dl.region,dl.city,dl.province,dl.district,us.name as usersnames,
            (SELECT CONCAT(geo.code, '-', geo.consignee_name) FROM puma_sap_data_trips  as tt
            join puma_sap_data as sd on sd.id=tt.main_id
            join geofenceing as geo on geo.code=sd.depo
            where salesapNo=om.SaleOrder group by tt.salesapNo) as consignee_name,
            (SELECT sd.is_tracker FROM puma_sap_data_trips  as tt
            join puma_sap_data as sd on sd.id=tt.main_id
            where salesapNo=om.SaleOrder group by tt.salesapNo) as is_tracker,
                        CASE
                                WHEN om.status = 0 THEN 'Pending'
                                WHEN om.status = 1 THEN 'Approved'
                                WHEN om.status = 2 THEN 'Complete'
                                WHEN om.status = 3 THEN 'Cancel'
                                WHEN om.status = 4 THEN 'Special Approval'
                                WHEN om.status = 5 THEN 'ASM Approved'
                            END AS current_status,sl.created_at as blocked_time
                        FROM order_main as om 
                        join dealers as dl on dl.id=om.created_by 
                        join users as us on us.id=dl.asm
                        join order_main_salesorder_log as sl on sl.order_id=om.id
            where om.is_send IN(1) and NOT sl.salesOrderNo REGEXP '^[0-9]+$' and dl.zm=$id and us.region!='' and province!='' and om.created_at>='$from' and om.created_at<='$to' order by om.id desc";
        } elseif ($pre == 'TM') {

            $sql_query1 = "SELECT om.*,dl.name ,dl.zm,dl.tm,dl.asm,dl.region,dl.city,dl.province,dl.district,us.name as usersnames,
            (SELECT CONCAT(geo.code, '-', geo.consignee_name) FROM puma_sap_data_trips  as tt
            join puma_sap_data as sd on sd.id=tt.main_id
            join geofenceing as geo on geo.code=sd.depo
            where salesapNo=om.SaleOrder group by tt.salesapNo) as consignee_name,
            (SELECT sd.is_tracker FROM puma_sap_data_trips  as tt
            join puma_sap_data as sd on sd.id=tt.main_id
            where salesapNo=om.SaleOrder group by tt.salesapNo) as is_tracker,
                        CASE
                                WHEN om.status = 0 THEN 'Pending'
                                WHEN om.status = 1 THEN 'Approved'
                                WHEN om.status = 2 THEN 'Complete'
                                WHEN om.status = 3 THEN 'Cancel'
                                WHEN om.status = 4 THEN 'Special Approval'
                                WHEN om.status = 5 THEN 'ASM Approved'
                            END AS current_status,sl.created_at as blocked_time
                        FROM order_main as om 
                        join dealers as dl on dl.id=om.created_by 
                        join users as us on us.id=dl.asm
                        join order_main_salesorder_log as sl on sl.order_id=om.id
            where om.is_send IN(1) and NOT sl.salesOrderNo REGEXP '^[0-9]+$' and dl.tm=$id and us.region!='' and province!='' and om.created_at>='$from' and om.created_at<='$to' order by om.id desc";
        } elseif ($pre == 'ASM') {
            $sql_query1 = "SELECT om.*,dl.name ,dl.zm,dl.tm,dl.asm,dl.region,dl.city,dl.province,dl.district,us.name as usersnames,
            (SELECT CONCAT(geo.code, '-', geo.consignee_name) FROM puma_sap_data_trips  as tt
            join puma_sap_data as sd on sd.id=tt.main_id
            join geofenceing as geo on geo.code=sd.depo
            where salesapNo=om.SaleOrder group by tt.salesapNo) as consignee_name,
            (SELECT sd.is_tracker FROM puma_sap_data_trips  as tt
            join puma_sap_data as sd on sd.id=tt.main_id
            where salesapNo=om.SaleOrder group by tt.salesapNo) as is_tracker,
                        CASE
                                WHEN om.status = 0 THEN 'Pending'
                                WHEN om.status = 1 THEN 'Approved'
                                WHEN om.status = 2 THEN 'Complete'
                                WHEN om.status = 3 THEN 'Cancel'
                                WHEN om.status = 4 THEN 'Special Approval'
                                WHEN om.status = 5 THEN 'ASM Approved'
                            END AS current_status,sl.created_at as blocked_time
                        FROM order_main as om 
                        join dealers as dl on dl.id=om.created_by 
                        join users as us on us.id=dl.asm
                        join order_main_salesorder_log as sl on sl.order_id=om.id
            where om.is_send IN(1) and NOT sl.salesOrderNo REGEXP '^[0-9]+$' and dl.asm=$id and us.region!='' and province!='' and om.created_at>='$from' and om.created_at<='$to' order by om.id desc";

        } else {
            
            $sql_query1 = "SELECT om.*,dl.name ,dl.zm,dl.tm,dl.asm,dl.region,dl.city,dl.province,dl.district,us.name as usersnames,
            (SELECT CONCAT(geo.code, '-', geo.consignee_name) FROM puma_sap_data_trips  as tt
            join puma_sap_data as sd on sd.id=tt.main_id
            join geofenceing as geo on geo.code=sd.depo
            where salesapNo=om.SaleOrder group by tt.salesapNo) as consignee_name,
            (SELECT sd.is_tracker FROM puma_sap_data_trips  as tt
            join puma_sap_data as sd on sd.id=tt.main_id
            where salesapNo=om.SaleOrder group by tt.salesapNo) as is_tracker,
                        CASE
                                WHEN om.status = 0 THEN 'Pending'
                                WHEN om.status = 1 THEN 'Approved'
                                WHEN om.status = 2 THEN 'Complete'
                                WHEN om.status = 3 THEN 'Cancel'
                                WHEN om.status = 4 THEN 'Special Approval'
                                WHEN om.status = 5 THEN 'ASM Approved'
                            END AS current_status,sl.created_at as blocked_time
                        FROM order_main as om 
                        join dealers as dl on dl.id=om.created_by 
                        join users as us on us.id=dl.asm
                        join order_main_salesorder_log as sl on sl.order_id=om.id
            where om.is_send IN(1) and us.region!='' and province!='' and NOT sl.salesOrderNo REGEXP '^[0-9]+$' and om.created_at>='$from' and om.created_at<='$to' order by om.id desc;";
        }


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