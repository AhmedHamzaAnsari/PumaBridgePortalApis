<?php
// error_reporting(0);
$user_id;	
    ini_set('memory_limit', '-1');
    set_time_limit(500);
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', 'Ptoptrack@(!!@');
   define('DB_DATABASE', 'sitara');
   $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);


   $today = date("Y-m-d");
    $email=$_GET['email'];
    $report=$_GET['report'];

		
	

   
//index.php
include('class/class.phpmailer.php');
include('pdf.php');


$message = '';

$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

function fetch_customer_data($connect)
{
   $today = date("Y-m-d");

	// $query = "SELECT dc.name,geo.consignee_name,geo.location,gca.in_time,gca.out_time,gca.in_duration,geo.Coordinates FROM sitara.geofenceing as geo join geo_check_audit as gca on gca.geo_id = geo.id join sitara.devices as dc on gca.veh_id=dc.uniqueId where geo.geotype='Black Spote' and gca.in_time >='$today';";
	$query = "SELECT dc.name,geo.consignee_name,geo.location,gca.in_time,gca.out_time,gca.in_duration,geo.Coordinates FROM sitara.geofenceing as geo join geo_check_audit as gca on gca.geo_id = geo.id join sitara.devices as dc on gca.veh_id=dc.uniqueId  join sitara.users_devices as docc on docc.devices_id=dc.uniqueId where docc.users_id='$user_id' and geo.geotype='Black Spote' and gca.in_time >='$today';";
    // echo $query;
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
				<th>Lorry Name</th>
				<th>Black Spot Name</th>
				<th>In time</th>
				<th>out time</th>
				<th>Duration</th>
				<th>Cordinates</th>
			</tr>
	';
	foreach($result as $row)
	{
		$output .= '
			<tr>
			<td class="text-center">'.$row["name"].'</td>
			<td >'.$row["consignee_name"].'</td>
			<td>'.$row["in_time"].'</td>
			<td>'.$row["out_time"].'</td>
			<td>'.round($row["in_duration"]).' Minutes</td>
            <td>'.$row["Coordinates"].'</td>
			</tr>
		';
	}
	$output .= '
		</table>
	</div>
	';
	return $output;
}

// $sql__="SELECT * FROM `email_scedule` where status='0'";
// $result__ = mysqli_query($db,$sql__);

// while( $row = mysqli_fetch_array($result__) ){
// 	// $userid = $row['id'];
// 	$report = $row['report'];
// 	$time = $row['time'];
// 	$email = $row['email'];
	 
	 smtp_mailer($email,$today);

     $sql_update = "UPDATE `email_scedule` SET `status`='1' WHERE email='$email' and report='$report'";
        echo $sql_update;
        if(mysqli_query($db, $sql_update)){
            echo "Status were updated successfully.";
            // header("location: manageGroup.php");
        } else {
            echo $sql_update;
            echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
        }
	

// }
$myObj = new stdClass();
$myObj->status = 200;
$myObj->response = "success";


$myJSON = json_encode($myObj);

echo $myJSON;


function smtp_mailer($to,$time){
	$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

	$file_name = md5(rand()) . '.pdf';
	$html_code = '<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">SITARA</h2>
        
        </div>
            <h6>Report Name : Black Spot</h6>
            <br/>
            <h6>Time : '.$time.'</h6>
        

        

    </div>
</div>';
	// $html_code = '<link rel="stylesheet" href="bootstrap.min.css">';
	$html_code .= fetch_customer_data($connect);
	$pdf = new Pdf();
	$pdf->load_html($html_code);
	$pdf->render();
	$file = $pdf->output();
	file_put_contents($file_name, $file);
	
	// require 'class/class.phpmailer.php';
	$mail = new PHPMailer(); 
	$mail->SMTPDebug  = 3;
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
	$mail->WordWrap = 50;							//Sets word wrapping on the body of the message to a given number of characters
	$mail->IsHTML(true);							//Sets message type to HTML				
	$mail->AddAttachment($file_name);     				//Adds an attachment from a path on the filesystem
	$mail->Subject = 'Black Stop Report '.$time;			//Sets the Subject of the message
	$mail->Body = '<h1>SITARA.<h1><h3>Please Find details report of Black Stop in attach PDF File.</h3>';				//An HTML or plain text message body
	if($mail->Send())								//Send an Email. Return true on success or false on error
	{
//         $myObj = new stdClass();
// $myObj->status = 200;
// $myObj->response = "success";


// $myJSON = json_encode($myObj);

// echo $myJSON;
		//echo $message = '<label class="text-success">Black Stop Details has been send successfully...</label>';
	}
	unlink($file_name);
}

?>