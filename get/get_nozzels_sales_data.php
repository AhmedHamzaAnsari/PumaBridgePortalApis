<?php
//fetch.php  
include("../config.php");


$access_key = '03201232927';

$pass = $_GET["key"];
if ($pass != '') {
    $dealer_id = $_GET["dealer_id"];
    if ($pass == $access_key) {
        $sql_query1 = "SELECT 
        FORMAT(SUM(Amount), 2) AS amounts,  -- Format Amount with commas and 2 decimals
        FORMAT(SUM(Quantity), 2) AS sales, -- Format Sales with commas and 2 decimals
        NozzleNo,
        CURDATE() AS current_dates,
        FLOOR(1000000 + (RAND() * 9000000)) AS totalizer -- Generate a random 7-digit number
    FROM 
        dealers_nozzels_sales AS ns
    JOIN 
        dealers AS dl 
    ON 
        dl.sap_no = ns.dealers_sap
    WHERE 
        DATE(slipDateTime) = CURDATE() 
        AND dl.id = $dealer_id
    GROUP BY 
        NozzleNo;
    ";

        $result1 = $db->query($sql_query1) or die("Error :" . mysqli_error($db));

        $thread = array();
        while ($user = $result1->fetch_assoc()) {
            $thread[] = $user;
        }
        echo json_encode($thread);

    } else {
        echo 'Wrong Key...';
    }

} 
else 
{
    echo 'Key is Required';
}


?>