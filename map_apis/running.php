<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
ini_set('max_execution_time', -1);
	date_default_timezone_set("Asia/Karachi");
	$username="root";
	$password="Ptoptrack@(!!@";
	$database="omcs";
	$connect=mysqli_connect('localhost', $username, $password,$database);
	if (!$connect)
	{
	  die('Not connected : ' . mysqli_error());
	}

	// Set the active MySQL database
	$db_selected = mysqli_select_db( $connect,$database);
	if (!$db_selected)
	{
	  die ('Can\'t use db : ' . mysqli_error());
	}
	// include_once('../includes/connect_database.php'); 
	// include_once('../includes/variables.php');
	if(isset($_GET['accesskey'])) {
		$access_key_received = $_GET['accesskey'];
		$user = $_GET['user'];
		// $offset = $_GET['offset'];
		$access_key = "12345";
		
		if($access_key_received == $access_key){
			// get all category data from category table
			$sql_query = "SELECT * FROM devicesnew as pos join users_devices_new ud on pos.id = ud.devices_id where pos.speed > 0 and pos.speed < 50 and ud.users_id = $user and pos.ignition ='ON' and pos.time >=curdate() order by time desc";
			
			$result = $connect->query($sql_query) or die ("Error :".mysqli_error());
	 
			$users = array();
			while($user = $result->fetch_assoc()) {
				$users[] = $user;
			}
			
			// create json output
			$output = json_encode($users);
		}else{
			die('accesskey is incorrect.');
		}
	} else {
		die('accesskey is required.');
	}
 
	//Output the output.
	echo $output;

	// include_once('../includes/close_database.php'); 
?>