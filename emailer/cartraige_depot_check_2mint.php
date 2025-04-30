
<?php
ini_set("max_execution_time", "0");
$url1 = $_SERVER["REQUEST_URI"];
header("Refresh: 120; URL=$url1");

include "config_sms.php";
$vehicle_name='';
$sql =
    "SELECT cd.cartrauge_id,us.name FROM sitara.cartrauge_depot_check as cd join sitara.users as us on us.id=cd.cartrauge_id where cd.sms_alerts='black spot';";
$result = mysqli_query($db, $sql);
$count = mysqli_num_rows($result);
if ($count > 0) {
    while ($row = mysqli_fetch_array($result)) {
        // $userid = $row['id'];
        $cartraige_id = $row["cartrauge_id"];
        $cartraige_name = $row["name"];

        // echo $cartraige_id;
        echo " <br> <br> -------------------" .
            $cartraige_name .
            "----------------------<br>";

        // -----------------------cartrige vehicle ----------------------------

        $sql_devices = "SELECT ud.devices_id,ud.users_id FROM sitara.cartrauge_depot_check as cd join sitara.users_devices as ud on ud.users_id=cd.cartrauge_id where ud.users_id='$cartraige_id' and cd.sms_alerts='black spot';";
        $result_devices = mysqli_query($db, $sql_devices);
        $count_devices = mysqli_num_rows($result_devices);
        if ($count_devices > 0) {
            while ($row = mysqli_fetch_array($result_devices)) {
                $devices_id = $row["devices_id"];

                // echo $devices_id .'<br>';

                // --------------------------------------depot check-----------------------------------------------

                $date = date("Y-m-d");

                $sql_depot_status = "SELECT gc.*,geo.geotype,dc.name,geo.Coordinates FROM sitara.geo_check  as gc 
                            join sitara.geofenceing as geo on geo.id=gc.geo_id
                            join sitara.users_devices as ud on ud.devices_id=gc.veh_id
                            join sitara.devices as dc on dc.uniqueId=ud.devices_id
                            where geo.geotype='black Spote' and gc.in_time>'$date 00:00:00' and ud.users_id='$cartraige_id' and ud.devices_id='$devices_id' and gc.log=0 and gc.depot_status='0' group by veh_id order by gc.id desc";
                $result_depot_status = mysqli_query($db, $sql_depot_status);
                $count_depot_status = mysqli_num_rows($result_depot_status);
                if ($count_depot_status > 0) {
                    while ($row = mysqli_fetch_array($result_depot_status)) {
                        $in_time = $row["in_time"];
                        $depot_status = $row["depot_status"];
                        $geo_id = $row["geo_id"];
                        $veh_id = $row["veh_id"];
                        $geo_check_id = $row["id"];
                        $vehicle_name = $row["name"];
                        $Coordinates = $row["Coordinates"];

                        echo "-------------------------------- <br>";

                        echo $in_time .
                            "<br> " .
                            $geo_id .
                            " <br>" .
                            $veh_id .
                            " <br>";

                        $d1 = new DateTime($in_time);
                        $d2 = new DateTime(date("Y-m-d H:i:s"));
                        $interval = $d1->diff($d2);
                        $diffInMinutes = $interval->i; //23

                        echo 'mint '. $diffInMinutes . "<br>";
                        $response='';
                        if ($diffInMinutes == 2 || $diffInMinutes>2) {
                            echo "Shoot MSG <br>";

                            $sql_update_depot_status = "UPDATE sitara.geo_check set depot_status='1' where veh_id='$veh_id' and geo_id='$geo_id' and id='$geo_check_id'";
                            echo $sql_update_depot_status;
                            if (mysqli_query($db, $sql_update_depot_status)) {
                                echo "Log update successfully ! <br>";
                                $sql_get_cartraige_no = "SELECT * FROM sitara.activity_contact where cartrauge_id='$cartraige_id';";
                                // echo $sql_get_cartraige_no .'<br>';
                                $result_contact = mysqli_query($db,$sql_get_cartraige_no);

                                $count_contact = mysqli_num_rows($result_contact);
                                echo $count_contact .' hamza <br>';
                                if ($count_contact > 0) {
                                    while ($row = mysqli_fetch_array($result_contact)) {
                                        $contact = $row["contact"];
                                        echo $contact .'<br>';

                                        // $msg = 'Dear ' . $cartraige_name . ' your vehicle' . $vehicle_name . ' is in black Spot last two minutes';
                                        $msg = "بلیک اسپاٹ
                                        آپکی ٹینک لاری نمبر ".$vehicle_name." نا مناسب جگہ کھڑی ہے یہاں ٹینک لاری کھڑی کرنے سے اجتناب کریں۔ ".$in_time." ".$Coordinates."";
                                        // echo  $msg;
                                    
                                        $curl = curl_init();
                            
                                        curl_setopt_array($curl, array(
                                        CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03028652867&Password=Jazz@123&From=SITARA-LIVE&To='.$contact.'&Message='.urlencode($msg),
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_ENCODING => '',
                                        CURLOPT_MAXREDIRS => 10,
                                        CURLOPT_TIMEOUT => 0,
                                        CURLOPT_FOLLOWLOCATION => true,
                                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                        CURLOPT_CUSTOMREQUEST => 'GET',
                                        ));
                                
                                        $response = curl_exec($curl);

                                
                                        curl_close($curl);
                                        if ($response==='Message Sent Successfully!'){
                                            echo '</br>';
                                            echo $response .' to '.$contact ;
                                            $date = date('Y-m-d H:i:s');
                                            $sql_update_log = "INSERT INTO `sitara`.`cartraige_sms_log`(`send_to`,`message`,`time`,`cartaige_id`) VALUES ('$contact','$msg','$date','$cartraige_id');";
                                            // echo $sql_update;
                                            if(mysqli_query($db, $sql_update_log)){
                                            echo '</br>';
                
                                                echo "Log Insert successfully.";
                                            } else {
                                            echo '</br>';
                
                                                // echo $sql_update_log;
                                            echo '</br>';
                
                                                echo "ERROR: Could not able to execute $sql_update_log. " . mysqli_error($db);
                                            }
                
                                        }
                                        else{
                                            echo '</br>';
                                            echo 'Msg Not Send '.$contact;
                                            echo '</br>';
                                            
                                        }
                                    }
                                }
                                else{
                                     echo "No Contact found of this cartraige  <br>";

                                }
                            }
                        } else {
                            echo "Time Available to Shoot MSG <br>";
                        }
                    }
                } else {
                    // echo '<h4>'.$devices_id.' Not en</h4>';
                }
            }
        } else {
            echo "<h1>No Contraige Found</h1>";
        }
    }
} else {
    echo "<h1>No Records Found</h1> <br>";
}

echo " <br> <br> -------------------Last Update----------------------<br>";

echo date("Y-m-d H:i:s") . "<br>";


?>
