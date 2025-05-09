<?php
include("../config.php");

$access_key = '03201232927';

$pass = $_GET["key"];
$dealer_id = isset($_GET["dealer_id"]) ? intval($_GET["dealer_id"]) : 0;

if ($pass != '') {
    if ($pass == $access_key) {
        $sql_query1 = "
            SELECT 
                dz.*,
                dp.name AS product_name,
                ds.name AS dispenser_name,
                dl.lorry_no AS tank_name,
                (
                    SELECT new_reading 
                    FROM dealer_reconcilation 
                    WHERE nozle_id = dz.id 
                    ORDER BY id DESC 
                    LIMIT 1
                ) AS new_reading
            FROM dealers_nozzel dz
            JOIN dealers_products dp ON dp.id = dz.products
            JOIN dealers_dispenser ds ON ds.id = dz.dispenser_id
            JOIN dealers_lorries dl ON dl.id = dz.tank_id
            WHERE dz.dealer_id = $dealer_id
            ORDER BY dz.id;
        ";

        $result1 = $db->query($sql_query1) or die("Error: " . mysqli_error($db));

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
