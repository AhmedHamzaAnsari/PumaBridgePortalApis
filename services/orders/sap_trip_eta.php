<?php
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 20; URL=$url1");
include ("../../config.php");
set_time_limit(5000);

$access_key = '03201232927';

$pass = isset($_GET["key"]) ? $_GET["key"] : '';
$date = date('Y-m-d H:i:s');
echo "<h1>Sap Trip ETA Check service .</h1><br>";

if ($pass != '') {
    if ($pass == $access_key) {
        // $sql_query1 = "SELECT dt.*,ps.vehicle,dl.`co-ordinates` as co,dl.name,geo.Coordinates as depo_co
        // FROM puma_sap_data_trips as dt 
        // join dealers as dl on dl.sap_no=CAST(TRIM(LEADING '0' FROM dt.dealer_sap ) AS UNSIGNED)
        // join puma_sap_data as ps on ps.id=dt.main_id 
        // JOIN order_main as om ON om.SaleOrder=dt.salesapNo 
        // join geofenceing as geo on geo.id=ps.depo
        // where om.delivered_status=1 and dt.status=0 and ps.is_tracker=1 
        //  group by om.SaleOrder;";

        $sql_query1 = "SELECT dt.*,ps.vehicle,dl.`co-ordinates` as co,dl.name,ps.depo,ps.is_tracker
        FROM puma_sap_data_trips AS dt
        JOIN dealers AS dl ON dl.sap_no = CAST(TRIM(LEADING '0' FROM dt.dealer_sap) AS UNSIGNED)
        JOIN puma_sap_data AS ps ON ps.id = dt.main_id
        WHERE ps.is_tracker IN(0,1) and dt.eta='' and ps.created_at >= NOW() - INTERVAL 10 DAY
        GROUP BY dt.salesapNo order by dt.id desc";

        $result1 = $db->query($sql_query1) or die("Error in SQL query: " . $db->error);

        while ($user = $result1->fetch_assoc()) {
            $id = $user['id'];
            $co = $user['co'];
            $depo = $user['depo'];
            $vehicle = $user['vehicle'];
            $mychars = explode(', ', $co);
            $c_lat = floatval($mychars[0]);
            $c_lng = floatval($mychars[1]);

            $sql = "SELECT * FROM geofenceing where code='$depo'";

            // echo $sql;

            $result = mysqli_query($db, $sql);
            $row = mysqli_fetch_array($result);


            $depo_co = $row['Coordinates'];
            $mydepo = explode(', ', $depo_co);
            $depo_lat = floatval($mydepo[0]);
            $depo_lng = floatval($mydepo[1]);

            // $distance = calculateDistance($depo_lat, $depo_lng, $c_lat, $c_lng);
            // $distance = mapquest_distance($depo_lat, $depo_lng, $c_lat, $c_lng);
            $distance = haversineGreatCircleDistance($depo_lat, $depo_lng, $c_lat, $c_lng);


            if ($distance !== null) {
                $sql_query2 = "SELECT *,DATE_ADD(DATE_ADD(created_at, INTERVAL ($distance/30) HOUR), INTERVAL 20 MINUTE) as eta_time FROM puma_sap_data_trips where id='$id';";
                $result2 = $db->query($sql_query2) or die("Error in SQL query: " . $db->error);

                while ($user2 = $result2->fetch_assoc()) {
                    $eta_time = $user2['eta_time'];
                    $created_at = $user2['created_at'];

                    $update = "UPDATE `puma_sap_data_trips`
                        SET
                        `status` = '1',
                        `active_time` = '$created_at',
                        `eta` = '$eta_time',
                        `distance` = '$distance',
                        `dealer_lat` = '$c_lat',
                        `dealer_lng` = '$c_lng'
                        WHERE `id` = '$id';";

                    if ($db->query($update)) {
                        echo 'ETA Updated';
                    } else {
                        echo 'Error updating ETA: ' . $db->error;
                    }
                }
            } else {
                echo 'Error calculating distance.';
            }

            // $get_vehicle_data_query = "SELECT * FROM devicesnew WHERE name = '$vehicle'";
            // $vehicle_data_result = mysqli_query($db, $get_vehicle_data_query);

            // if (!$vehicle_data_result) {
            //     die("Query failed: " . mysqli_error($db));
            // }

            // if ($vehicle_row = mysqli_fetch_array($vehicle_data_result)) {
            //     $v_lat = $vehicle_row['lat'];

            //     $vehicle_id = $vehicle_row['id'];
            //     $v_lat = $vehicle_row['lat'];
            //     $v_lng = $vehicle_row['lng'];




            // }

        }
    } else {
        echo 'Wrong Key...';
    }
} else {
    echo 'Key is Required';
}

function calculateDistance($originLat, $originLng, $destLat, $destLng)
{
    $apiKey = 'AIzaSyD9ztWZaPapSg_s2x_VIKx2DwO5zq0gcDU';
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins={$originLat},{$originLng}&destinations={$destLat},{$destLng}&key={$apiKey}";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data['status'] === 'OK' && isset($data['rows'][0]['elements'][0]['distance']['value'])) {
        $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];
        $distanceInKm = $distanceInMeters / 1000; // Convert meters to kilometers
        return $distanceInKm;
    } else {
        return null;
    }
}
function mapquest_distance($originLat, $originLng, $destLat, $destLng){
    $apiKey = 'bISm97KoaeLqoBEhF7ubFYDjr8zyH1BH';

    $url = "https://www.mapquestapi.com/directions/v2/route?key={$apiKey}&from={$originLat},{$originLng}&to={$destLat},{$destLng}&outFormat=json&ambiguities=ignore&routeType=fastest&doReverseGeocode=false&enhancedNarrative=false&avoidTimedConditions=false";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Check if the route data and distance exist
    if (isset($data['route']['distance'])) {
        // Extract the distance in the specified unit (assuming kilometers in the API response)
        $distanceInKm = $data['route']['distance'];

        // Return the distance in kilometers
        return $distanceInKm;
    } else {
        // Return null if the distance data is not available
        return null;
    }
}

function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // Convert degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    // Calculate the difference in latitudes and longitudes
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    // Haversine formula to calculate the distance
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
              cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    
    // Multiply by the Earth's radius (in meters)
    $distanceInMeters = $angle * $earthRadius;

    // Convert meters to kilometers
    $distanceInKilometers = $distanceInMeters / 1000;

    // Add 50% extra distance
    $distanceInKilometers *= 1.5;

    return $distanceInKilometers;
}

echo 'Service Last Run => ' . date('Y-m-d H:i:s');
?>