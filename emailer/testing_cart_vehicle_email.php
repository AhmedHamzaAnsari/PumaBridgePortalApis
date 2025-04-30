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
                        <th>50+ Count With-Load</th>
                        <th>50+ Count Without-Load</th>
                        <th>65+ Count With-Load</th>
                        <th>65+ Count Without-Load</th>
                        <th>70+ Count With-Load</th>
                        <th>70+ Count Without-Load</th>
                        
                    </tr>
            ';
            $sql__count = "SELECT 
            SUM(CASE WHEN da.speed >= 55 and da.speed<=64 and tm.delivery_no IS NOT NULL THEN 1 ELSE 0 END) AS `50+ With-Load`,
            SUM(CASE WHEN da.speed >= 55 and da.speed<=64 and tm.delivery_no IS NULL THEN 1 ELSE 0 END) AS `50+ Without-Load`,
            SUM(CASE WHEN da.speed >= 65 and da.speed<=69 and tm.delivery_no IS NOT NULL THEN 1 ELSE 0 END) AS `65+ With-Load`, 
            SUM(CASE WHEN da.speed >= 65 and da.speed<=69 and tm.delivery_no IS NULL THEN 1 ELSE 0 END) AS `65+ Without-Load`, 
            SUM(CASE WHEN da.speed >= 70 and tm.delivery_no IS NOT NULL  THEN 1 ELSE 0 END) AS `70+ With-Load`, 
            SUM(CASE WHEN da.speed >= 70 and tm.delivery_no IS NULL  THEN 1 ELSE 0 END) AS `70+ Without-Load`  
            FROM  sitara.driving_alerts as da 
            join devicesnew as dc on dc.id=da.device_id 
            left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
            where $time_1 and da.created_by='$cartraige_id' 
            and da.type='Overspeed'";
            $result__count = mysqli_query($db, $sql__count);



            while ($row = mysqli_fetch_array($result__count)) {

                $fifty = $row['50+ With-Load'];
                if ($fifty == null) {
                    $fifty = 0;
                }
                $fifty_no = $row['50+ Without-Load'];
                if ($fifty_no == null) {
                    $fifty_no = 0;
                }

                $sixty = $row['65+ With-Load'];
                if ($sixty == null) {
                    $sixty = 0;
                }
                $sixty_no = $row['65+ Without-Load'];
                if ($sixty_no == null) {
                    $sixty_no = 0;
                }

                $seventy = $row['70+ With-Load'];
                if ($seventy == null) {
                    $seventy = 0;
                }
                $seventy_no = $row['70+ Without-Load'];
                if ($seventy_no == null) {
                    $seventy_no = 0;
                }




                $overspeed_count .= '
                <tr>
                    <td class="text-center">' . $fifty . ' Times</td>
                    <td >' . $fifty_no . ' Times</td>
                    <td >' . $sixty . ' Times</td>
                    <td >' . $sixty_no . ' Times</td>
                    <td >' . $seventy . ' Times</td>
                    <td >' . $seventy_no . ' Times</td>
               
                </tr>
            ';



            }





            $overspeed_count .= '
                </table>
            </div>
            ';

            $cart_vehicle = '
            <br/>
            <h3>Cartraige Vehicle</h3>
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
                        <th>Total Vehicles</th>
                        <th>On-Load Vehicles</th>
                        
                    </tr>
            ';
            $sql__count_cart_veh = "SELECT
            (SELECT count(*) as total_vehicle FROM sitara.devicesnew as dc 
            join users_devices_new as ud on ud.devices_id=dc.id where ud.users_id='$cartraige_id') AS total_vehicle,
            (SELECT count(*) as load_vehicle FROM sitara.sap_data_upload as sp
            join devicesnew as dc on dc.name=sp.tl_no
            join users_devices_new as ud on ud.devices_id=dc.id where ud.users_id='$cartraige_id') AS load_vehicle;";
            $result__count_cart_veh = mysqli_query($db, $sql__count_cart_veh);



            while ($row_cart_veh = mysqli_fetch_array($result__count_cart_veh)) {

                $total_vehicle = $row_cart_veh['total_vehicle'];
                $load_vehicle = $row_cart_veh['load_vehicle'];






                $cart_vehicle .= '
                <tr>
                    <td class="text-center">' . $total_vehicle . ' Times</td>
                    <td class="text-center">' . $load_vehicle . ' Times</td>
               
                </tr>
            ';



            }





            $cart_vehicle .= '
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
                        <th>Night Voilation With-Load</th>
                        <th>Night Voilation Without-Load</th>
                        
                    </tr>
            ';
            $sql__count = "SELECT 
            distinct(count(device_id)) as night_counting ,
            SUM(CASE WHEN tm.delivery_no IS NOT NULL  THEN 1 ELSE 0 END) AS `With-Load`, 
            SUM(CASE WHEN tm.delivery_no IS NULL  THEN 1 ELSE 0 END) AS `Without-Load`
            FROM  sitara.driving_alerts as da 
            join devicesnew as dc on dc.id=da.device_id
            left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
                        where $time_1 and da.created_by='$cartraige_id' and da.type='Night time violations'";
            $result__count = mysqli_query($db, $sql__count);



            while ($row = mysqli_fetch_array($result__count)) {

                $With_Load = $row['With-Load'];
                if ($With_Load == null) {
                    $With_Load = 0;
                }

                $Without_Load = $row['Without-Load'];

                if ($Without_Load == null) {
                    $Without_Load = 0;
                }





                $night_voilation_t .= '
                <tr>
                    <td class="text-center">' . $With_Load . ' Times</td>
                    <td class="text-center">' . $Without_Load . ' Times</td>
               
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
                        <th>Continuous Driving With-Load</th>
                        <th>Continuous Driving Without-Load</th>
                        
                    </tr>
            ';
            $excess__ = "SELECT 
            count(*) as axcess_driving ,
            SUM(CASE WHEN tm.delivery_no IS NOT NULL THEN 1 ELSE 0 END) AS `With-Load`, 
            SUM(CASE WHEN tm.delivery_no IS NULL THEN 1 ELSE 0 END) AS `Without-Load`
            FROM sitara.axcess_driving_alerts as da
            join users_devices_new as ud on ud.devices_id=da.vehicle_id
            join devicesnew as dc on dc.id=da.vehicle_id
            left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
            where $time_1
            and duration>0
            and da.created_by='$cartraige_id'
            and ud.users_id='$cartraige_id' order by da.id desc";
            $result__excess__ = mysqli_query($db, $excess__);



            while ($row = mysqli_fetch_array($result__excess__)) {

                $With_Load = $row['With-Load'];
                if ($With_Load == null) {
                    $With_Load = 0;
                }

                $Without_Load = $row['Without-Load'];

                if ($Without_Load == null) {
                    $Without_Load = 0;
                }







                $excess_driving .= '
                    <tr>
                        <td class="text-center">' . $With_Load . ' Times</td>
                        <td class="text-center">' . $Without_Load . ' Times</td>
                   
                    </tr>
                ';
            }




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
                        <th>Black Spot With-Load</th>
                        <th>Black Spot Without-Load</th>
                        
                    </tr>
            ';
            $black_spot_count = "SELECT 
            count(*) as balck_count ,
            SUM(CASE WHEN tm.delivery_no IS NOT NULL  THEN 1 ELSE 0 END) AS `With-Load`, 
            SUM(CASE WHEN tm.delivery_no IS NULL  THEN 1 ELSE 0 END) AS `Without-Load`
            FROM sitara.geo_check as ck 
            join geofenceing as geo on geo.id=ck.geo_id 
            join users_devices_new as ud on ud.devices_id=ck.veh_id
            join devicesnew as dc on dc.id=ck.veh_id 
            left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
            where geo.geotype='black Spote' and ck.log=1 and in_duration>=10 
            and $black_1 and ud.users_id='$cartraige_id'
            order by in_time desc ;";
            $result__black_spot_count = mysqli_query($db, $black_spot_count);



            while ($row = mysqli_fetch_array($result__black_spot_count)) {

                $balck_count = $row['balck_count'];


                $With_Load = $row['With-Load'];
                if ($With_Load == null) {
                    $With_Load = 0;
                }

                $Without_Load = $row['Without-Load'];

                if ($Without_Load == null) {
                    $Without_Load = 0;
                }






                $black_spot_in .= '
                    <tr>
                        <td class="text-center">' . $With_Load . ' Times</td>
                        <td class="text-center">' . $Without_Load . ' Times</td>
                   
                    </tr>
                ';





            }




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
                        <th>NR With-Load</th>
                        <th>NR Without-Load</th>
                        
                    </tr>
            ';
            // $NR_sql = "SELECT count(*) as nr_count FROM sitara.driving_alerts as da 
            // where type='NR' and $time_1 and da.created_by='$cartraige_id' order by da.id desc";
            $todate = date("Y-m-d H:i:s", time());
            $prev_date = date("Y-m-d H:i:s", strtotime($todate . ' -1 day'));
            $NR_sql = "SELECT 
            count(*) as nr_count ,
            SUM(CASE WHEN tm.delivery_no IS NOT NULL  THEN 1 ELSE 0 END) AS `With-Load`, 
            SUM(CASE WHEN tm.delivery_no IS NULL  THEN 1 ELSE 0 END) AS `Without-Load`
            FROM sitara.users_devices_new as ud 
            join sitara.devicesnew as dc on dc.id=ud.devices_id
            left join sitara.sap_data_upload as tm on tm.tl_no=dc.name where dc.time <='$prev_date' and ud.users_id='$cartraige_id'";
            $result__NR_sql = mysqli_query($db, $NR_sql);



            while ($row = mysqli_fetch_array($result__NR_sql)) {

                $nr_count = $row['nr_count'];



                $With_Load = $row['With-Load'];
                if ($With_Load == null) {
                    $With_Load = 0;
                }

                $Without_Load = $row['Without-Load'];

                if ($Without_Load == null) {
                    $Without_Load = 0;
                }






                $nr_html .= '
                    <tr>
                        <td class="text-center">' . $With_Load . ' Times</td>
                        <td class="text-center">' . $Without_Load . ' Times</td>
                   
                    </tr>
                ';




            }





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
                    echo smtp_mailer($email, $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation_t, $currentHour, $db,$cart_vehicle);

                }
            }




            echo smtp_mailer('abasit9119@gmail.com', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation_t, $currentHour, $db,$cart_vehicle);
            echo smtp_mailer('jahanzaib.javed@gno.com.pk', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation, $currentHour, $db,$cart_vehicle);
            // echo smtp_mailer('ahmedhamzaansari.99@gmail.com', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation_t, $currentHour, $db, $cart_vehicle);
            // echo smtp_mailer('asad.saeed@gno.com.pk', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing,$db,$cart_vehicle);
            // echo smtp_mailer('Omair.ahmed@rowsolution.com', $dur_time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing,$db,$cart_vehicle);




        }

    }



} else {
    // Do nothing or perform other actions
    echo "It's not 9 AM yets." . $currentHour;
}
$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");


function get_cart_vehi($connect, $time_1, $cartraige_id)
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
                            <th>Total Vehicle</th>
                            <th>On-Load Vehicle</th>
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
function fetch_customer_data($connect, $time_1, $cartraige_id)
{
    $today = date("Y-m-d");
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    echo $query = "SELECT da.*,dc.name,tm.delivery_no FROM  sitara.driving_alerts as da 
    join devicesnew as dc on dc.id=da.device_id 
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
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
                            <th>Is-Load</th>
                        </tr>
                ';
    foreach ($result as $row) {
        $isload = '';
        if ($row['delivery_no'] == '') {
            $isload = 'Without Load';

        } else {
            $isload = 'With Load';

        }

        $output .= '
                <tr>
                    <td class="text-center">' . $row["name"] . '</td>
                    <td >' . $row["speed"] . '</td>
                    <td >' . $row["message"] . '</td>
    
                    <td>' . $row["time"] . '</td>
                    <td>' . $row["location"] . '</td>
                    <td>' . $row["lat"] . '</td>
                    <td>' . $row["lng"] . '</td>
                    <td>' . $isload . '</td>
    
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
    $query = "SELECT *,sum(duration) as sum_duration,tm.delivery_no FROM sitara.axcess_driving_alerts as da 
    join users_devices_new as ud on ud.devices_id=da.vehicle_id
    join devicesnew as dc on dc.id=da.vehicle_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
    where $time_1 and duration>0 and da.created_by='$cartraige_id' and ud.users_id='$cartraige_id' group by vehicle_id order by da.id desc";
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
                            <th>Is-Load</th>

                        
                        </tr>
                ';
    foreach ($result as $row) {
        $isload = '';
        if ($row['delivery_no'] == '') {
            $isload = 'Without Load';

        } else {
            $isload = 'With Load';

        }
        $output .= '
                <tr>
                    <td class="text-center">' . $row["vehicle_name"] . '</td>
                    <td >' . $row["message"] . '</td>
                    <td >' . $row["duration"] . ' Min</td>
                    <td>' . $row["created_at"] . '</td>
                    <td>' . $isload . '</td>

    
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

    $query = "SELECT ck.*,geo.consignee_name,dc.name,tm.delivery_no FROM sitara.geo_check as ck 
    join geofenceing as geo on geo.id=ck.geo_id 
    join devicesnew as dc on dc.id=ck.veh_id 
    join users_devices_new as ud on ud.devices_id=ck.veh_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
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
                            <th>Is-Load</th>

                            
                            </tr>
                    ';
    foreach ($result as $row) {
        $isload = '';
        if ($row['delivery_no'] == '') {
            $isload = 'Without Load';

        } else {
            $isload = 'With Load';

        }
        $output .= '
                        <tr>
                            <td class="text-center">' . $row["name"] . '</td>
                            <td >' . $row["consignee_name"] . '</td>
                            <td >' . $row["in_time"] . ' </td>
                            <td>' . $row["out_time"] . '</td>
                            <td>' . $row["in_duration"] . '</td>
                            <td>' . $isload . '</td>

    
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
    $query = "SELECT dc.name,dc.tracker as vehicle_make,dc.time,dc.speed,dc.location as vlocation ,dc.lat as latitude,dc.lng as longitude,tm.delivery_no FROM sitara.users_devices_new as ud 
    join sitara.devicesnew as dc on dc.id=ud.devices_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name where dc.time <='$prev_date' and ud.users_id='$cartraige_id'";
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
                            <th>Is-Load</th>

                                
                            </tr>
                    ';
    foreach ($result as $row) {
        $isload = '';
        if ($row['delivery_no'] == '') {
            $isload = 'Without Load';

        } else {
            $isload = 'With Load';

        }
        $output .= '
                <tr>
                    <td class="text-center">' . $row["name"] . '</td>
                    <td >' . $row["time"] . '</td>
                    <td >' . $row["vlocation"] . '</td>
                    <td >' . $row["latitude"] . ' , ' . $row["longitude"] . '</td>
                    <td >' . $row["speed"] . '</td>
                    <td>' . $isload . '</td>

    
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


    $query = "SELECT dc.*,tm.delivery_no FROM sitara.users_devices_new as ud 
    join devicesnew as dc on dc.id=ud.devices_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name where ud.users_id=$cartraige_id";
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
                <th>Is-Load</th>

			</tr>
	';
    foreach ($result as $row) {
        $isload = '';
        if ($row['delivery_no'] == '') {
            $isload = 'Without Load';

        } else {
            $isload = 'With Load';

        }
        $output .= '
			<tr>
			<td class="text-center">' . $row["name"] . '</td>
			<td >' . $row["location"] . '</td>
			<td >' . $row["speed"] . '</td>
			<td>' . $row["ignition"] . '</td>
			<td>' . $row["lat"] . '</td>
			<td>' . $row["lng"] . '</td>
			<td>' . $row["time"] . '</td>
            <td>' . $isload . '</td>



            
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

    $query = "SELECT da.*,dc.name,tm.delivery_no FROM  sitara.driving_alerts as da 
    join devicesnew as dc on dc.id=da.device_id
    left join sitara.sap_data_upload as tm on tm.tl_no=dc.name
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
                            <th>Is-Load</th>

                        </tr>
                ';
    foreach ($result as $row) {
        $isload = '';
        if ($row['delivery_no'] == '') {
            $isload = 'Without Load';

        } else {
            $isload = 'With Load';

        }
        $output .= '
                <tr>
                    <td class="text-center">' . $row["name"] . '</td>
                    <td >' . $row["speed"] . '</td>
                    <td >' . $row["message"] . '</td>
                    <td >' . $row["location"] . '</td>
    
                    <td>' . $row["created_at"] . '</td>
                    <td>' . $isload . '</td>

    
                </tr>
            ';
    }
    $output .= '
                    </table>
                </div>
                ';
    return $output;
}

function smtp_mailer($to, $time, $vehi_name, $overspeed_count, $excess_driving, $black_spot_in, $nr_html, $time_1, $cartraige_id, $black_1, $cartraige_name, $report_timing, $night_voilation_t, $currentHour, $db, $cart_vehicle)
{
    $connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");
    $alert_today = date("Y-m-d");
    $alert_today_time = date("Y-m-d H:i:s");
    $verificationCode = generateVerificationCode();
    // $alert_link = "";
    $alert_link = "http://151.106.17.246:8080/sitara/email_alert_link.php?id=" . $cartraige_id . "&from=" . $alert_today . "&name=" . $cartraige_name . "&interval=" . $currentHour . "&e_id=" . $to . "";
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
    $mail->Subject = $cartraige_name . ' Vehicle Activity ' . $report_timing; //Sets the Subject of the message
    $mail->Body = '<h1>SITARA.<h1><h3>Please Find details report of Overspeed in attach PDF File.</h3><br/>' . $cart_vehicle . '<br/>' . $overspeed_count . '<br/>' . $excess_driving . '<br/>' . $black_spot_in . '<br/>' . $nr_html . '<br/>' . $night_voilation_t . '<br/>' . $alert_link . '<br/> Verification Code : ' . $verificationCode; //An HTML or plain text message body
    if ($mail->Send()) //Send an Email. Return true on success or false on error
    {
        echo $message = '<label class="text-success">Customer Details has been send successfully...</label>';

        $verify = "INSERT INTO `sitara`.`alert_email_link_verification`
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