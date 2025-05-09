<?php
include("../config.php");
session_start();

if (isset($_POST['checkboxValue']) && isset($_POST['id'])) {
    // Sanitize inputs
    $checkboxValue = mysqli_real_escape_string($db, $_POST['checkboxValue']);
    $id = mysqli_real_escape_string($db, $_POST['id']);
    $user_id = $_POST['user_id'];
    $dealer_id = $_POST['dealer_id'];
    // Update the status in the correct table (dealers_lorries)
    $query = "UPDATE dealers_lorries SET status = '$checkboxValue' WHERE id = '$id'";

    if (mysqli_query($db, $query)) {
     logSystemActivity($db, $user_id, 'UPDATED dealer lorry status', 'dealers_lorries', $id, '', $checkboxValue);

        // echo 1;
    } else {
        echo 0;
        // Optional for debugging: echo mysqli_error($db);
    }
// 2. Update Nozzles
$query_nozzel = "UPDATE dealers_nozzel 
SET status = $checkboxValue 
WHERE tank_id = $id AND dealer_id = $dealer_id";
if (!mysqli_query($db, $query_nozzel)) {
die("Error update dealers_nozzel: " . mysqli_error($db));
}

// Log nozzles update
$getnozzels = "SELECT id 
FROM dealers_nozzel 
WHERE tank_id = $id AND dealer_id = $dealer_id";
$noz_result = mysqli_query($db, $getnozzels);

while ($row = mysqli_fetch_assoc($noz_result)) {
    if (!empty($row['id'])) {
        // $disp_ids[] = intval($row['dispenser_id']);

        logSystemActivity($db, $user_id, 'UPDATED dealer nozzel status', 'dealers_nozzel', $row['id'], '', $checkboxValue);
    }
}

    

        // 3. Get all tank_ids linked via nozzles
        $disp_ids = [];
        $getTanks = "SELECT DISTINCT dispenser_id 
                     FROM dealers_nozzel 
                     WHERE tank_id = $id AND dealer_id = $dealer_id";
        $tankResult = mysqli_query($db, $getTanks);
        if (!$tankResult) {
            die("Error fetching tanks: " . mysqli_error($db));
        }
        // echo $tankResult->numrows();
        // exit;
        while ($row = mysqli_fetch_assoc($tankResult)) {
            if (!empty($row['dispenser_id'])) {
                $disp_ids[] = intval($row['dispenser_id']);
            }
        }

        // echo json_encode([$disp_ids]);
        // exit;
    
        // 4. Update Lorries (Tanks) if any found
        if (!empty($disp_ids)) {
            $tank_ids_str = implode(",", $disp_ids);
            $query_tanks = "UPDATE dealers_dispenser 
                            SET status = $checkboxValue 
                            WHERE id IN ($tank_ids_str) AND dealer_id = $dealer_id";
            if (!mysqli_query($db, $query_tanks)) {
                die("Error update dealers_lorries: " . mysqli_error($db));
            }
    
            // Log lorries (tanks) update
            foreach ($disp_ids as $tank_id) {
                logSystemActivity($db, $user_id, 'UPDATE Dealer Dispenser  status', 'dealers_dispenser', $tank_id, '', $checkboxValue);
            }
        }
    
        echo 1;


} else {
        echo 0;
}
function logSystemActivity($db, $user_id, $action, $resource, $resource_id, $old_value = '', $new_value = '') {
    $stmt = mysqli_prepare($db, "INSERT INTO system_logs (user_id, timestamp, action, resource, resource_id, old_value, new_value) 
                                 VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ississ", 
            $user_id,
            $action,
            $resource,
            $resource_id,
            $old_value,
            $new_value
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing system log statement: " . mysqli_error($db);
    }
}

?>