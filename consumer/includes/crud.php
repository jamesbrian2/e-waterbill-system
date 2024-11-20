<?php 
// Update

// Employee 
if ($_POST['employeeId']) {
    $employeeId = $_POST['employeeId'];

    $sql = "SELECT e.*,e.id as id, u.username AS username, us.label AS label 
     FROM employee e LEFT JOIN users u ON u.id = e.user_id
      LEFT JOIN usertype us ON us.id = e.position  WHERE e.user_id = :employeeId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':employeeId', $employeeId, PDO::PARAM_STR);
    $query->execute();

    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        // Initialize the variable to store the HTML
        if ($result) {
            // Create an associative array with the pig data
            $employeeData = array(
                "id" => $result->id,
                "user_id" => $result->user_id,
                "position" => $result->position,
                "firstname" => $result->firstname,
                "middlename" => $result->middlename,
                "lastname" => $result->lastname,
                "mobile_number" => $result->mobile_number,
                "address" => $result->address,
                "picture" => $result->picture,
                "username" => $result->username,
                "label" => $result->label,

            );
        
            // Encode the array as a JSON object and output it
            echo json_encode($employeeData);
        } else {
            echo json_encode(array("error" => "No pig found with id $employeeId"));
        }
    }
}

 // Update
 
 if ($_POST['delete']) {
    
 }
// Delete
    ?>