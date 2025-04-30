<?php
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 3600; URL=$url1");

include ("config_sms.php");
$vehicle_name;
$todate = date("Y-m-d H:i:s", time());
$prev_date = date("Y-m-d H:i:s", strtotime($todate . ' -1 hour'));
$sql = "SELECT * FROM sitara.trip_start_sms_log where time>='$prev_date' and time<='$todate';";
$result = mysqli_query($db, $sql);
$count = mysqli_num_rows($result);
// $count = 101;
// echo $sql;
if ($count > 100)
{
    $number =  '03201232927';
    $number2 = '03137152168';
    $number3 = '03094441583';
    $number4 = '03423600000';

    $contact_no = array();

    if ($number != '' && $number2 != '' && $number3 != '')
    {
        array_push($contact_no, $number, $number2, $number3 ,$number4);

    }

    print_r($contact_no);

    foreach ($contact_no as $value) {
        if($value!='' && strlen($value)==11){
            echo '<br>';
            echo "SMS Shoot consignee " .$value.'<br>';
            $msg = $count.' send in last one hour please check your service';
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://connect.jazzcmt.com/sendsms_url.html?Username=03028652867&Password=Jazz@123&From=SITARA-LIVE&To='.$value.'&Message='.urlencode($msg),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            ));
    
            $response = curl_exec($curl);
            echo '</br>';
            echo $response .' to '.$value ;
            curl_close($curl);
           

        }
        else{
            echo '</br>';
                echo 'Null Value ';
                echo '</br>';
        }
        
    
    }
  



}

else
{
    echo '<h1>' . $count . ' SMS Send in last one hour</h1>';
}

?>
