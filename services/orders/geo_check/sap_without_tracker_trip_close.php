<?php
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 20; URL=$url1");
// error_reporting(0);

include ("../../../config.php");
set_time_limit(5000);

echo "<h1>Sap With-out Tracker Trip Close service .</h1><br>";

// $sql = "SELECT ss.* 
// FROM puma_sap_data_trips AS ss
// JOIN puma_sap_data AS sp ON sp.id = ss.main_id
// WHERE sp.is_tracker = 0 and ss.status=0
// AND MONTH(ss.created_at) = MONTH(CURRENT_DATE()) 
// AND YEAR(ss.created_at) = YEAR(CURRENT_DATE());";

$sql = "SELECT dd.*,tt.eta,tt.id as sub_id FROM puma_sap_data as dd
join puma_sap_data_trips as tt on tt.main_id=dd.id
where dd.is_tracker=0 AND dd.created_at >= NOW() - INTERVAL 10 DAY and tt.status!=2 order by dd.id desc;";

$result = mysqli_query($db, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($db));
}

$count = mysqli_num_rows($result);

if ($count > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $main_id = $row['id'];
        $sub_order_id = $row['sub_id'];
        $eta = $row['eta'];

        $sql_update = "UPDATE puma_sap_data_trips 
        SET close_time = '$eta', 
            status = 2 
        WHERE id = $sub_order_id";
        if (mysqli_query($db, $sql_update)) {
            echo "Trip Closed successfully !";
        } else {
            echo "Error: " . $sql_update . " " . mysqli_error($db);
        }
    }
} else {
    echo '<h1>No Records Found to send Msg</h1>';
}



mysqli_close($db);
echo "Last Run " . date('Y-m-d H:i:s');
?>