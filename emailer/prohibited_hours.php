
<?php
            ini_set('max_execution_time', '0');
            $url1=$_SERVER['REQUEST_URI'];
            header("Refresh: 1800; URL=$url1");

        
            include("config_sms.php");
            $vehicle_name;

            $check_time=date('H');
            echo $check_time . '<br>';

            if($check_time=='00' || $check_time=='01' || $check_time=='02' || $check_time=='03' || $check_time=='04'){

                $sql="SELECT cd.cartrauge_id,us.name FROM sitara.cartrauge_depot_check as cd join sitara.users as us on us.id=cd.cartrauge_id where cd.sms_alerts='prohibited hours';";
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
                
                                $sql_depot_status="SELECT * FROM sitara.positions where device_id='$devices_id' and time>'$date 00:00:00' and time<'$date 04:59:59' and speed>0 and power='1'  order by time desc limit 1 ;";
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
    
                                        echo '-------------------------------- <br>';
    
                                        echo $vehicle_name .'<br> location = '.$vlocation.' <br> speed = '.$speed.' <br> time = '.$time.'<br>';
    
    
    
                                        if($speed>0 && $power==1){
                                            echo 'Vehicle Moving <br>';
                                            echo 'Shoot MSG <br>';
    
                                           
                                        }else{
                                            echo 'Vehicle Not Moving <br>';
    
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
            }else{
                echo '<h1>Service run between 1 AM to 5 AM</h1> <br>';

            }

            
            echo' <br> <br> -------------------Last Update----------------------<br>';
            
            echo date('Y-m-d H:i:s').'<br>';
            echo 'prohibited_hours Service <br>';


?>