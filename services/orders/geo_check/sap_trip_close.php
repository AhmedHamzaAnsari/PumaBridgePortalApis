<?php
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 20; URL=$url1");
// error_reporting(0);

include("../../../config.php");
set_time_limit(5000);

echo "<h1>Sap Trip Close Check IN service .</h1><br>";

$sql = "SELECT dt.*, ps.vehicle, dl.`co-ordinates` as co ,dl.name
FROM puma_sap_data_trips as dt 
join dealers as dl on dl.sap_no=CAST(TRIM(LEADING '0' FROM dt.dealer_sap ) AS UNSIGNED)
join puma_sap_data as ps on ps.id=dt.main_id 
JOIN order_main as om ON om.SaleOrder=dt.salesapNo 
WHERE om.delivered_status=1 AND dt.status=1 and ps.is_tracker=1
 GROUP BY om.SaleOrder";

$result = mysqli_query($db, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($db));
}

$count = mysqli_num_rows($result);

if ($count > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $co = $row['co'];
        $sub_order_id = $row['id'];
        $v_num = $row['vehicle'];
        $id = $row['id'];

         $get_vehicle_data_query = "SELECT * FROM devicesnew WHERE name = '$v_num'";
        $vehicle_data_result = mysqli_query($db, $get_vehicle_data_query);

        if (!$vehicle_data_result) {
            die("Query failed: " . mysqli_error($db));
        }

        if ($vehicle_row = mysqli_fetch_array($vehicle_data_result)) {
            $v_lat = $vehicle_row['lat'];
            $v_lng = $vehicle_row['lng'];
            $v_id = $vehicle_row['id'];

            // echo '<br/>';
            // echo 'Car name = ' . $v_num . ' | ID = ' . $v_id . ' | TRIP-ID = ' . $id;
            // echo '<br/>';
            // echo '---------------------------------------------------';
            // echo '<br/>';

            get_geo($v_lat, $v_lng, $v_num, $v_id, $co, $db, $sub_order_id);
        }
    }
} else {
    echo '<h1>No Records Found to send Msg</h1>';
}

function get_geo($v_lat, $v_lng, $v_num, $v_id, $co, $db, $sub_order_id)
{
    $mychars = explode(', ', $co);
    $c_lat = floatval($mychars[0]);
    $c_lng = floatval($mychars[1]);
    $km = 0.155;

    $ky = 40000 / 360;
    $kx = cos(pi() * $c_lat / 180.0) * $ky;
    $dx = abs($c_lng - $v_lng) * $kx;
    $dy = abs($c_lat - $v_lat) * $ky;
    $distance = sqrt(($dx * $dx) + ($dy * $dy));

    echo $distance . '<=' . $km . '<br>';
    echo $km . '<br>';

    if ($distance <= $km == true) {
        $in_time = date('Y-m-d H:i:s');
        // echo 'IN TIME: ' . $in_time . '<br>';
        // echo $distance . '<=' . $km . '<br>';

        $sql_update = "UPDATE puma_sap_data_trips SET close_time='$in_time', status=2 WHERE id='$sub_order_id'";
        if (mysqli_query($db, $sql_update)) {
            echo "Trip Closed successfully !";
        } else {
            echo "Error: " . $sql_update . " " . mysqli_error($db);
        }
    } else {
        echo 'Not IN<br>';
    }
}

mysqli_close($db);
echo "Last Run " . date('Y-m-d H:i:s');
?>