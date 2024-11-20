<?php 
include('includes/config.php');
include 'sitio.php';
// Update




// reading history



if (isset($_POST['bill'])){  

    $bill = $_POST['bill'];
    $serial = $_POST['serials'];
    $consumer = $_POST['consumers'];

    function bill($dbh, $serial) {
        $sql = "SELECT MAX(reading_date) AS recent_reading,reading FROM `bill` WHERE serial_number = :serial";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['reading'] ?? null;
    }
    $recent_reading = bill($dbh, $serial);
    function billhistory($dbh, $bill) {
        $sql = "SELECT reading_date,consumption FROM `bill` WHERE id = :bill";
        $stmt1 = $dbh->prepare($sql);
        $stmt1->bindParam(':bill', $bill, PDO::PARAM_STR);
        $stmt1->execute();
        $results = $stmt1->fetch(PDO::FETCH_ASSOC);
        if($results){
            return[
                'reading_date' => $results['reading_date'],
                'consumption' => $results['consumption']
            ];
        }
            return null;
       
        }   
        $billhistory = billhistory($dbh, $bill);
    $sql = "SELECT * FROM consumers WHERE id = :consumer";
    $query = $dbh->prepare($sql);
    $query->bindParam(':consumer', $consumer, PDO::PARAM_INT);
    $query->execute();

    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        $middle_initial = !empty($result->middlename) ? substr($result->middlename, 0, 1) : '';
        $consumerdata = array(
            "id" => $result->id,
            "firstname" => $result->firstname,
            "middlename" => $result->middlename,
            "lastname" => $result->lastname,
            "middle_initial" => $middle_initial,
            "mobile_number" => $result->mobile_number,
            "previous" => $recent_reading, 
            "previouslabel" => $recent_reading . 'm³', 
            "sitio" => $result->sitio,
            "serial_number" => $result->serial_no,
            "bill_id" => $bill,
            "reading_date" => $billhistory['reading_date'],
            "consumption" => $billhistory['consumption'],
        );
        echo json_encode($consumerdata);
    } else {
          echo json_encode($response);
    }
}


if (isset($_POST['employeeId'])) {
    $employeeId = $_POST['employeeId'];

    $sql = "SELECT e.*, u.username as username FROM employee e
    LEFT JOIN users u  ON  e.user_id = u.id WHERE e.user_id = :employeeId";
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
                "dob" => $result->dob,
                "username" => $result->username,
                "picture" => $result->picture,
            );
        
            // Encode the array as a JSON object and output it
            echo json_encode($employeeData);
       
        } else {
            echo json_encode(array("error" => "No data found with employee $employeeId"));
        }
    }
}

// consumer 

if (isset($_POST['consumeraccount'])) {
    $consumer = $_POST['consumeraccount'];
    $sql = "SELECT * FROM consumers WHERE id = :consumer";
    $query = $dbh->prepare($sql);
    $query->bindParam(':consumer', $consumer, PDO::PARAM_INT);
    $query->execute();

    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        $consumerdata = array(
            "id" => $result->id,
            "firstname" => $result->firstname,
            "middlename" => $result->middlename,
            "lastname" => $result->lastname,
            "mobile_number" => $result->mobile_number,
            "sitio" => $result->sitio,
            "serial_number" => $result->serial_no,
            "dob" => $result->dob,
        );
        echo json_encode($consumerdata);
    } else {
        echo json_encode(array("error" => "No data found with Consumer ID $consumer"));
    }
}






if (isset($_POST['consumer']) && isset($_POST['serial'])) {

    $serial = $_POST['serial'];
    $consumer = $_POST['consumer'];

    function bills($dbh, $serial) {
        $sql = "SELECT MAX(reading_date) AS recent_reading,reading FROM `bill` WHERE serial_number = :serial";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['reading'] ?? null;
    }

    $recent_reading = bills($dbh, $serial);


    $sql = "SELECT * FROM consumers WHERE id = :consumer";
    $query = $dbh->prepare($sql);
    $query->bindParam(':consumer', $consumer, PDO::PARAM_INT);
    $query->execute();

    $result = $query->fetch(PDO::FETCH_OBJ);

    if ($result) {
        $middle_initial = !empty($result->middlename) ? substr($result->middlename, 0, 1) : '';
        $consumerdata = array(
            "id" => $result->id,
            "firstname" => $result->firstname,
            "middlename" => $result->middlename,
            "lastname" => $result->lastname,
            "middle_initial" => $middle_initial,
            "mobile_number" => $result->mobile_number,
            "previous" => $recent_reading, 
            "previouslabel" => $recent_reading . 'm³', 
            "sitio" => $result->sitio,
            "serial_number" => $result->serial_no,
        );
        echo json_encode($consumerdata);
    } else {
        echo json_encode(array("error" => "No data found with Consumer ID $consumer"));
    }
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sitio_id'])) {
    $id = $_POST['sitio_id'];
    echo getsitio($dbh, $id=$id );
}


 if (isset($_POST['delete'])) {
    
 }
// Delete
    ?>