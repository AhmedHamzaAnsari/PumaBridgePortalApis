<?php
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 20; URL=$url1");

include ("config_sms.php");
$vehicle_name;
echo '<h1>TRIP START SMS SERVICE</h1>';
echo '<br/>';
$sql = "SELECT sl.*,ts.consignee_contact_1,ts.consignee_contact_2,ts.consignee_contact_3,mt.driver_contact,mt.driver_helper_contact,ts.products,ts.quantity,ts.eta,ts.consignee_name,ts.del_order,ts.vehicle_name,mt.driver_cnic,mt.driver_helper_cnic,mt.without_tracker,mt.driver_name,mt.driver_helper_name FROM sitara.sms_link as sl join sitara.trip_sub as ts on sl.sub_id=ts.id join sitara.trip_main as mt on mt.id=ts.main_id where sl.sms='0';";
$result = mysqli_query($db, $sql);
$count = mysqli_num_rows($result);
echo $count;
if ($count > 0)
{
    while ($row = mysqli_fetch_array($result))
    {
        // $userid = $row['id'];
        $sub_id = $row['sub_id'];
        $status = $row['status'];
        $sms = $row['sms'];
        $number = $row['consignee_contact_1'];
        $number2 = $row['consignee_contact_2'];
        $number3 = $row['consignee_contact_3'];
        $driver_contact = $row['driver_contact'];
        $driver_helper_contact = $row['driver_helper_contact'];
        $products = $row['products'];
        $quantity = $row['quantity'];
        $eta = $row['eta'];
        $consignee_name = $row['consignee_name'];
        $del_order = $row['del_order'];
        $vehicle_name = $row['vehicle_name'];
        $driver_cnic = $row['driver_cnic'];
        $driver_helper_cnic = $row['driver_helper_cnic'];
        $without_tracker = $row['without_tracker'];
        $driver_name = $row['driver_name'];
        $driver_helper_name = $row['driver_helper_name'];

        $contact_no = array();
        $driverss = array();
        $driver_cnic_arr = array();
        $driver_name_arr = array();
        array_push($driverss, $driver_contact);
        array_push($contact_no, $number, $number2, $number3);
        array_push($driver_cnic_arr, $driver_cnic);
        array_push($driver_name_arr, $driver_name);
        print_r($driverss);
        print_r($driver_cnic_arr);

        echo '<br/>';
        echo $sub_id;
        echo '<br/>';
        echo $sms;
        echo '<br/>';

        $enc_ID = base64_encode($sub_id);
        echo '<br/>';
        echo $sub_id . ' ==> ' . $enc_ID;
        echo '<br/>';

        $response;

        if ($sms == '0')
        {
            if ($without_tracker == '0')
            {
                echo '</br>';
                $sql_update_sms = "UPDATE `sms_link` SET `sms`='1' WHERE sub_id='$sub_id'";
                // echo $sql_update;
                if (mysqli_query($db, $sql_update_sms))
                {
                    echo '</br>';
                    echo "Status were updated successfully.";
                    echo '</br>';
                    echo '----------------------------------------------------------<br>';
                    foreach ($contact_no as $value)
                    {
                        echo "SMS Shoot consignee" . $value . '<br>';;
                        $date_ = date("Y-m-d");
                        $time_ = date("H:i");
                        $msg = '' . $date_ . ' (' . $time_ . ') , ' . $value . ',' . $consignee_name . ',' . $vehicle_name . ',' . $driver_name . ' (D),' . $driver_contact . ',' . $driver_cnic . ',' . $products . ',' . $quantity . ' (L),Track Order Click Link : ';
                        $text = 'Track Your Trip';
                        $url = 'http://151.106.17.246:8080/sitara/view_link.php?c=' . $enc_ID;
                        $f_url = $msg . ' ' . $url;
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03028652867&Password=Jazz@123&From=SITARA-LIVE&To=' . $value . '&Message=' . urlencode($f_url) ,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);
                        echo $response . '<br>';

                        curl_close($curl);
                        if ($response === 'Message Sent Successfully!')
                        {
                            echo '</br>';
                            echo $response . ' to ' . $value;
                            $date = date('Y-m-d H:i:s');

                            $sql_update = "INSERT INTO `sitara`.`trip_start_sms_log`(`sub_id`,`send_to`,`message`,`time`) VALUES ('$sub_id','$value','$f_url','$date');";

                            // echo $sql_update;
                            if (mysqli_query($db, $sql_update))
                            {
                                echo '</br>';

                                echo "Status were updated successfully.";
                                // header("location: manageGroup.php");
                                
                            }
                            else
                            {
                                echo '</br>';

                                echo $sql_update;
                                echo '</br>';

                                echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
                            }

                        }
                        else
                        {
                            echo '</br>';
                            echo 'Msg Not Send ' . $value;
                        }

                    }

                    foreach ($driverss as $value)
                    {
                        $i = 0;
                        echo "SMS Shoot consignee" . $value . '<br>';;
                        $msg = 'آپ کا لوڈ ڈیلیوری کے لیے تیار ہے براہ کرم اپنی گاڑی ' . $vehicle_name . ' تک پہنچیں۔';

                        $text = 'Track Your Trip';
                        $url = 'http://151.106.17.246:8080/sitara/view_link.php?c=' . $enc_ID;
                        $f_url = $msg . ' ' . $url;
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03028652867&Password=Jazz@123&From=SITARA-LIVE&To=' . $value . '&Message=' . urlencode($msg) ,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                        ));
                        // echo "https://connect.jazzcmt.com/sendsms_url.html?Username=03028652867&Password=Jazz@123&From=SITARA-LIVE&To=".$value."&Message='.urlencode($msg)";
                        $response = curl_exec($curl);
                        echo $response . '<br>';

                        $i++;

                        curl_close($curl);
                        if ($response === 'Message Sent Successfully!')
                        {
                            echo '</br>';
                            echo $response . ' to ' . $value;
                            $date = date('Y-m-d H:i:s');

                            $sql_update = "INSERT INTO `sitara`.`trip_start_sms_log`(`sub_id`,`send_to`,`message`,`time`) VALUES ('$sub_id','$value','$msg','$date');";
                            // echo $sql_update;
                            if (mysqli_query($db, $sql_update))
                            {
                                echo '</br>';

                                echo "Status were updated successfully.";
                                // header("location: manageGroup.php");
                                
                            }
                            else
                            {
                                echo '</br>';

                                echo $sql_update;
                                echo '</br>';
                                echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
                            }

                            // echo '</br>';
                            // $sql_update_sms = "UPDATE `sms_link` SET `sms`='1' WHERE sub_id='$sub_id'";
                            // // echo $sql_update;
                            // if(mysqli_query($db, $sql_update_sms)){
                            //     echo '</br>';
                            //     echo "Status were updated successfully.";
                            //     echo '</br>';
                            // } else {
                            //     echo '</br>';
                            //     echo $sql_update_sms;
                            //     echo '</br>';
                            //         echo "ERROR: Could not able to execute $sql_update_sms. " . mysqli_error($db);
                            // }
                            

                            
                        }
                        else
                        {
                            echo '</br>';
                            echo 'Msg Not Send ' . $value;
                        }

                    }

                }
                else
                {
                    echo '</br>';

                    echo $sql_update_sms;
                    echo '</br>';

                    echo "ERROR: Could not able to execute $sql_update_sms. " . mysqli_error($db);
                }
            }
            else
            {

                echo '</br>';
                $sql_update_sms = "UPDATE `sms_link` SET `sms`='1' WHERE sub_id='$sub_id'";
                // echo $sql_update;
                if (mysqli_query($db, $sql_update_sms))
                {
                    echo '</br>';

                    echo "Status were updated successfully.";
                    echo '</br>';
                    echo "SMS Shoot consignee" . $value . '<br>';
                    foreach ($contact_no as $value)
                    {
                        // $msg = 'Dear '.$consignee_name.' delivery for your order Will be dispatched shortly .Your Trip Driver name is
                        //         '.$driver_name.' and contact number is
                        //         '.$driver_contact.' having CNIC
                        //         '.$driver_cnic.' and Vehicle Number is
                        //         '.$vehicle_name.' and ETA is
                        //         '.$eta.' . Your product is
                        //         '.$products.'  and Quantity is
                        //         '.$quantity.' ltr. Your Order Quee # is
                        //         '.$del_order.'. This Vehicle is without Tracker ';
                        $date_ = date("Y-m-d");
                        $time_ = date("H:i");
                        $msg = '' . $date_ . ' (' . $time_ . ') , ' . $value . ',' . $consignee_name . ',' . $vehicle_name . ',' . $driver_name . ' (D),' . $driver_contact . ',' . $driver_cnic . ',' . $products . ',' . $quantity . ' (L),Track Order Click Link : ';

                        $text = 'Track Your Trip';
                        $url = 'http://151.106.17.246:8080/sitara/view_link.php?c=' . $enc_ID;
                        $f_url = $msg . ' ' . $url;
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03028652867&Password=Jazz@123&From=SITARA-LIVE&To=' . $value . '&Message=' . urlencode($msg) ,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);
                        echo $response . '<br>';

                        curl_close($curl);
                        if ($response === 'Message Sent Successfully!')
                        {
                            echo '</br>';
                            echo $response . ' to ' . $value;

                            $date = date('Y-m-d H:i:s');

                            $sql_update = "INSERT INTO `sitara`.`trip_start_sms_log`(`sub_id`,`send_to`,`message`,`time`) VALUES ('$sub_id','$value','$msg','$date');";
                            // echo $sql_update;
                            if (mysqli_query($db, $sql_update))
                            {
                                echo '</br>';

                                echo "Status were updated successfully.";
                                // header("location: manageGroup.php");
                                
                            }
                            else
                            {
                                echo '</br>';

                                echo $sql_update;
                                echo '</br>';

                                echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
                            }

                        }
                        else
                        {
                            echo '</br>';
                            echo 'Msg Not Send ' . $value;
                        }

                    }

                    foreach ($driverss as $value)
                    {
                        $i = 0;
                        echo "SMS Shoot consignee" . $value . '<br>';;
                        // $msg = 'پیارے ڈرائیور CNIC نمبر '.$driver_cnic_arr[$i].' کے ساتھ آپ کا لوڈ ڈیلیوری کے لیے تیار ہے براہ کرم اپنی گاڑی '.$vehicle_name.' تک پہنچیں۔';
                        $msg = 'آپ کا لوڈ ڈیلیوری کے لیے تیار ہے براہ کرم اپنی گاڑی ' . $vehicle_name . ' تک پہنچیں۔';

                        $text = 'Track Your Trip';
                        // $url = 'http://151.106.17.246:8080/sitara/view_link.php?c='.$enc_ID;
                        $f_url = $msg . ' ' . $url;
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03028652867&Password=Jazz@123&From=SITARA-LIVE&To=' . $value . '&Message=' . urlencode($msg) ,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                        ));

                        $response = curl_exec($curl);
                        echo $response . '<br>';

                        $i++;

                        curl_close($curl);
                        if ($response === 'Message Sent Successfully!')
                        {
                            echo '</br>';
                            echo $response . ' to ' . $value;

                            $date = date('Y-m-d H:i:s');

                            $sql_update = "INSERT INTO `sitara`.`trip_start_sms_log`(`sub_id`,`send_to`,`message`,`time`) VALUES ('$sub_id','$value','$msg','$date');";
                            // echo $sql_update;
                            if (mysqli_query($db, $sql_update))
                            {
                                echo '</br>';

                                echo "Status were updated successfully.";
                                // header("location: manageGroup.php");
                                
                            }
                            else
                            {
                                echo '</br>';

                                echo $sql_update;
                                echo '</br>';

                                echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
                            }

                            // echo '</br>';
                            // $sql_update_sms = "UPDATE `sms_link` SET `sms`='1' WHERE sub_id='$sub_id'";
                            // // echo $sql_update;
                            // if(mysqli_query($db, $sql_update_sms)){
                            //     echo '</br>';
                            //     echo "Status were updated successfully.";
                            //     echo '</br>';
                            // } else {
                            //     echo '</br>';
                            //     echo $sql_update_sms;
                            //     echo '</br>';
                            //         echo "ERROR: Could not able to execute $sql_update_sms. " . mysqli_error($db);
                            // }
                            
                        }
                        else
                        {
                            echo '</br>';
                            echo 'Msg Not Send ' . $value;
                        }

                    }

                  
                    
                }
                else
                {
                    echo '</br>';

                    echo $sql_update_sms;
                    echo '</br>';

                    echo "ERROR: Could not able to execute $sql_update_sms. " . mysqli_error($db);
                }

            }


            
        }

    }

}

else
{
    echo '<h1>No Records Found to send Msg</h1>';
}

?>
