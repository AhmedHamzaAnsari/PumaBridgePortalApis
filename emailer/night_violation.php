<?php
// error_reporting(0);
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'Ptoptrack@(!!@');
define('DB_DATABASE', 'sitara');
$db = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

//index.php
ini_set('memory_limit', '-1');
set_time_limit(500);

include ('class/class.phpmailer.php');
include ('pdf.php');
$today = date("Y-m-d");
$_to_today = date("Y-m-d H:i:s");
$dur_time = $today . ' 00:00:00 ' . ' - ' . $_to_today;
$email = $_GET['email'];
$report = $_GET['report'];
$user_id;
$u_name='';
$privilege = $_GET['privilege'];

if ($privilege == 'Cartraige')
{
    $user_id = $_GET['user_id'];
    $user_name = "SELECT name FROM sitara.users where id='$user_id';";

    $result__user_name = mysqli_query($db, $user_name);

   

    while ($row = mysqli_fetch_array($result__user_name))
    {
        $u_name = $row['name'];

    }

}
else
{
    $user_id = '1';

}

$curr_date = date('Y-m-d');
$next_date = new dateTime($curr_date);
$next_date->modify('-1 day');
$tommorrow = $next_date->format('Y-m-d');

$vehi_name = 'Vehicle ';

//=====================================================================================================================
$start_time;
$vehicle_name;
$pre_time = 0;
$final_time = 0;

$start_speed;
$next_speed = 0;
$pre_speed = 0;
$total_event;
$min_ = 0;
$max_ = 0;
$location;
$time_;
$lati;
$lngi;

$vehi;
// $if_vehi = implode($vehi,',');
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
	<table border = "1">
		<tr>
			<th>Date</th>
			<th>Vehicle #</th>
			<th>Moving Duration</th>
			
			
		</tr>
';
$if_vehi = 'all';
if ($if_vehi === 'all')
{
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');

    // $sql__devi="SELECT pos.device_id as uniqueId,pos.vehicle_id as name FROM sitara.positions as pos where speed>'55' and pos.time>'$curr_date 00:00:00' and pos.time<'$curr_date 15:00:00'  and power='1' group by pos.vehicle_id;";
    $sql__devi = "SELECT devices_id FROM sitara.users_devices where users_id='$user_id';";

    $result__devi = mysqli_query($db, $sql__devi);

    $vehi__id = array();

    while ($row = mysqli_fetch_array($result__devi))
    {
        $asset_id = $row['devices_id'];
        $vehi__id[] = $asset_id;

    }
    $vehi = $vehi__id;
}
else
{
    $vehi = $_POST['check'];

}

foreach ($vehi as $value)
{
    // echo "$value <br>";
    $curr_date = date('Y-m-d');
    $next_date = new dateTime($curr_date);
    $next_date->modify('-1 day');
    $tommorrow = $next_date->format('Y-m-d');
    $sql__ = "SELECT DATE_FORMAT(time,'%H:%i:%s') time,speed,power,imileage,vehicle_id FROM sitara.positions where device_id='$value' and time>'$curr_date 00:00:00' and time<'$curr_date 04:59:59'  order by time asc";
    $result__ = mysqli_query($db, $sql__);

    $number = mysqli_num_rows($result__);
    if ($number > 0)
    {

        $j = 1;

        $i = 0;
        $k = 0;
        $l = 0;
        while ($row = mysqli_fetch_array($result__))
        {

            $speed = $row['speed'];
            $time = $row['time'];
            $power = $row['power'];
            $imileage = $row['imileage'];
            $vehicle_id = $row['vehicle_id'];
            $vehicle_name = $vehicle_id;
            $start_speed = $speed;
            $start_time = $time;

            // -------------------------------------------------------------------------------------------------------------------------------
            if ($start_speed > '0' && $power == '1')
            {

                if ($i == 0)
                {
                    $pre_time = $time;
                }
                // echo $time . ' => ' . $speed . '<br>';
                $d1 = strtotime($start_time);
                $d2 = strtotime($pre_time);

                $totalSecondsDiff = abs($d1 - $d2); //42600225
                // echo $start_time . ' - ' . $pre_time . '<br>';
                $totalMinutesDiff = $totalSecondsDiff / 60;
                // echo 'Diff => ' . $totalMinutesDiff . '<br>';
                $final_time = $final_time + $totalMinutesDiff;
                // echo 'cal => ' . $final_time . '<br>';
                

                $pre_speed = $start_speed;
                $pre_time = $start_time;
                $i++;
                $pre_speed = $start_speed;
                $pre_time = $start_time;
            }
            else
            {
                // $pre_speed = $time;
                $pre_time = $start_time;
            }
            // ---------------------------------------------------------------------------------------------------------------------------------
            if ($start_speed == '0' && $power == '1')
            {
                $idel_start_time = $time;

                if ($k == 0)
                {
                    $idel_pre_time = $time;
                }
                // echo $time . ' => ' . $speed . '<br>';
                $idel_d1 = strtotime($idel_start_time);
                $idel_d2 = strtotime($idel_pre_time);

                $idel_totalSecondsDiff = abs($idel_d1 - $idel_d2); //42600225
                // echo $idel_start_time . ' - ' . $idel_pre_time . '<br>';
                $idel_totalMinutesDiff = $idel_totalSecondsDiff / 60;
                // echo 'Diff => ' . $idel_totalMinutesDiff . '<br>';
                $idel_final_time = $idel_final_time + $idel_totalMinutesDiff;
                // echo 'cal => ' . $idel_final_time . '<br>';
                $idel_pre_time = $idel_start_time;
                $k++;

            }
            else
            {
                // $pre_speed = $time;
                $idel_pre_time = $pre_time;
            }

            if ($j == $number)
            {
                $last_stop = $time;
            }
            else if ($j == 1)
            {
                $first_start = $time;

            }

            $j++;

        }

      
  
        $c_d1 = strtotime($first_start);
        $c_d2 = strtotime($last_stop);

        $c_totalSecondsDiff = abs($c_d2 - $c_d1);

        $c_totalMinutesDiff = $c_totalSecondsDiff / 60;
        
        

       

        if($final_time>0){
            $output .= '
                <tr>
                    <td class="text-center">' . $curr_date . '</td>
                    <td >' .$vehicle_name .'</td>
                    <td >' . convert_time($final_time * 60) . '</td>
                    
               
                </tr>
            ';
        }
        
        $final_time = 0;
        $idel_final_time = 0;
        $final_mile = 0;
        // $l=0;
        
    }

    


}

$output .= '
	</table>
</div>
';
// echo $output;



// echo $list;


//======================================================================================================================

echo smtp_mailer($email, $dur_time, $output, $vehi_name,$db,$u_name);



// }


function smtp_mailer($to, $time, $output, $vehi_name,$db,$u_name)
{
    $connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

    $file_name = md5(rand()) . '.pdf';
    $html_code = '<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">SITARA</h2>
        
        </div>
            <h6>Report Name : Night Violation</h6>
          
            <h6>Account Name : '.$u_name.'</h6>
           
            <h6>Time : ' . date('Y-m-d') . '</h6>
        

        

    </div>
</div>';
    $html_code .= $output;
    $pdf = new Pdf();
    $pdf->load_html($html_code);
    $pdf->render();
    $file = $pdf->output();
    file_put_contents($file_name, $file);

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
    $mail->Subject = 'Night Violation Report ' . date('Y-m-d'); //Sets the Subject of the message
    $mail->Body = '<h1>SITARA.<h1><h3>Please Find details report of Night Violation in attach PDF File.</h3>'; //An HTML or plain text message body
    if ($mail->Send()) //Send an Email. Return true on success or false on error
    
    {
        $email = $_GET['email'];
        $report = $_GET['report'];
        echo $message = '<label class="text-success">Customer Details has been send successfully...</label>';
        $sql_update = "UPDATE `email_scedule` SET `status`='1' WHERE email='$email' and report='$report'";
            // echo $sql_update;
            if (mysqli_query($db, $sql_update))
            {
                echo "Status were updated successfully.";
                // header("location: manageGroup.php");
                
            }
            else
            {
                echo $sql_update;
                echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
            }
    }
    else
    {
        echo 'Mail Not send';
    }
    unlink($file_name);
}


function convert_time($mili)
{

    $init = $mili;
    $hours = floor($init / 3600);
    $minutes = floor(($init / 60) % 60);
    $seconds = $init % 60;

    // echo "$hours:$minutes:$seconds";
    $con = $hours . 'h' . $minutes . 'm' . $seconds . 's';
    return $con;

}
?>
