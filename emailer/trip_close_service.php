<?php
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 20; URL=$url1");

include ("config_sms.php");
echo '<h1>TRIP Close SMS SERVICE</h1>';
echo '<br/>';
$vehicle_name;
$sql = "SELECT tc.id as table_id,ts.id,ts.consignee_code,ts.consignee_contact_1,ts.consignee_contact_2,ts.consignee_contact_3,ts.consignee_name,ts.vehicle_name,tc.sms FROM sitara.trip_close as tc inner join sitara.trip_sub as ts on tc.sub_id=ts.id where tc.sms='0';";
$result = mysqli_query($db, $sql);
$count = mysqli_num_rows($result);
echo $count;
if ($count > 0)
{
    while ($row = mysqli_fetch_array($result))
    {
        // $userid = $row['id'];
        $table_id = $row['table_id'];
        $sub_id = $row['id'];
        $status = $row['sms'];
        $number = $row['consignee_contact_1'];
        $number2 = $row['consignee_contact_2'];
        $number3 = $row['consignee_contact_3'];
        $consignee_name = $row['consignee_name'];
        $vehicle_name = $row['vehicle_name'];

        $contact_no = array();

        if ($number != '' && $number2 != '' && $number3 != '')
        {
            array_push($contact_no, $number, $number2, $number3);

        }
        else if ($number != '' && $number2 != '' && $number3 == '')
        {
            array_push($contact_no, $number, $number2);

        }
        else if ($number != '' && $number3 != '' && $number2 == '')
        {
            array_push($contact_no, $number, $number3);

        }
        else if ($number != '' && $number3 == '' && $number2 == '')
        {
            array_push($contact_no, $number);

        }

        print_r($contact_no);

        echo '<br/>';
        echo $sub_id;
        echo '<br/>';
        echo 'Status ==> ' . $status;
        echo '<br/>';
        echo $consignee_name;
        echo '<br/>';

        $response = '';

        if ($status == '0')
        {
            echo '</br>';
            
            $sql_update = "UPDATE `trip_close` SET `sms`='1' WHERE sub_id='$sub_id' and id='$table_id'";
            // echo $sql_update;
            if (mysqli_query($db, $sql_update))
            {
                echo '</br>';

                echo "Status were updated successfully.";
                foreach ($contact_no as $value)
                {
                    if ($value != '' && strlen($value) == 11)
                    {

                        echo "SMS Shoot consignee " . $value;
                        $msg = 'Dear ' . $consignee_name . ' ' . $vehicle_name . ' had reached at your Location With Your  Order. Your Order has been complete succesfully ';

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

                        curl_close($curl);
                        if ($response === 'Message Sent Successfully!')
                        {
                            echo '</br>';
                            echo $response . ' to ' . $value;
                            $date = date('Y-m-d H:i:s');
                            $sql_update = "INSERT INTO `sitara`.`trip_close_sms_log`(`sub_id`,`send_to`,`message`,`time`) VALUES ('$sub_id','$value','$msg','$date');";
                            // echo $sql_update;
                            if (mysqli_query($db, $sql_update))
                            {
                                echo '</br>';

                                echo "Log Insert successfully.";
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
                            echo '</br>';

                        }

                    }
                    else
                    {
                        echo '</br>';
                        echo 'Null Value ';
                        echo '</br>';
                    }

                }
            }
            else
            {
                echo '</br>';

                echo $sql_update;
                echo '</br>';

                echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
            }

           

        }

    }

}

else
{
    echo '<h1>No Records Found to send Msg</h1>';
}

?>
