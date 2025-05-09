<?php
date_default_timezone_set('Asia/Karachi');

header('Content-Type: application/json');

// Allow requests from any origin (not recommended for production, use with caution)
header('Access-Control-Allow-Origin: *');

// You may want to allow other HTTP methods and headers as needed
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'pumasalesbridge');
$db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);


?>