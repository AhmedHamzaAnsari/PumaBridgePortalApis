
<?php
            ini_set('max_execution_time', '0');
            $url1=$_SERVER['REQUEST_URI'];
            header("Refresh: 300; URL=$url1");

        
            include("config_sms.php");
            $vehicle_name;


                $sql="SELECT cd.cartrauge_id,us.name FROM sitara.cartrauge_depot_check as cd join sitara.users as us on us.id=cd.cartrauge_id where cd.sms_alerts='Overspeeding';";
                $result = mysqli_query($db,$sql);
                $count = mysqli_num_rows($result);
                if($count>0) {
                    while( $row = mysqli_fetch_array($result) ){
                        // $userid = $row['id'];
                        $cartraige_id = $row['cartrauge_id'];
                        $cartraige_name = $row['name'];
    
                        // echo $cartraige_id;
                        echo' <br> <br> -------------------'.$cartraige_name.'----------------------<br>';
    
    
                        // -----------------------cartrige vehicle ----------------------------
    
                        $sql_devices="SELECT ud.devices_id,ud.users_id FROM sitara.cartrauge_depot_check as cd join sitara.users_devices as ud on ud.users_id=cd.cartrauge_id where ud.users_id='$cartraige_id';";
                        $result_devices = mysqli_query($db,$sql_devices);
                        $count_devices = mysqli_num_rows($result_devices);
                        if($count_devices > 0) {
                            while( $row = mysqli_fetch_array($result_devices) ){ 
                                $devices_id = $row['devices_id'];
    
                                // echo $devices_id .'<br>';
    
    
                                // --------------------------------------depot check-----------------------------------------------
                                
                                $date = date('Y-m-d');
                                $pre_hours= date("Y-m-d H:i:s", strtotime("-1 hour"));
                
                                $sql_depot_status="SELECT pos.vehicle_id,pos.device_id,pos.id,pos.time,pos.speed,pos.vlocation,pos.power FROM `positions` as pos INNER join `devices` as dv on pos.id = dv.latestPosition_id INNER JOIN `users_devices` as ud on dv.uniqueId=ud.devices_id where pos.speed >= 55 and pos.time >='$date 00:00:00' and ud.users_id='$cartraige_id' and ud.devices_id='$devices_id'";
                                // echo $sql_depot_status .'<br>';
                                $result_depot_status = mysqli_query($db,$sql_depot_status);
                                $count_depot_status = mysqli_num_rows($result_depot_status);
                                if($count_depot_status > 0) {
                                    while( $row = mysqli_fetch_array($result_depot_status) ){ 
                                        $vehicle_name = $row['vehicle_id'];
                                        $time = $row['time'];
                                        $speed = $row['speed'];
                                        $vlocation = $row['vlocation'];
                                        $power = $row['power'];
                                        $id = $row['id'];
    
                                        echo '-------------------------------- <br>';
    
                                        
                                        
                                        
                                        if($speed>55){

                                            $insert_overspeed_log="SELECT * FROM sitara.overspeed_log where latestposition_id='$id' and device_id='$devices_id';";
                                            // echo $sql_depot_status .'<br>';
                                            $result_insert_overspeed_log = mysqli_query($db,$insert_overspeed_log);
                                            $count_insert_overspeed_log = mysqli_num_rows($result_insert_overspeed_log);
                                            if($count_insert_overspeed_log > 0) {
                                                echo 'New record Not Found of '. $vehicle_name.'<br>';
                                                 
                                            }
                                            else{
                                                echo 'MSG Shoot <br>';

                                                $curr_time=date('Y-m-d H:i:s');
                                                $sql_insert_log = "INSERT INTO `sitara`.`overspeed_log`(`latestposition_id`,`device_id`,`time`,`msg_status`,`user_id`)VALUES('$id','$devices_id','$curr_time','0','$cartraige_id');";
                                                // echo $sql_update;
                                                if(mysqli_query($db, $sql_insert_log)){
                                                echo '</br>';
                                                echo 'MSG Shoot <br>';

                                                $sql_get_cartraige_no = "SELECT * FROM sitara.activity_contact where cartrauge_id='$cartraige_id' and contact='03137152168' or contact='03094441583';";
                                                $result_contact = mysqli_query($db,$sql_get_cartraige_no);

                                                $count_contact = mysqli_num_rows($result_contact);
                                                echo $count_contact .' hamza <br>';
                                                if ($count_contact > 0) {
                                                    while ($row = mysqli_fetch_array($result_contact)) {
                                                        $contact = $row["contact"];
                                                        echo $contact .'<br>';

                                                        $msg = "اوور سپیڈ
                                                        آپ".$vehicle_name." 55 کلومیٹر فی گھنٹہ کی رفتار کی حد کو عبور کر رہے ہیں آہستہ چلائیں";
                                                        echo $msg;
                                                    
                                                        $curl = curl_init();
                                            
                                                        curl_setopt_array($curl, array(
                                                        CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03202538075&Password=Jazz@123&From=SitaraLive&To='.$contact.'&Message='.urlencode($msg),
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
                                                            echo '</br>';
                                                            $date = date('Y-m-d H:i:s');
                                                            $sql_update_log = "INSERT INTO `sitara`.`cartraige_sms_log`(`send_to`,`message`,`time`,`cartaige_id`) VALUES ('$contact','$msg','$date','$cartraige_id');";
                                                            // echo $sql_update;
                                                            if(mysqli_query($db, $sql_update_log)){
                                                            echo '</br>';
                                
                                                                echo "Log Insert successfully <br>.";
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
                    
                                                    echo "Log Insert successfully. <br>";
                                                } else {
                                                echo '</br>';
                    
                                                    echo "ERROR: Could not able to execute $sql_insert_log. " . mysqli_error($db);
                                                }
                                            }
                                            echo $vehicle_name .'<br> location = '.$vlocation.' <br> speed = '.$speed.' <br> time = '.$time.'<br>';
                                            // echo $vehicle_name.' Vehicle Doing overspeed at speed '.$speed.' <br>';
                                            // echo 'Shoot MSG <br>';
                                            echo $id.' = position id <br>';

    
                                           
                                        }else{
                                            echo $vehicle_name.' No overspeed <br>';
                                            echo $id.' = position id <br>';
    
                                        }
    
                
    
                                    }
                        
                        
                                }
                                else{
                                    // echo '<h4>'.$devices_id.' Not en</h4>';
            
                                }
                
                            
    
                                            
                                        
                                            
    
                            }
                
                
                        }
                        else{
                            echo '<h1>No Contraige Found</h1>';
    
                        }
    
                     
        
                        
        
                       
    
                                    
                                
                                    
    
                    }
        
        
                }
                else{
                    echo '<h1>No Records Found</h1> <br>';
                }
          

            
            echo' <br> <br> -------------------Last Update----------------------<br>';
            
            echo date('Y-m-d H:i:s').'<br>';
            echo 'Overspeed Service <br>';

?>