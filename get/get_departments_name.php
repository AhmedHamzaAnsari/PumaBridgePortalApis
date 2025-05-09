<?php
// get_department.php
include("../config.php");

$access_key = '03201232927';
$pass = $_GET["key"]; // Access key from the request

if ($pass == $access_key) {
    // Query to fetch departments
    $sql_query = "SELECT id, name FROM department ORDER BY name ASC";
    $result = $db->query($sql_query);

    if ($result->num_rows > 0) {
        $departments = array();
        while ($row = $result->fetch_assoc()) {
            $departments[] = array(
                'id' => $row['id'],
                'name' => $row['name']
            );
        }

        // Return the department data as JSON
        echo json_encode($departments);
    } else {
        echo json_encode(["error" => "No departments found"]);
    }
} else {
    echo json_encode(["error" => "Wrong Key"]);
}
?>
