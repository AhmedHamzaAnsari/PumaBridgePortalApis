
<?php
error_reporting(0);
ini_set('memory_limit', '-1');
set_time_limit(500);

   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', 'Ptoptrack@(!!@');
   define('DB_DATABASE', 'sitara');
   $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);


//index.php
include('class/class.phpmailer.php');
include('pdf.php');
// $today = date("Y-m-d");
$email_arr = $_POST['email_arr'];
$from = $_POST['from'];
$to = $_POST['to'];

$today = $from . ' to ' . $to ;
$check = $_POST['check'];
$vehi_id = implode($check,',');

$vehicle_name = $_POST['vehicle_name'];
$vehi_name = implode($vehicle_name,' , ');


// $email=$_GET['email'];
// $report=$_GET['report'];






$message = '';

$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

function fetch_customer_data($connect)
{
	// $today = date("Y-m-d");vehicle_name
    $query;
    $check = $_POST['check'];
    $v_id = implode($check,',');

    $from = $_POST['from'];
    $to = $_POST['to'];

    $today = $from . ' to ' . $to ;

    
    // $email_r = implode($email,',');
    if($v_id==='all'){
	    $query = "SELECT dc.name,geo.consignee_name,geo.location,gca.in_time,gca.out_time,gca.in_duration,geo.Coordinates FROM sitara.geofenceing as geo join geo_check_audit as gca on gca.geo_id = geo.id join sitara.devices as dc on gca.veh_id=dc.uniqueId where geo.geotype='Black Spote' and gca.in_time >='$from'  and gca.in_time <='$to' order by gca.id desc;";

    }
    else{

        $query = "SELECT dc.name,geo.consignee_name,geo.location,gca.in_time,gca.out_time,gca.in_duration,geo.Coordinates FROM sitara.geofenceing as geo join geo_check_audit as gca on gca.geo_id = geo.id join sitara.devices as dc on gca.veh_id=dc.uniqueId where geo.geotype='Black Spote' and gca.in_time >='$from'  and gca.in_time <='$to' and dc.uniqueId IN ({$v_id}) order by gca.id desc;";
    }

    


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
                <th >Plate.No</th>
                <th >Consignee Name</th>
                <th >Location</th>
                <th >In Time</th>
                <th >Out Time</th>
                <th >Duration</th>
                <th >Cordinates</th>
			</tr>
	';
	foreach($result as $row)
	{
		$output .= '
			<tr>
			<td class="text-center">'.$row["name"].'</td>
			<td >'.$row["consignee_name"].'</td>
			<td >'.$row["location"].'</td>
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

// $sql__="SELECT * FROM `email_scedule`";
// $result__ = mysqli_query($db,$sql__);

// while( $row = mysqli_fetch_array($result__) ){
// 	// $userid = $row['id'];
// 	$report = $row['report'];
// 	$time = $row['time'];
// 	$email = $row['email'];
// 	echo "Hamza ".$time;

	// echo smtp_mailer('ahmedhamzaansari.99@gmail.com',$today);

    foreach ($email_arr as $value) {
        // echo "$value <br>";
	    echo smtp_mailer($value,$today,$vehi_name);

    }

	// $sql_update = "UPDATE `email_scedule` SET `status`='1' WHERE email='$email' and report='$report'";
    //     echo $sql_update;
    //     if(mysqli_query($db, $sql_update)){
    //         echo "Status were updated successfully.";
    //         // header("location: manageGroup.php");
    //     } else {
    //         echo $sql_update;
    //         echo "ERROR: Could not able to execute $sql_update. " . mysqli_error($db);
    //     }
	

// }
$myObj = new stdClass();
$myObj->status = 200;
$myObj->response = "success";


$myJSON = json_encode($myObj);

echo $myJSON;

function smtp_mailer($to,$time,$vehi_name){
	$connect = new PDO("mysql:host=localhost;dbname=sitara", "root", "Ptoptrack@(!!@");

	$file_name = md5(rand()) . '.pdf';
	$html_code = '<div class="container">
    <div class="row">
        <div class="col-md-12">
        <h2 style="font-weight: bold;    color: #3e3ea7;font-size: 72px;font-style: italic;font-weight: bold;text-decoration: underline">SITARA</h2>
        
        </div>
            <h6>Report Name : History Report</h6>
            <br/>
            <h6>Time : '.$time.'</h6>
            <br/>
            <h6>Vehicles : '.$vehi_name.'</h6>
        

        

    </div>
</div>';
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
	$mail->Subject = 'Black Spot Report '.$time;			//Sets the Subject of the message
	$mail->Body = '<h1>SITARA.<h1><h3>Please Find details of Black spot Report in attach PDF File.</h3>';				//An HTML or plain text message body
	if($mail->Send())								//Send an Email. Return true on success or false on error
	{
		echo $message = '<label class="text-success">Report Details has been send successfully...</label>';
	}
    else {
        echo ' Mail Not Send';
    }
	unlink($file_name);
}

?>






