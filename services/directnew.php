<?php
// Initialize cURL session
ini_set('max_execution_time', -1);
$username = "root";
$password = "Ptoptrack@(!!@";
$database = "omcs";

// Opens a connection to a MySQL server
$connection = mysqli_connect('localhost', $username, $password, $database);
if (!$connection) {
	die('Not connected : ' . mysqli_error());
}

// Set the active MySQL database
$db_selected = mysqli_select_db($connection, $database);
if (!$db_selected) {
	die('Can\'t use db : ' . mysqli_error());
}
$ch = curl_init();

// Set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/OMCS-CMS-APIS/services/tpl.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL session and fetch response
$response = curl_exec($ch);
function clean($string)
{
	$string = str_replace('', '-', $string); // Replaces all spaces with hyphens.

	return preg_replace('/[^A-Za-z0-9]/', '', $string); // Removes special chars.
}

// Check for errors
if ($response === false) {
	"cURL Error: " . curl_error($ch);
} else {
	// $datatpl = file_get_contents($response);
	// $arraytpl = json_decode($datatpl, true);
	$data = json_decode($response, true);
	foreach ($data as $hascoltpl) {

		$imeitplnew = "tpl" . clean($hascoltpl["RegNo"]);
		$nametplnew = $hascoltpl["RegNo"];
		$lattplnew = $hascoltpl["Lat"];
		$lngtplnew = $hascoltpl["Long"];
		$angletplnew = $hascoltpl["Direction"];
		$speedtplnew = $hascoltpl["Speed"];
		$datetimetplnew = $hascoltpl["GpsDateTime"];
		$datebbtpl = date_create($datetimetplnew);
		$datetimetpl = date_format($datebbtpl, "Y-m-d H:i:s");
		$licensepntplnew = '112113114115';
		$odometertplnew = $hascoltpl["Odo"];
		$ignetiontplnew = $hascoltpl["Ignition"];
		$protocoltplnew = "TPL";
		$last_idletplnew = '000';
		$last_movetplnew = '000';
		$last_stoptplnew = $hascoltpl["Location"];

		$sqltplnew = "INSERT INTO bulkdatanew (id,imei,st_server,lat,lng,angle,speed,name,sim_number,odometer,list,protocol,last_idle,last_move,last_stop,status)
VALUES ('TPL','$imeitplnew','$datetimetpl','$lattplnew','$lngtplnew','$angletplnew','$speedtplnew','$nametplnew','$licensepntplnew','$odometertplnew','$ignetiontplnew','$protocoltplnew','$last_idletplnew','$last_movetplnew','$last_stoptplnew','0');";


		mysqli_query($connection, $sqltplnew);
	}
}

// Close cURL session
curl_close($ch);





$filemanteletix =
	"http://web.teletix.pk:1949/api/live/status?a=1&t=300032002C00310035002C0031003500350035002C004E004100";
$datateletix = file_get_contents($filemanteletix);
$arrayteletix = json_decode($datateletix, true);
$i = 0;
foreach ($arrayteletix as $rowteletix) {

	$vehicle_teletix = $rowteletix["VRN"];
	$LandMark_teletix = $rowteletix["Location"];
	$imei = "teletix" . clean($rowteletix["VRN"]);
	$time_server_xtr_tele = $rowteletix["GpsDateTime"];
	$time_server_teletix = str_replace("T", " ", $time_server_xtr_tele);
	$coordinates = $rowteletix["Point"];
	list($latitude, $longitude) = explode(",", $coordinates);
	$LAT_teletix = trim($latitude);
	$LON_teletix = trim($longitude);
	$Speed_teletix = $rowteletix["Speed"];
	$ign_teletix = $rowteletix["ACC"];

	$sql_teletix = "INSERT INTO bulkdatanew
(id,imei,st_server,lat,lng,angle,speed,name,sim_number,odometer,list,protocol,last_idle,last_move,last_stop,status)
VALUES
('teletix','$imei','$time_server_teletix','$LAT_teletix','$LON_teletix','360','$Speed_teletix','$vehicle_teletix','','3321','$ign_teletix','teletix','$time_server_teletix','$time_server_teletix','$LandMark_teletix','0');";
	mysqli_query($connection, $sql_teletix);
}

?>





<div class="progress">
	<div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"
		style="width:<?php echo $i; ?>%">
		<span class="sr-only">
			<?php ?>
		</span>
	</div>
</div>
</div>
<?php

if ($sqltplnew == true) {
	echo "<br> New record created successfully yeahoo TPL ";

} else {
	echo "Error: " . $sqltplnew . "<br>" . mysqli_error($connection);

}


$sql1 = mysqli_query($connection, "SELECT COUNT(*) as num FROM bulkdatanew");

$result = mysqli_fetch_assoc($sql1);
echo '<br>' . $result['num'];
$t_row = $result['num'];
mysqli_close($connection);
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="refresh" content="100">
	<title>Puma Data</title>
	<style>
		.progress {
			height: 3px !important;
			margin-bottom: 1px !important;
		}
	</style>
</head>

<body style="background: #fff;">
	<div class="col-md-8">

		<div class="col-md-12">
			<br>
			<?php echo "Successfully done" . "<br>";
			echo date("d-m-Y H:i:s", time()); ?>
		</div>
</body>

</html>