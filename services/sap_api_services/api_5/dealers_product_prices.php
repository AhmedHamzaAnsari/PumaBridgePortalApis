<?php
//fetch.php  
include("../../../config.php");
ini_set('max_execution_time', '0');
$url1 = $_SERVER['REQUEST_URI'];
header("Refresh: 60; URL=$url1");
set_time_limit(5000); // 
// file_put_contents('reload_log.txt', 'Page reloaded at ' . date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
date_default_timezone_set('Asia/Karachi');

$access_key = '03201232927';

$pass = $_GET["key"];
echo $date = date('Y-m-d H:i:s');
echo '<br>';
if (date('H:i') == '02:00') {

    if ($pass != '') {
        if ($pass == $access_key) {



            $sql_query1 = "SELECT * FROM dealers where privilege='Dealer' and sap_no!='';";

            $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

            $thread = array();
            while ($user = $result1->fetch_assoc()) {

                $dealers_id = $user['id'];
                $sap = $user['sap_no'];

                // echo '<br>';

                $acount = $user['acount'];

                // echo 'http://151.106.17.246:8080/OMCS-CMS-APIS/services/get_dealer_ledger.php?sap='.$sap.'' . '<br>';

                $url = 'http://151.106.17.246:8080/OMCS-CMS-APIS/services/sap_api_services/api_5/api_5_data_check.php?sap=' . $sap . ''; // Replace '...' with the provided data string
                $data = file_get_contents($url);

                // Extracting the relevant information from the data string
                $startPos = strpos($data, '[');
                $endPos = strrpos($data, ']');

                $jsonData = substr($data, $startPos, $endPos - $startPos + 1);

                // Decoding the JSON string into a PHP array
                $arrayData = json_decode($jsonData, true);
                // print_r($arrayData);
                // Encoding the array as JSON for better formatting
                // $jsonFormatted = json_encode($arrayData, JSON_PRETTY_PRINT);

                // Output the formatted JSON
                // echo $jsonFormatted;
                if ($arrayData) {
                    foreach ($arrayData as $item) {
                        $MAT_DESCRIPTION = $item['MAT_DESCRIPTION'];
                        // echo '<br>';
                        $NET_VALUE = $item['NET_VALUE'];
                        $FREIGHT_VALUE = $item['FREIGHT_VALUE'];
                        $MATERIAL_ID = $item['MATERIAL_ID'];
                        $nozel_price = 0;



                        // Get the current date
                        $currentDate = date('d'); // Day of the month
                        $currentMonth = date('m'); // Current month
                        $currentYear = date('Y'); // Current year
                        $from_date = ''; // First day of the month
                        $to_date = '';
                        if ($currentDate <= 15) {
                            // If the current date is less than or equal to the 15th
                            $from_date = date('Y-m-01') . ' 00:00:00'; // First day of the month
                            $to_date = date('Y-m-15') . ' 23:59:59'; // 15th of the month
                        } else {
                            // If the current date is greater than or equal to the 16th
                            $from_date = date('Y-m-16') . ' 00:00:00'; // 16th of the month
                            $to_date = date('Y-m-t') . ' 23:59:59'; // Last day of the month
                        }


                        // $from_date = '2024-08-16 00:00:00';
                        // $to_date = '2024-08-31 23:59:59';
                        $sql_product = "SELECT * FROM all_products where sap_no='$MATERIAL_ID'";

                        $resultsql_product = $db->query($sql_product) or die("Error :" . mysqli_error($db));


                        while ($row_product = $resultsql_product->fetch_assoc()) {
                            $p_id = $row_product['id'];
                            $p_name = $row_product['name'];

                            $sql = "SELECT * FROM dealers_products where name='$p_name' and dealer_id='$dealers_id'";

                            // echo $sql;

                            $result = mysqli_query($db, $sql);
                            $row = mysqli_fetch_array($result);

                            $count = mysqli_num_rows($result);

                            if ($count > 0) {
                                $rows_id = $row['id'];

                                $query = "UPDATE `dealers_products`
                                SET 
                                `from` = '$from_date',
                                `to` = '$to_date',
                                `indent_price` = '$NET_VALUE',
                                `nozel_price` = '$nozel_price',
                                `update_time` = '$date',
                                `freight_value`= '$FREIGHT_VALUE',
                                `description` = '$MAT_DESCRIPTION'
                                WHERE `id` = $rows_id;";
                                if (mysqli_query($db, $query)) {

                                    $backlog = "INSERT INTO `dealer_nozel_price_log`
                                    (`dealer_id`,
                                    `product_id`,
                                    `indent_price`,
                                    `nozel_price`,
                                    `from`,
                                    `to`,
                                    `description`,
                                    `freight_value`,
                                    `created_at`,
                                    `created_by`)
                                    VALUES
                                    ('$dealers_id',
                                    '$rows_id',
                                    '$NET_VALUE',
                                    '$nozel_price',
                                    '$from_date',
                                    '$to_date',
                                    '$MAT_DESCRIPTION',
                                    '$FREIGHT_VALUE',
                                    '$date',
                                    '$dealers_id');";
                                    if (mysqli_query($db, $backlog)) {
                                        $output = 1;

                                    } else {

                                        $output = 'Error' . mysqli_error($db) . '<br>' . $backlog;
                                    }

                                } else {
                                    $output = 'Error' . mysqli_error($db) . '<br>' . $query;



                                }
                            } else {
                                $query = "INSERT INTO `dealers_products`
                                    (`dealer_id`,
                                    `name`,
                                    `from`,
                                    `to`,
                                    `indent_price`,
                                    `nozel_price`,
                                    `freight_value`,
                                    `description`,
                                    `created_at`,
                                    `update_time`,
    
                                    `created_by`)
                                    VALUES
                                    ('$dealers_id',
                                    '$p_name',
                                    '$from_date',
                                    '$to_date',
                                    '$NET_VALUE',
                                    '$nozel_price',
                                    '$FREIGHT_VALUE',
                                    '$MAT_DESCRIPTION',
                                    '$date',
                                    '$date',
                                    '$dealers_id');";
                                if (mysqli_query($db, $query)) {
                                    $lastInsertedId = mysqli_insert_id($db);


                                    $backlog = "INSERT INTO `dealer_nozel_price_log`
                                        (`dealer_id`,
                                        `product_id`,
                                        `indent_price`,
                                        `nozel_price`,
                                        `from`,
                                        `to`,
                                        `description`,
                                        `freight_value`,
                                        `created_at`,
                                        `created_by`)
                                        VALUES
                                        ('$dealers_id',
                                        '$lastInsertedId',
                                        '$NET_VALUE',
                                        '$nozel_price',
                                        '$from_date',
                                        '$to_date',
                                        '$MAT_DESCRIPTION',
                                        '$FREIGHT_VALUE',
                                        '$date',
                                        '$dealers_id');";
                                    if (mysqli_query($db, $backlog)) {
                                        $output = 1;

                                    } else {

                                        $output = 'Error' . mysqli_error($db) . '<br>' . $backlog;
                                    }


                                } else {
                                    $output = 'Error' . mysqli_error($db) . '<br>' . $query;

                                }
                            }

                        }




                    }





                }

                // echo $output;

            }


        } else {
            echo 'Wrong Key...';
        }

    } else {
        echo 'Key is Required';
    }
} else {
    echo 'Current TIme ' . date('H:i');
}

echo date('Y-m-d H:i:s');
?>