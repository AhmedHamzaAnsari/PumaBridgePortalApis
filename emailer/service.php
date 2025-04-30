
<?php
ini_set('max_execution_time', '0');
    $url1=$_SERVER['REQUEST_URI'];
    header("Refresh: 20; URL=$url1");
?>
<?php
// header("Refresh: 5;");
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Ptoptrack@(!!@');
define('DB_DATABASE', 'sitara');
$db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
$today = date("Y-m-d");
//index.php
include('class/class.phpmailer.php');
include('pdf.php');

$diff;
$report;
$sql__='SELECT es.id,es.report,es.time,es.email,es.user_id,us.privilege,TIME_FORMAT(TIMEDIFF(CURTIME(),time), "%T") as diff FROM sitara.email_scedule as es inner join sitara.users as us on us.id=es.user_id where es.status="0";';
$result__ = mysqli_query($db,$sql__);



while( $row = mysqli_fetch_array($result__) ){
    // $userid = $row['id'];
    $date = date('H:i');
	$report = $row['report'];
	$time = $row['time'] ;
	$email = $row['email'];
	$diff = $row['diff'] ;
	$user_id = $row['user_id'] ;
	$privilege = $row['privilege'] ;
    // echo " <br/>";
	// echo "service ".$time .'<br/>';
    // echo 'System '.$date .'<br/>';
    // echo " " . $email .'<br/>';
    // echo "diff " . $email .'<br/>';
    
    // $time1 = strtotime($time);
    // $time2 = strtotime($date);
    // $difference = round(abs($time2 - $time1) / 3600,2);
    // echo 'Check '. $difference;
    // $timesplit2=explode('.',$difference);
    // $min2=($timesplit2[0]*60)+($timesplit2[1]);
    
    // echo '<br/>';
    // echo $min2;

    $d1 = strtotime($time);
$d2 = strtotime($date);

$totalSecondsDiff = abs($d1-$d2); //42600225
$totalMinutesDiff = $totalSecondsDiff/60; 
echo '<br/>';

echo '-----------------------------------------------------------------------';
echo '<br/>';

echo 'Cakculated Diff Time => ';

    echo $totalMinutesDiff;
    echo '<br/>';
    
    $sign;
    echo $diff .'</br>';
    $timee=$diff;
    
    $minus = $timee[0];
    echo '<br/>';
    if($minus=='-'){
        $sign='-';
        echo $sign;
        

    }else{
        $sign='';

        echo $sign ;

    }
    $timesplit=explode(':',$timee);
    
    $min=($timesplit[0]*60)+($timesplit[1])+($timesplit[2]>30?1:0);
    // $min = $sign.$min;
    $min = $min;
    echo '<br/>';
    echo ' Min of '.$email.' ==> '.$min .'min report => '.$report.'</br>';
    if($report=='Black_spot'){
        $timee = ($timesplit[0]).":".($timesplit[1]);   
        echo $timee;
    
        if ($timee==='00:00' || $timee==='00:01' || $timee==='00:02' || $timee==='00:03' || $timee==='00:04' || $timee==='00:05' || $timee==='00:06' || $timee==='00:07' || $timee==='00:07' || $timee==='00:08' || $timee==='00:09' || $timee==='00:10' || $timee==='00:11' || $timee==='00:12' || $timee==='00:13' || $timee==='00:14' || $timee==='00:15') {
            echo " Time To Shoot Mail ";
                //echo "<script>window.open('black_spot.php','_blank');</script>";
                // header("Location: black_spot.php");
                $curl = curl_init();
        
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://151.106.17.246:8080/sitara_schedule_email/black_spot.php?email='.$email.'&report='.$report.'&user_id='.$user_id.'&privilege='.$privilege,
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
                echo $response;
        
                
            
        }
        else{
            echo "Time Available mail Shoot After ".$min. " Mnutes";
            
        }        
    }
    // || $min>=0 && $min<=30
    if($report=='Speed_violation'){

        $timee = ($timesplit[0]).":".($timesplit[1]);   
        echo $timee;
    
        if ($timee==='00:00' || $timee==='00:01' || $timee==='00:02' || $timee==='00:03' || $timee==='00:04' || $timee==='00:05' || $timee==='00:06' || $timee==='00:07' || $timee==='00:07' || $timee==='00:08' || $timee==='00:09' || $timee==='00:10' || $timee==='00:11' || $timee==='00:12' || $timee==='00:13' || $timee==='00:14' || $timee==='00:15' || $timee==='00:16' || $timee==='00:17' || $timee==='00:18' || $timee==='00:19' || $timee==='00:20') {
            echo " Time To Shoot Mail ";
                //echo "<script>window.open('black_spot.php','_blank');</script>";
                // header("Location: black_spot.php");
                $curl = curl_init();
        
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://151.106.17.246:8080/sitara_schedule_email/index.php?email='.$email.'&report='.$report.'&user_id='.$user_id.'&privilege='.$privilege,
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
                echo $response;
        
                
            
        }
        
        // else if ($min>=10) {
        //     echo " Time To Shoot Mail ";
        //         //echo "<script>window.open('black_spot.php','_blank');</script>";
        //         // header("Location: black_spot.php");
        //         $curl = curl_init();
        
        //         curl_setopt_array($curl, array(
        //         CURLOPT_URL => 'http://151.106.17.246:8080/sitara_schedule_email/index.php?email='.$email.'&report='.$report,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'GET',
        //         ));
        
        //         $response = curl_exec($curl);
        
        //         curl_close($curl);
        //         echo $response;
        
                
            
        // }
        // else if($min>10){
        //     echo " Time over ... " . $min;
        // }
        else{
            echo "Time Available mail Shoot After ".$min. " Mnutes";
           
        }        
    }

    if($report=='night_voilation'){

        $timee = ($timesplit[0]).":".($timesplit[1]);   
        echo $timee;
    
        if ($timee==='00:00' || $timee==='00:01' || $timee==='00:02' || $timee==='00:03' || $timee==='00:04' || $timee==='00:05' || $timee==='00:06' || $timee==='00:07' || $timee==='00:07' || $timee==='00:08' || $timee==='00:09' || $timee==='00:10' || $timee==='00:11' || $timee==='00:12' || $timee==='00:13' || $timee==='00:14' || $timee==='00:15' || $timee==='00:16' || $timee==='00:17' || $timee==='00:18' || $timee==='00:19' || $timee==='00:20') {
            echo " Time To Shoot Mail ";
                $curl = curl_init();
        
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://151.106.17.246:8080/sitara_schedule_email/night_violation.php?email='.$email.'&report='.$report.'&user_id='.$user_id.'&privilege='.$privilege,
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
                echo $response;
        
                
            
        }
        
        // else if ($min>=10) {
        //     echo " Time To Shoot Mail ";
        //         //echo "<script>window.open('black_spot.php','_blank');</script>";
        //         // header("Location: black_spot.php");
        //         $curl = curl_init();
        
        //         curl_setopt_array($curl, array(
        //         CURLOPT_URL => 'http://151.106.17.246:8080/sitara_schedule_email/index.php?email='.$email.'&report='.$report,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'GET',
        //         ));
        
        //         $response = curl_exec($curl);
        
        //         curl_close($curl);
        //         echo $response;
        
                
            
        // }
        // else if($min>10){
        //     echo " Time over ... " . $min;
        // }
        else{
            
            echo "Time Available mail Shoot After ".$min. " Mnutes";
           
        }        
    }

    if($report=='nr'){
        $timee = ($timesplit[0]).":".($timesplit[1]);   
        echo $timee;
    
        if ($timee==='00:00' || $timee==='00:01' || $timee==='00:02' || $timee==='00:03' || $timee==='00:04' || $timee==='00:05' || $timee==='00:06' || $timee==='00:07' || $timee==='00:07' || $timee==='00:08' || $timee==='00:09' || $timee==='00:10' || $timee==='00:11' || $timee==='00:12' || $timee==='00:13' || $timee==='00:14' || $timee==='00:15') {
            echo " Time To Shoot Mail ";
                //echo "<script>window.open('black_spot.php','_blank');</script>";
                // header("Location: black_spot.php");
                $curl = curl_init();
        
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://151.106.17.246:8080/sitara_schedule_email/sche_nr.php?email='.$email.'&report='.$report.'&user_id='.$user_id.'&privilege='.$privilege,
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
                echo $response;
        
                
            
        }
        else{
            echo "Time Available mail Shoot After ".$min. " Mnutes";
           
        }        
    }

    if($report=='current_location'){
        $timee = ($timesplit[0]).":".($timesplit[1]);   
        echo $timee;
    
        if ($timee==='00:00' || $timee==='00:01' || $timee==='00:02' || $timee==='00:03' || $timee==='00:04' || $timee==='00:05' || $timee==='00:06' || $timee==='00:07' || $timee==='00:07' || $timee==='00:08' || $timee==='00:09' || $timee==='00:10' || $timee==='00:11' || $timee==='00:12' || $timee==='00:13' || $timee==='00:14' || $timee==='00:15' ) {
            echo " Time To Shoot Mail ";
                //echo "<script>window.open('black_spot.php','_blank');</script>";
                // header("Location: black_spot.php");
                $curl = curl_init();
        
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://151.106.17.246:8080/sitara_schedule_email/sche_current_report.php?email='.$email.'&report='.$report.'&user_id='.$user_id.'&privilege='.$privilege,
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
                echo $response;
        
                
            
        }
        else{
            echo "Time Available mail Shoot After ".$min. " Mnutes";
           
        }        
    }

    if($report=='sms_report'){
        $timee = ($timesplit[0]).":".($timesplit[1]);   
        echo $timee;
    
        if ($timee==='00:00' || $timee==='00:01' || $timee==='00:02' || $timee==='00:03' || $timee==='00:04' || $timee==='00:05' || $timee==='00:06' || $timee==='00:07' || $timee==='00:07' || $timee==='00:08' || $timee==='00:09' || $timee==='00:10' || $timee==='00:11' || $timee==='00:12' || $timee==='00:13' || $timee==='00:14' || $timee==='00:15' ) {
            echo " Time To Shoot Mail ";
                //echo "<script>window.open('black_spot.php','_blank');</script>";
                // header("Location: black_spot.php");
                $curl = curl_init();
        
                curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://151.106.17.246:8080/sitara_schedule_email/sms_log_report.php?email='.$email.'&report='.$report,
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
                echo $response;
        
                
            
        }
        else{
            echo "Time Available mail Shoot After ".$min. " Mnutes";
           
        }        
    }
	

}



?>

<?php
// $checkTime = strtotime('16:23');
// echo 'Check Time : '.date('H:i', $checkTime);
// echo '<hr>';

// $loginTime = strtotime('16:41');
// $diff = $checkTime - $loginTime;
// echo 'Login Time : '.date('H:i', $loginTime).'<br>';
// echo ($diff < 0)? 'Late!' : 'Right time!'; echo '<br>';
// echo 'Time diff in sec: '.abs($diff);

// echo '<hr>';


?>