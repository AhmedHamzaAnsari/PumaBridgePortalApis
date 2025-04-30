<?php
ini_set('max_execution_time', '0');
// $url1 = $_SERVER['REQUEST_URI'];
// header("Refresh: 10; URL=$url1");
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




$currentHour = '09:00';
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

            // $overspeed_count = "";
            $overspeed_count = '
            <br/>
            <h3>Overspeed</h3>
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
                <table border = "1">
                    <tr>
                        <th>50+ Count</th>
                        <th>65+ Count</th>
                        <th>70+ Count</th>
                        
                    </tr>
            ';
            $sql__count = "SELECT SUM(CASE WHEN speed >= 55 and speed<=64 THEN 1 ELSE 0 END) AS `50+`,
            SUM(CASE WHEN speed >= 65 and speed<=69 THEN 1 ELSE 0 END) AS `65+`, 
            SUM(CASE WHEN speed >= 70  THEN 1 ELSE 0 END) AS `70+` FROM  sitara.driving_alerts as da 
            where $time_1 and da.created_by='$cartraige_id' 
            and da.type='Overspeed'";
            $result__count = mysqli_query($db, $sql__count);



            while ($row = mysqli_fetch_array($result__count)) {

                $fifty = $row['50+'];
                if ($fifty == null) {
                    $fifty = 0;
                }
                $sixty = $row['65+'];
                if ($sixty == null) {
                    $sixty = 0;
                }
                $seventy = $row['70+'];
                if ($seventy == null) {
                    $seventy = 0;
                }




                $overspeed_count .= '
                <tr>
                    <td class="text-center">' . $fifty . ' Times</td>
                    <td >' . $sixty . ' Times</td>
                    <td >' . $seventy . ' Times</td>
               
                </tr>
            ';



            }





            $overspeed_count .= '
                </table>
            </div>
            ';

            $night_voilation_t = '
            <br/>
            <h3>Night Voilation</h3>
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
                <table border = "1">
                    <tr>
                        <th>Night Voilation Count</th>
                        
                    </tr>
            ';
            $sql__count = "SELECT distinct(count(device_id)) as night_counting FROM  sitara.driving_alerts as da 
            join devicesnew as dc on dc.id=da.device_id
                        where $time_1 and da.created_by='$cartraige_id' and da.type='Night time violations'";
            $result__count = mysqli_query($db, $sql__count);



            while ($row = mysqli_fetch_array($result__count)) {

                $night_counting = $row['night_counting'];
                if ($night_counting == null) {
                    $night_counting = 0;
                }





                $night_voilation_t .= '
                <tr>
                    <td class="text-center">' . $night_counting . ' Times</td>
               
                </tr>
            ';



            }





            $night_voilation_t .= '
                </table>
            </div>
            ';

            $excess_driving = '
            <br/>
            <h3>Continuous Driving</h3>
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
                <table border = "1">
                    <tr>
                        <th>Continuous Driving Count</th>
                        
                    </tr>
            ';
            $excess__ = "SELECT count(*) as axcess_driving FROM sitara.axcess_driving_alerts as da 
            join users_devices_new as ud on ud.devices_id=da.vehicle_id
            where da.created_at BETWEEN CONCAT(CURDATE(), ' 06:00:00') AND CONCAT(CURDATE(), ' 18:00:00') 
            and duration>0 
            and da.created_by='$cartraige_id' 
            and ud.users_id='$cartraige_id' group by vehicle_id order by da.id desc";
            $result__excess__ = mysqli_query($db, $excess__);



            while ($row = mysqli_fetch_array($result__excess__)) {

                $axcess_driving = $row['axcess_driving'];








            }




            $excess_driving .= '
                <tr>
                    <td class="text-center">' . $axcess_driving . ' Times</td>
               
                </tr>
            ';
            $excess_driving .= '
                </table>
            </div>
            ';

            $black_spot_in = '
            <br/>
            <h3>Black Spot</h3>
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
                <table border = "1">
                    <tr>
                        <th>Black Spot Count</th>
                        
                    </tr>
            ';
            $black_spot_count = "SELECT count(*) as balck_count FROM sitara.geo_check as ck 
            join geofenceing as geo on geo.id=ck.geo_id 
            join users_devices_new as ud on ud.devices_id=ck.veh_id
            where geo.geotype='black Spote' and ck.log=1 and in_duration>=10 
            and $black_1 and ud.users_id='$cartraige_id'
            order by in_time desc ;";
            $result__black_spot_count = mysqli_query($db, $black_spot_count);



            while ($row = mysqli_fetch_array($result__black_spot_count)) {

                $balck_count = $row['balck_count'];








            }




            $black_spot_in .= '
                <tr>
                    <td class="text-center">' . $balck_count . ' Times</td>
               
                </tr>
            ';
            $black_spot_in .= '
                </table>
            </div>
            ';


            $nr_html = '
                <br/>
                <h3>NR</h3>
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
                <table border = "1">
                    <tr>
                        <th>NR Count</th>
                        
                    </tr>
            ';
            // $NR_sql = "SELECT count(*) as nr_count FROM sitara.driving_alerts as da 
            // where type='NR' and $time_1 and da.created_by='$cartraige_id' order by da.id desc";
            $todate = date("Y-m-d H:i:s", time());
            $prev_date = date("Y-m-d H:i:s", strtotime($todate . ' -1 day'));
            $NR_sql = "SELECT count(*) as nr_count FROM sitara.users_devices_new as ud 
            join sitara.devicesnew as dc on dc.id=ud.devices_id where dc.time <='$prev_date' and ud.users_id='$cartraige_id'";
            $result__NR_sql = mysqli_query($db, $NR_sql);



            while ($row = mysqli_fetch_array($result__NR_sql)) {

                $nr_count = $row['nr_count'];








            }




            $nr_html .= '
                <tr>
                    <td class="text-center">' . $nr_count . ' Times</td>
               
                </tr>
            ';
            $nr_html .= '
                </table>
            </div>
            ';





            //======================================================================================================================

            $message = '';


            $sql_get_cartraige_no = "SELECT * FROM sitara.report_email where user_id='$cartraige_id';";
            // echo $sql_get_cartraige_no .'<br>';
            $result_contact = mysqli_query($db, $sql_get_cartraige_no);

            $count_contact = mysqli_num_rows($result_contact);
            echo $count_contact . ' hamza <br>';
            if ($count_contact > 0) {
                while ($row = mysqli_fetch_array($result_contact)) {
                    $email = $row["email"];
                    echo $email . '<br>';
                    // echo smtp_mailer($email, $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation_t, $currentHour, $db);

                }
            }




            // echo smtp_mailer('abasit9119@gmail.com', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation_t, $currentHour, $db);
            // echo smtp_mailer('jahanzaib.javed@gno.com.pk', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation, $currentHour, $db);
            echo smtp_mailer('ahmedhamzaansari.99@gmail.com', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation_t,$currentHour,$db);
            // echo smtp_mailer('asad.saeed@gno.com.pk', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing,$db);
            // echo smtp_mailer('Omair.ahmed@rowsolution.com', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing,$db);




        }

    }



} else {
    // Do nothing or perform other actions
    echo "It's not 9 AM yets." . $currentHour;
}
$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

function fetch_customer_data($connect, $time_1, $cartraige_id)
{
    $today = date("Y-m-d");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    echo $query = "SELECT da.*,dc.name FROM  sitara.driving_alerts as da 
    join devicesnew as dc on dc.id=da.device_id
                where $time_1 and da.created_by='$cartraige_id'
                and da.type='Overspeed'";
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
                            <th>Vehicle #</th>
                            <th>Speed</th>
                            <th>Message</th>
                            <th>Time</th>
                            <th>Location</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                        </tr>
                ';
    foreach ($result as $row) {
        $output .= '
                <tr>
                    <td class="text-center">' . $row["name"] . '</td>
                    <td >' . $row["speed"] . '</td>
                    <td >' . $row["message"] . '</td>
    
                    <td>' . $row["time"] . '</td>
                    <td>' . $row["location"] . '</td>
                    <td>' . $row["lat"] . '</td>
                    <td>' . $row["lng"] . '</td>
    
                </tr>
            ';
    }
    $output .= '
                    </table>
                </div>
                ';
    return $output;
}
function excess_drive($connect, $time_1, $cartraige_id)
{
    $today = date("Y-m-d");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');


    // SELECT *,sum(duration) as sum_duration FROM sitara.axcess_driving_alerts as da 
// join users_devices_new as ud on ud.devices_id=da.vehicle_id
//             where da.created_at BETWEEN CONCAT(CURDATE(), " 06:00:00") AND CONCAT(CURDATE(), " 18:00:00") and da.created_by='1' and ud.users_id='1' group by vehicle_id order by da.id desc ;

    //  $query = "SELECT * FROM sitara.axcess_driving_alerts as da 
    // join users_devices_new as ud on ud.devices_id=da.vehicle_id
    //             where $time_1 and da.created_by='$cartraige_id' and ud.users_id='$cartraige_id' order by da.id desc";
    $query = "SELECT *,sum(duration) as sum_duration FROM sitara.axcess_driving_alerts as da 
    join users_devices_new as ud on ud.devices_id=da.vehicle_id
    where da.created_at BETWEEN CONCAT(CURDATE(), ' 06:00:00') AND CONCAT(CURDATE(), ' 18:00:00') and duration>0 and da.created_by='$cartraige_id' and ud.users_id='$cartraige_id' group by vehicle_id order by da.id desc";
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
                                <th>Vehicle #</th>
                            <th>Message</th>
                            <th>Duration</th>
                            <th>Time</th>
                        
                        </tr>
                ';
    foreach ($result as $row) {
        $output .= '
                <tr>
                    <td class="text-center">' . $row["vehicle_name"] . '</td>
                    <td >' . $row["message"] . '</td>
                    <td >' . $row["duration"] . ' Min</td>
                    <td>' . $row["created_at"] . '</td>
    
                </tr>
            ';
    }
    $output .= '
                    </table>
                </div>
                ';
    return $output;
}

function black_spot($connect, $black_1, $cartraige_id)
{
    $today = date("Y-m-d");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    $query = "SELECT ck.*,geo.consignee_name,dc.name FROM sitara.geo_check as ck 
                join geofenceing as geo on geo.id=ck.geo_id 
                join devicesnew as dc on dc.id=ck.veh_id 
                join users_devices_new as ud on ud.devices_id=ck.veh_id
                where geo.geotype='black Spote' and ck.log=1 and ck.in_duration>=10 and $black_1 and ud.users_id='$cartraige_id'  order by in_time desc limit 20";
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
                                <th>Vehicle #</th>
                                <th>Back Spot</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>In Duration</th>
                            
                            </tr>
                    ';
    foreach ($result as $row) {
        $output .= '
                        <tr>
                            <td class="text-center">' . $row["name"] . '</td>
                            <td >' . $row["consignee_name"] . '</td>
                            <td >' . $row["in_time"] . ' </td>
                            <td>' . $row["out_time"] . '</td>
                            <td>' . $row["in_duration"] . '</td>
    
                        </tr>
                    ';
    }
    $output .= '
                    </table>
                </div>
                ';
    return $output;
}

function nr_v($connect, $time_1, $cartraige_id)
{


    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    $todate = date("Y-m-d H:i:s", time());
    $prev_date = date("Y-m-d H:i:s", strtotime($todate . ' -1 day'));

    // $query = "SELECT da.*,dc.name FROM sitara.driving_alerts as da
    //             join devicesnew as dc on dc.id=da.device_id
    //              where type='NR' and $time_1 and da.created_by='$cartraige_id' order by da.id desc";
    $query = "SELECT dc.name,dc.tracker as vehicle_make,dc.time,dc.speed,dc.location as vlocation ,dc.lat as latitude,dc.lng as longitude FROM sitara.users_devices_new as ud 
    join sitara.devicesnew as dc on dc.id=ud.devices_id where dc.time <='$prev_date' and ud.users_id='$cartraige_id'";
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
                            <th>Reg No</th>
                            <th>Reporting Time</th>
                            <th>Location</th>
                            <th>Coordinates</th>
                            <th>Speed</th>
                                
                            </tr>
                    ';
    foreach ($result as $row) {
        $output .= '
                <tr>
                    <td class="text-center">' . $row["name"] . '</td>
                    <td >' . $row["time"] . '</td>
                    <td >' . $row["vlocation"] . '</td>
                    <td >' . $row["latitude"] . ' , ' . $row["longitude"] . '</td>
                    <td >' . $row["speed"] . '</td>
    
                </tr>
            ';
    }
    $output .= '
                    </table>
                </div>
                ';
    return $output;
}

function current_location($connect, $time_1, $cartraige_id)
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
function night_time_voilation($connect, $time_1, $cartraige_id)
{
    $today = date("Y-m-d");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    $query = "SELECT da.*,dc.name FROM  sitara.driving_alerts as da 
    join devicesnew as dc on dc.id=da.device_id
                where $time_1 and da.created_by='$cartraige_id'
                and da.type='Night time violations'";
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
                            <th>Vehicle #</th>
                            <th>Speed</th>
                            <th>Message</th>
                            <th>Address</th>
                            <th>Time</th>
                        </tr>
                ';
    foreach ($result as $row) {
        $output .= '
                <tr>
                    <td class="text-center">' . $row["name"] . '</td>
                    <td >' . $row["speed"] . '</td>
                    <td >' . $row["message"] . '</td>
                    <td >' . $row["location"] . '</td>
    
                    <td>' . $row["created_at"] . '</td>
    
                </tr>
            ';
    }
    $output .= '
                    </table>
                </div>
                ';
    return $output;
}

function smtp_mailer($to, $time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation_t, $currentHour, $db)
{
    $connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");
    $alert_today = date("Y-m-d");
    $alert_today_time = date("Y-m-d H:i:s");
    $verificationCode = generateVerificationCode();
    // $alert_link = "";
    $alert_link = "http://151.106.17.246:8080/sitara/email_alert_link.php?id=".$cartraige_id."&from=".$alert_today."&name=".$cartraige_name."&interval=".$currentHour."&e_id=".$to."";
    $alert_link_button = '<a href="'.$alert_link.'">Visit Alert page</a>';
    $file_name = 'files/' . md5(rand()) . '.pdf';
    $html_code = '<div class="container">
                    <div class="row">
                        <div class="col-md-12">
                        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">' . $cartraige_name . '</h2>
                        
                        </div>
                            <h6>Report Name : Vehicle Activity </h6>
                            <br/>
                            <h6>Time : ' . $report_timing . '</h6>
                        
    
                        
    
                    </div>
                </div>';
    $html_code .= 'Overspeed ';
    $html_code .= fetch_customer_data($connect, $time_1, $cartraige_id);
    $html_code .= 'Excess Driving ';
    $html_code .= excess_drive($connect, $time_1, $cartraige_id);
    $html_code .= 'Black Spot ';
    $html_code .= black_spot($connect, $black_1, $cartraige_id);
    $html_code .= 'NR';
    $html_code .= nr_v($connect, $time_1, $cartraige_id);
    $html_code .= 'Night Voilations';
    $html_code .= night_time_voilation($connect, $time_1, $cartraige_id);
    // $html_code .= 'Current Status';
    // $html_code .= current_location($connect, $time_1, $cartraige_id);

    $pdf = new Pdf();
    $pdf->load_html($html_code);
    $pdf->render();
    $file = $pdf->output();
    $file1 = $pdf->output();
    file_put_contents($file_name, $file);



    $file_name1 = 'files/' . md5(rand()) . '.pdf';
    $html_code1 = '<div class="container">
                    <div class="row">
                        <div class="col-md-12">
                        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">' . $cartraige_name . '</h2>
                        
                        </div>
                            <h6>Report Name : Vehicle Activity </h6>
                            <br/>
                            <h6>Time : ' . $report_timing . '</h6>
                        
    
                        
    
                    </div>
                </div>';

    $html_code1 .= 'Current Status';
    $html_code1 .= current_location($connect, $time_1, $cartraige_id);

    $pdf1 = new Pdf();
    $pdf1->load_html($html_code1);
    $pdf1->render();
    $file1 = $pdf1->output();
    $file1 = $pdf1->output();
    file_put_contents($file_name1, $file1);

    // require 'class/class.phpmailer.php';
    $mail = new PHPMailer();
    $mail->SMTPDebug = 3;
    $mail->IsSMTP();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "sitaras222@gmail.com";
    $mail->Password = "kjyqvamkejoqtbki";
    $mail->SetFrom("sitaras222@gmail.com");
    $mail->AddAddress($to);
    $mail->WordWrap = 50; //Sets word wrapping on the body of the message to a given number of characters
    $mail->IsHTML(true); //Sets message type to HTML				
    $mail->AddAttachment($file_name); //Adds an attachment from a path on the filesystem
    $mail->AddAttachment($file_name1); //Adds an attachment from a path on the filesystem
    $mail->Subject = $cartraige_name . ' Vehicle Activity  ' . $report_timing; //Sets the Subject of the message
    
    $mail->Body = '<h1>SITARA.<h1><h3>Please Find details report of Overspeed in attach PDF File.</h3><br/>' . $overspeed_count . '<br/>' . $excess_driving . '<br/>' . $black_spot_in . '<br/>' . $nr_html . '<br/>' . $night_voilation_t . '<br/>' . $alert_link_button. '<br/> Verification Code : ' . $verificationCode; //An HTML or plain text message body
    if ($mail->Send()) //Send an Email. Return true on success or false on error
    {
        echo $message = '<label class="text-success">Customer Details has been send successfully...</label>';
        
       echo $verify = "INSERT INTO `sitara`.`alert_email_link_verification`
        (`cart_id`,
        `email`,
        `verify_code`,
        `date`,
        `status`,
        `time_interval`,
        `created_at`)
        VALUES
        ('$cartraige_id',
        '$to',
        '$verificationCode',
        '$alert_today',
        '0',
        '$currentHour',
        '$alert_today_time');";
        mysqli_query($db, $verify);


    } else {
        echo 'Mail Not send';
    }
    unlink($file_name);
}
function generateVerificationCode($length = 6)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $code;
}



// echo $list;


?>