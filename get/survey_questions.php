<?php
include("../config.php");

$access_key = '03201232927';

$pass = $_GET["key"] ?? '';

if ($pass !== $access_key) {
    echo json_encode(["error" => "Wrong or missing key"]);
    exit;
}

// Fetch all survey questions
$sql = "SELECT * FROM survey_category_questions ORDER BY id DESC";
$result = mysqli_query($db, $sql);

$questions = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        // Convert comma-separated department IDs into names
        $dept_ids = explode(',', $row['departments']);
        $dept_names = [];

        foreach ($dept_ids as $dept_id) {
            $dept_id = trim($dept_id);
            if ($dept_id !== '') {
                $dept_query = mysqli_query($db, "SELECT name FROM department WHERE id = '$dept_id'");
                if ($dept_query && mysqli_num_rows($dept_query) > 0) {
                    $dept_row = mysqli_fetch_assoc($dept_query);
                    $dept_names[] = $dept_row['name'];
                }
            }
        }

        $row['dpt'] = implode(', ', $dept_names); // Replacing ID string with names
        $questions[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($questions);

} else {
    echo json_encode([]);
}
?>