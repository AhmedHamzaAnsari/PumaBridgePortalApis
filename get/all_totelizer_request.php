<?php
include("../config.php");

$access_key = '03201232927';
$pass = $_GET["key"] ?? null;
$id = $_GET["user_id"] ?? null;

if ($pass != '') {
    if ($pass == $access_key) {
        $data = [];

        // Optional filter for TM
        $where = "";
        if (!empty($id)) {
            $where = "WHERE d.tm = '$id'";
        }
        $sql_query = "
        SELECT 
            tcr.dealer_id,
            tcr.id AS totelizer_id,
            d.name AS dealer_name,
            dd.name AS dispenser_name,
            dl.lorry_no AS tank_name,
            dn.name AS nozzel_name,
            tcr.status,
            u.name AS tm_name,
            tcr.created_at,
            tcr.last_reading as lr
        FROM totelizer_change_request AS tcr
        LEFT JOIN dealers_nozzel AS dn ON dn.id = tcr.nozzel_id  -- Join dealers_nozzel table using nozzel_id
        LEFT JOIN dealers AS d ON d.id = tcr.dealer_id  -- Join dealers table using dealer_id
        LEFT JOIN dealers_lorries AS dl ON dl.id = dn.tank_id  -- Join dealers_lorries table using tank_id
        LEFT JOIN dealers_dispenser AS dd ON dd.id = dn.dispenser_id  -- Join dealers_dispenser table using dispenser_id
        LEFT JOIN users AS u ON u.id = tcr.created_by  -- Join users table to get TM name
        WHERE tcr.status != 1  -- Exclude rows where status is 1 (approved)
        AND dn.is_change != 1  -- Exclude rows where is_change is 1 (change applied)
        ORDER BY tcr.id DESC  -- Order by totelizer_change_request id in descending order
    ";
    
       $result = $db->query($sql_query);

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode($data);
    } else {
        echo 'Wrong Key...';
    }
} else {
    echo 'Key is Required';
}