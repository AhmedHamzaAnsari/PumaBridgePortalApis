<?php
//fetch.php  
include("../config.php");

$access_key = '03201232927';
$pass = $_GET["key"];

if (!empty($pass)) {
    if ($pass == $access_key) {
        // $dealer_id = intval($_GET["dealer_id"]);
        // $tm_id = intval($_GET["tm_id"]);
        $region = $_GET["region"];
        $tm = $_GET["tm"] ?? '';

        $from = $db->real_escape_string($_GET["from"]);
        $to = $db->real_escape_string($_GET["to"]);
        $products = $db->real_escape_string($_GET["products"]);
        $product_val = "";
        if ($products != "") {
            $product_val = "AND pp.id='$products'";
        } else {
            $product_val = "";
        }

        // Initialize an array to store the data
        // and tr.recon_approval='1' and tr.approved_status='1';


        $formatted_data = [];
        $recon_dater = "SELECT GROUP_CONCAT(DISTINCT(rr.inspection_id)) AS task_ids 
        FROM survey_response_main as rr
        join inspector_task as it on it.id=rr.inspection_id
        left join inspector_task_response as tr on tr.task_id=it.id
        JOIN users as us on us.id = it.user_id
        WHERE date(rr.created_at) >= '$from' 
        AND date(rr.created_at) <= '$to'
        AND us.region='$region' and us.id IN($tm)";

        $result_recon_dater = mysqli_query($db, $recon_dater);

        if ($result_recon_dater) {
            $row_recon_dater = mysqli_fetch_assoc($result_recon_dater);
            $task_ids = $row_recon_dater['task_ids'];

            if (!empty($task_ids)) {
                // Query to fetch records
                $sql = "SELECT it.*, dl.name as dealer_name, dl.region, us.name as tm_name,dl.sap_no as dealer_sap
                FROM inspector_task as it
                JOIN dealers as dl on dl.id = it.dealer_id
                JOIN users as us on us.id = it.user_id
                WHERE us.region='$region'
                AND it.inspection=1
                and us.id IN($tm)
                AND it.id IN($task_ids) order by it.id desc;";

                $result = $db->query($sql);

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $id = $row["id"];
                        $dealer_sap = $row["dealer_sap"];

                        $name = $row["dealer_name"];
                        $terr = '';
                        $region = $row["region"];
                        $tm_name = $row["tm_name"];

                        $get_orders = "SELECT rs.*,sq.question,rf.file as cancel_file,cc.name as catogory_name,dl.name as dealer_name,dl.sap_no as dealers_sap,us.name as tm_name,dl.region,it.id as task_id
                        FROM survey_response as rs
                        join survey_category_questions as sq on sq.id=rs.question_id
                        join survey_category as cc on cc.id=rs.category_id
                        LEFT JOIN survey_response_files rf ON (rf.question_id = rs.question_id and rf.inspection_id=rs.inspection_id)
                        join inspector_task as it on it.id=rs.inspection_id
                        left join inspector_task_response as tr on tr.task_id=it.id
                        JOIN users as us on us.id = it.user_id
                        join dealers as dl on dl.id=rs.dealer_id
                        WHERE rs.inspection_id = $id and us.region='$region' and us.id IN($tm)";

                        $result_orders = $db->query($get_orders);

                        if ($result_orders) {
                            while ($row_2 = $result_orders->fetch_assoc()) {
                            
                                $task_id = $row_2['inspection_id'];
                                $category_id = $row_2['category_id'];
                                $question_id = $row_2['question_id'];
                                $response = $row_2['response'];
                                $comment = $row_2['comment'];
                                $dealer_id = $row_2['dealer_id'];
                                $created_at = $row_2['created_at'];
                                $created_by = $row_2['created_by'];
                                $question = $row_2['question'];
                                $cancel_file = $row_2['cancel_file'];
                                $catogory_name = $row_2['catogory_name'];
                                $dealer_name = $row_2['dealer_name'];
                                $dealers_sap = $row_2['dealers_sap'];
                                $tm_name = $row_2['tm_name'];
                                $region = $row_2['region'];
                                $task_id = $row_2['task_id'];

                                
                               
                                





                                $record_data = [
                                    'task_id' => $task_id,
                                    'created_at' => $created_at,
                                    'site' => $dealer_name,
                                    'dealer_sap' => $dealers_sap,
                                    'tm' => $tm_name,
                                    'region' => $region,
                                    'category_id' => $category_id,
                                    'question_id' => $question_id,
                                    'response' => $response,
                                    'comment' => $comment,
                                    'dealer_id' => $dealer_id,
                                    'question' => $question,
                                    'cancel_file' => $cancel_file,
                                    'catogory_name' =>$catogory_name



                                ];

                                // Append the record data to the formatted_data array
                                $formatted_data[] = $record_data;
                            }
                        } else {
                            echo "Error fetching stock recon data: " . $db->error;
                        }
                    }

                    // Convert the array to a JSON string
                    // $jsonData = json_encode($formatted_data);
                    header('Content-Type: application/json');

                    $formatted_data = utf8ize($formatted_data);
                    $json = json_encode($formatted_data, JSON_PRETTY_PRINT);

                    if ($json === false) {
                        echo json_encode(["error" => "JSON encoding failed", "details" => json_last_error_msg()]);
                    } else {
                        echo $json;
                    }



                    // Set the response header to indicate JSON content

                    // Output the JSON string
                    // echo $jsonData;

                } else {
                    echo "Error fetching inspector task data: " . $db->error;
                }
            } else {
                echo "No tasks found for the specified dealer and date range.";
            }
        } else {
            echo "Error executing the task ID query.";
        }
    } else {
        echo 'Wrong Key...';
    }
} else {
    echo 'Key is Required';
}

function utf8ize($data)
{
    if (is_array($data)) {
        return array_map('utf8ize', $data);
    } elseif (is_string($data)) {
        return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
    }
    return $data;
}
?>