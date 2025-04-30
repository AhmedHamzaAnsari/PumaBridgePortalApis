<?php
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 10; URL=$url1");
// error_reporting(0);
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Ptoptrack@(!!@');
define('DB_DATABASE', 'sitara');
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);


//index.php
ini_set('memory_limit', '-1');
set_time_limit(500);

include('class/class.phpmailer.php');
include('pdf.php');

$today = date("Y-m-d");
$_to_today = date("Y-m-d H:i:s");
echo $_to_today . 'Run time <br>';
$dur_time = $today . ' 00:00:00 ' . ' - ' . $_to_today;
$report_time = 1;
$email = 'ahmedhamzaansari.99@gmail.com';
$report = 'vehicle';
$user_id;
$privilege = 'Admin';
$time_1 = "";
$black_1 = "";
$cartraige_name = "";
$report_timing = "";




// $currentHour = '21:00';
$currentHour = date('H:i');
if ($currentHour == '09:00') {
    $report_timing = '18:00 to 06:00';

    $time_1 = 'da.created_at BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 1 DAY) + INTERVAL 18 HOUR AND DATE(NOW()) + INTERVAL 6 HOUR ';
    $black_1 = 'ck.in_time BETWEEN DATE_SUB(DATE(NOW()), INTERVAL 1 DAY) + INTERVAL 18 HOUR AND DATE(NOW()) + INTERVAL 6 HOUR ';
} else if ($currentHour == '21:00') {
    $time_1 = 'da.created_at BETWEEN CONCAT(CURDATE(), " 06:00:00") AND CONCAT(CURDATE(), " 18:00:00") ';
    $black_1 = 'ck.in_time BETWEEN CONCAT(CURDATE(), " 06:00:00") AND CONCAT(CURDATE(), " 18:00:00") ';
    $report_timing = '06:00 to 18:00';
}

// Check if the current hour is 9 AM
if ($currentHour == '09:00' || $currentHour == '21:00') {
    // Execute your PHP script here

    echo 'Hamza';


    $sql =
        "SELECT * FROM sitara.users where privilege='Cartraige'and name!='Basit';";
    $result_U = mysqli_query($db, $sql);
    $count_U = mysqli_num_rows($result_U);
    if ($count_U > 0) {
        while ($row_U = mysqli_fetch_array($result_U)) {
            // $userid = $row['id'];
            $cartraige_id = $row_U["id"];
            $cartraige_name = $row_U["name"];



            $sql_get_cartraige_no = "SELECT * FROM sitara.report_email where user_id='$cartraige_id';";
            // echo $sql_get_cartraige_no .'<br>';
            $result_contact = mysqli_query($db, $sql_get_cartraige_no);

            $count_contact = mysqli_num_rows($result_contact);
            echo $count_contact . ' hamza <br>';
            if ($count_contact > 0) {
                while ($row = mysqli_fetch_array($result_contact)) {
                    $email = $row["email"];
                    echo $email . '<br>';
                    echo smtp_mailer($email, $dur_time, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $currentHour);

                }
            }






        }

    }



} else {
    // Do nothing or perform other actions
    echo "It's not 9 AM yets." . $currentHour;
}
$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

function fetch_customer_data($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour)
{
    $today = date("Y-m-d H:i:s");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    $query = "SELECT da.*,dc.name,
    CASE WHEN tm.delivery_no IS NOT NULL THEN '1' ELSE '0' END AS `is_load`
    FROM  sitara.driving_alerts as da 
    join devicesnew as dc on dc.id=da.device_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
    where $time_1 and da.created_by='$cartraige_id'
    and da.type='Overspeed'";
    
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    foreach ($result as $row) {
        $name = $row["name"];
        $message = $row["message"];
        $time = $row["time"];
        $location = $row["location"];
        $lat = $row["lat"];
        $lng = $row["lng"];
        $speed = $row["speed"];
        $device_id = $row["device_id"];
        $is_load = $row["is_load"];

        $insert_overspeed = "INSERT INTO `sitara`.`activity_overspeed_voiltion`
        (`vehicle_id`,
        `vehicle_name`,
        `message`,
        `time`,
        `location`,
        `latitude`,
        `longitude`,
        `cartraige_id`,
        `cartriage_name`,
        `time_peiod`,
        `speed`,
        `is_load`,
        `created_at`,
        `created_by`)
        VALUES
        ('$device_id',
        '$name',
        '$message',
        '$time',
        '$location',
        '$lat',
        '$lng',
        '$cartraige_id',
        '$cartraige_name',
        '$currentHour',
        '$speed',
        '$is_load',
        '$today',
        '$cartraige_id');";

        if ($connect->query($insert_overspeed) === TRUE) {
            echo "data inserted <br>";
        } else {
            echo "failed <br>";
        }

    }


}
function excess_drive($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour)
{
    $today = date("Y-m-d H:i:s");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    $query = "SELECT da.*,sum(duration) as sum_duration,
    CASE WHEN tm.delivery_no IS NOT NULL THEN '1' ELSE '0' END AS `is_load`
    FROM sitara.axcess_driving_alerts as da 
    join users_devices_new as ud on ud.devices_id=da.vehicle_id
    join devicesnew as dc on dc.id=da.vehicle_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
    where $time_1 and duration>0 and da.created_by='$cartraige_id' and ud.users_id='$cartraige_id' group by vehicle_id order by da.id desc";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    foreach ($result as $row) {
        $name = $row["vehicle_name"];
        $time = $row["created_at"];
        $device_id = $row["vehicle_id"];
        $message = $row["message"];
        $duration = $row["duration"];
        $is_load = $row["is_load"];

        $insert_overspeed = "INSERT INTO `sitara`.`activity_excess_driving_voiltion`
        (`vehicle_id`,
        `vehicle_name`,
        `time`,
        `cartraige_id`,
        `cartraige_name`,
        `time_peiod`,
        `message`,
        `duration`,
        `is_load`,
        `created_at`,
        `created_by`)
        VALUES
        ('$device_id',
        '$name',
        '$time',
        '$cartraige_id',
        '$cartraige_name',
        '$currentHour',
        '$message',
        '$duration',
        '$is_load',
        '$today',
        '$cartraige_id');";

        if ($connect->query($insert_overspeed) === TRUE) {
            echo "data inserted <br>";
        } else {
            echo "failed <br>";
        }
    }

}

function black_spot($connect, $black_1, $cartraige_id, $cartraige_name, $currentHour)
{
    $today = date("Y-m-d H:i:s");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    $query = "SELECT ck.*,geo.consignee_name,dc.name,
    CASE WHEN tm.delivery_no IS NOT NULL THEN '1' ELSE '0' END AS `is_load`
    FROM sitara.geo_check as ck 
    join geofenceing as geo on geo.id=ck.geo_id 
    join devicesnew as dc on dc.id=ck.veh_id 
    join users_devices_new as ud on ud.devices_id=ck.veh_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
    where geo.geotype='black Spote' and ck.log=1 and ck.in_duration>=10 and $black_1 and ud.users_id='$cartraige_id'  order by in_time desc";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    foreach ($result as $row) {
        $name = $row["name"];
        $geo_id = $row["geo_id"];
        $consignee_name = $row["consignee_name"];
        $in_duration = $row["in_duration"];
        $in_time = $row["in_time"];
        $out_time = $row["out_time"];
        $device_id = $row["veh_id"];
        $is_load = $row["is_load"];

        $insert_overspeed = "INSERT INTO `sitara`.`activity_blackspot_voiltion`
        (`vehicle_id`,
        `vehicle_name`,
        `blackspot_id`,
        `blackspot_name`,
        `in_time`,
        `out_time`,
        `in_duration`,
        `cartraige_id`,
        `cartraige_name`,
        `time_peiod`,
        `is_load`,
        `created_at`,
        `created_by`)
        VALUES
        ('$device_id',
        '$name',
        '$geo_id',
        '$consignee_name',
        '$in_time',
        '$out_time',
        '$in_duration',
        '$cartraige_id',
        '$cartraige_name',
        '$currentHour',
        '$is_load',
        '$today',
        '$cartraige_id');";

        if ($connect->query($insert_overspeed) === TRUE) {
            echo "data inserted <br>";
        } else {
            echo "failed <br>";
        }
    }
}

function nr_v($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour)
{

    $today = date("Y-m-d H:i:s");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    $todate = date("Y-m-d H:i:s", time());
    $prev_date = date("Y-m-d H:i:s", strtotime($todate . ' -1 day'));

        $query = "SELECT dc.id,dc.name,dc.tracker as vehicle_make,dc.time,dc.speed,dc.location as vlocation ,dc.lat as latitude,dc.lng as longitude,
        CASE WHEN tm.delivery_no IS NOT NULL THEN '1' ELSE '0' END AS `is_load`
        FROM sitara.users_devices_new as ud 
        join sitara.devicesnew as dc on dc.id=ud.devices_id 
        left join sitara.sap_data_upload as tm on tm.tl_no=dc.name where dc.time <='$prev_date' and ud.users_id='$cartraige_id'";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    foreach ($result as $row) {
        $name = $row["name"];
        $time = $row["time"];
        $location = $row["vlocation"];
        $lat = $row["latitude"];
        $lng = $row["longitude"];
        $speed = $row["speed"];
        $device_id = $row["id"];
        $is_load = $row["is_load"];

        $insert_overspeed = "INSERT INTO `sitara`.`activity_nr_voiltion`
        (`vehicle_id`,
        `vehicle_name`,
        `time`,
        `location`,
        `latitude`,
        `longitude`,
        `cartraige_id`,
        `cartriage_name`,
        `time_peiod`,
        `speed`,
        `is_load`,
        `created_at`,
        `created_by`)
        VALUES
        ('$device_id',
        '$name',
        '$time',
        '$location',
        '$lat',
        '$lng',
        '$cartraige_id',
        '$cartraige_name',
        '$currentHour',
        '$speed',
        '$is_load',
        '$today',
        '$cartraige_id');";

        if ($connect->query($insert_overspeed) === TRUE) {
            echo "data inserted <br>";
        } else {
            echo "failed <br>";
        }

    }
}

function current_location($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour)
{


    $query = "SELECT * FROM sitara.users_devices_new as ud join devicesnew as dc on dc.id=ud.devices_id where ud.users_id=$cartraige_id";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();
    $output = '
	<div class="table-responsive">
	<style>
    table, th, td {
    border: 1px solid black;
    border-collapse: collapse;
    }
    th, td {
        padding:10px;
    }
    </style>
		<table >
			<tr>
                <th class="text-center">Plate.No</th>
                <th class="text-center">Location</th>
                <th class="text-center">Speed</th>
                <th class="text-center">Power</th>
                <th class="text-center">Latitude</th>
                <th class="text-center">Longitude</th>
                <th class="text-center">Time</th>
			</tr>
	';
    foreach ($result as $row) {
        $output .= '
			<tr>
			<td class="text-center">' . $row["name"] . '</td>
			<td >' . $row["location"] . '</td>
			<td >' . $row["speed"] . '</td>
			<td>' . $row["ignition"] . '</td>
			<td>' . $row["lat"] . '</td>
			<td>' . $row["lng"] . '</td>
			<td>' . $row["time"] . '</td>


            
			</tr>
		';
    }
    $output .= '
		</table>
	</div>
	';
    return $output;
}
function night_time_voilation($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour)
{
    $today = date("Y-m-d H:i:s");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    $query = "SELECT da.*,dc.name,
    CASE WHEN tm.delivery_no IS NOT NULL THEN '1' ELSE '0' END AS `is_load`
    FROM  sitara.driving_alerts as da 
    join devicesnew as dc on dc.id=da.device_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
    where $time_1 and da.created_by='$cartraige_id'
    and da.type='Night time violations'";
    $statement = $connect->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    foreach ($result as $row) {
        $name = $row["name"];
        $time = $row["created_at"];
        $device_id = $row["device_id"];
        $message = $row["message"];
        $duration = '';
        $is_load = $row["is_load"];

        $insert_overspeed = "INSERT INTO `sitara`.`activity_night_voilation_voiltion`
        (`vehicle_id`,
        `vehicle_name`,
        `time`,
        `cartraige_id`,
        `cartriage_name`,
        `time_peiod`,
        `message`,
        `is_load`,
        `created_at`,
        `created_by`)
        VALUES
        ('$device_id',
        '$name',
        '$time',
        '$cartraige_id',
        '$cartraige_name',
        '$currentHour',
        '$message',
        '$is_load',
        '$today',
        '$cartraige_id');";

        if ($connect->query($insert_overspeed) === TRUE) {
            echo "data inserted <br>";
        } else {
            echo "failed <br>";
        }
    }
}

function smtp_mailer($to, $time, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $currentHour)
{
    $connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");
    fetch_customer_data($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour);
    excess_drive($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour);
    black_spot($connect, $black_1, $cartraige_id, $cartraige_name, $currentHour);
    nr_v($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour);
    night_time_voilation($connect, $time_1, $cartraige_id, $cartraige_name, $currentHour);

}

// echo $list;


?>