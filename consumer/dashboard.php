<?php
include ('includes/config.php');
if(strlen($_SESSION['user'])==0)
{	
header('location:index2.php');
}
else{

    $current_year = date("Y");
    $_SESSION['sidebarname'] = 'Dashboard';
    $user = $_SESSION['user'];
    $dataPoints = [];
    
    $query = "SELECT MONTH(reading_date) AS month, SUM(consumption) AS total_consumption ,bill.total_amount  AS total_amount
    FROM bill
    LEFT JOIN consumers ON bill.serial_number = consumers.serial_no
    WHERE bill.status = 'billed' AND consumers.user_id = :user_id AND YEAR(reading_date) = :year
    GROUP BY MONTH(reading_date)
    ORDER BY month";
    $stmt = $dbh->prepare($query);
    $stmt->execute(['user_id' => $user, 'year' => $current_year]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        $monthName = DateTime::createFromFormat('!m', $row['month'])->format('F');
        $dataPoints[] = ["label" => $monthName, "y" => (int)$row['total_amount']];
    }
    

$labels = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

$consumption_prev_year = array_fill(0, 12, 0); 
$consumption_current_year = array_fill(0, 12, 0); 

function fetchConsumptionData($dbh,$year,$user) {
    $query = "
        SELECT MONTH(reading_date) AS month, SUM(consumption) AS total_consumption
        FROM bill
        LEFT JOIN consumers ON bill.serial_number = consumers.serial_no
        WHERE status = 'billed' AND YEAR(reading_date) = :year AND consumers.user_id = :users
        GROUP BY MONTH(reading_date)
        ORDER BY month;
    ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(['year' => $year ,'users' => $user ]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); 
}

$current_year = date("Y");
$previous_year = $current_year - 1;


$data_prev_year = fetchConsumptionData($dbh,  $previous_year,$user);
$data_current_year = fetchConsumptionData($dbh,  $current_year,$user);

foreach ($data_prev_year as $month => $total) {
    $consumption_prev_year[$month - 1] = (int)$total;
}
foreach ($data_current_year as $month => $total) {
    $consumption_current_year[$month - 1] = (int)$total; 
}


if(isset($_POST['sent'])){
    $id = htmlentities($_POST['id']);
    $name = htmlentities($_POST['name']);
    $email = htmlentities($_POST['email']);
    $subject = htmlentities($_POST['subject']);
    $message = htmlentities($_POST['message']);

    $query = $dbh->prepare("UPDATE messages SET status='sent' WHERE id = :id");
    $query->bindParam(':id', $id, PDO::PARAM_STR);

    $mail = new PHPMailer(true);
    try {
        $query->execute();
        
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'primebea11@gmail.com';
    $mail->Password = 'eudjclfackegutph';
    $mail->Port = 465;
    $mail->SMTPSecure = 'ssl';
    $mail->isHTML(true);
    $mail->setFrom('primebea11@gmail.com', $name);
    $mail->addAddress($email);
    $mail->Subject = $subject;
    $mail->Body = $message;
    $mail->send();

    $success = "Email Sent";
    header("refresh:1; url=dashboard.php");
  } catch (Exception $e) {
    echo $e->getMessage();
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    exit;
}
}

}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brgy.Panadtaran Water Works </title>
    	<!-- CSS -->
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="icon" type="image/x-icon" href="img/favicon.ico">
        <link href='../boxicons/css/boxicons.min.css' rel='stylesheet'>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<link rel="preconnect" href="https://fonts.googleapis.com">
<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</head>
<style>
 
   
        .content{
            width: 100%;
            position: relative;
            height:40vh; 
            margin:auto;
            overflow-x: auto; 
            padding:0;
        }
        .content canvas {
            width: 100% !important; 
            min-width: 700px;
            height: 100% !important; 
        }
        .title {
    padding-top:5px;
}

.title-icon {
    position: absolute;
    right: -3px;
    top: 35%;
    transform: translateY(-50%);
    font-size: 24px; 
}
.d-flex {
    position: relative; 
}
.left-dash{
    padding-right:5px !important;
}
    </style>
<body>
    	<!-- SIDEBAR -->
	<?php include('includes/sidebar.php');?>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<?php include('includes/header.php');?>
		<!-- NAVBAR -->
		<!-- MAIN -->
		<main>
		<div class="container mt-3 gx-1">
		<div class="row gy-4 p-0 g-3">
      <div class=" col-sm-5 p-0 left-dash">
        <div class="row g-2 ">
          <div class="col-6 col-md-6">
		<div class="rounded bg-white p-2  d-flex flex-column shadow-sm"> 
        <?php
$sql_current = "SELECT COUNT(id) as current_count FROM consumers WHERE warning = 'active' AND MONTH(registration_date) = MONTH(CURRENT_DATE())";
$query_current = $dbh->prepare($sql_current);
$query_current->execute();
$result_current = $query_current->fetch(PDO::FETCH_OBJ);
$current_count = $result_current->current_count;

$sql_previous = "SELECT COUNT(id) as previous_count FROM consumers WHERE warning = 'active' AND MONTH(registration_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)";
$query_previous = $dbh->prepare($sql_previous);
$query_previous->execute();
$result_previous = $query_previous->fetch(PDO::FETCH_OBJ);
$previous_count = $result_previous->previous_count;

if ($previous_count > 0) {
    $percent_change = (($current_count - $previous_count) / $previous_count) * 100;
} else {
    $percent_change = 0;
}
?>

            <div class="d-flex justify-content-between align-items-center mb-1">
                        <p class="title "> Consumers</p>
                        <i class='bx bx-user-circle ico rounded title-icon'></i>
                    </div><h5 class="mb-2"><?php echo number_format($current_count); ?></h5> 	
       
        <p class="pnl mb-1 "><i class='bx bx-<?php echo ($percent_change > 0) ? "trending-down" : "minus"; ?>'>
        <?php echo number_format(abs($percent_change), 2); ?>%</i><small>Last Month</small> </p>
  </div></div> 

          <div class="col-6">
			<div class="rounded bg-white p-2 d-flex flex-column shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-1">
                <p class="title"> Employees </p><i class='bx bx-hard-hat ico rounded title-icon'></i></div>
                <h5 class="mb-2">0000</h5>
                     	<p class="fw-light fs-6 mb-1 pnl "><br>
                    </p></div></div>
          <div class="col-6">
			<div class="rounded bg-white p-2 d-flex flex-column shadow-sm">
            <?php
$sql_current1 = "SELECT SUM(consumption) as consumption FROM bill WHERE  MONTH(reading_date) = MONTH(CURRENT_DATE())";
$query_current1 = $dbh->prepare($sql_current1);
$query_current1->execute();
$result_current1 = $query_current1->fetch(PDO::FETCH_OBJ);
$current_count1 = $result_current1->consumption;

$sql_previous1 = "SELECT SUM(consumption) as previousconsumption FROM bill WHERE MONTH(reading_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)";
$query_previous1 = $dbh->prepare($sql_previous1);
$query_previous1->execute();
$result_previous1 = $query_previous1->fetch(PDO::FETCH_OBJ);
$previous_count1 = $result_previous1->previousconsumption;

if ($previous_count1 > 0) {
    $percent_change1 = (($current_count1 - $previous_count1) / $previous_count1) * 100;
} else {
    $percent_change1 = 0;
}
?>

                <div class="d-flex justify-content-between align-items-center mb-1">
                    <p class="title">Overall Usage</p>
            <i class='bx bx-tachometer ico rounded title-icon'></i></div><h5><?php echo ($current_count1) ? $current_count1 : 0 ?>m³</h5> 
            	<p class="fw-light fs-6 mb-1 pnl "><i class='bx bx-<?php echo ($percent_change1 > 0) ? "trending-down" : "minus"; ?>'><?php echo number_format(abs($percent_change1), 2); ?>%</i><small>Last Month</small></p>
            </div></div>
          <div class="col-6">
			<div class="rounded bg-white p-2 d-flex flex-column shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-1">
                <?php
$sql_current2 = "SELECT COUNT(id) as overdue FROM bill WHERE MONTH(due_date) = MONTH(CURRENT_DATE())";
$query_current2 = $dbh->prepare($sql_current2);
$query_current2->execute();
$result_current2 = $query_current2->fetch(PDO::FETCH_OBJ);
$current_count2 = $result_current2->overdue;

$sql_previous2 = "SELECT COUNT(id) as previousoverdue FROM bill WHERE MONTH(due_date) <= MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)";
$query_previous2 = $dbh->prepare($sql_previous2);
$query_previous2->execute();
$result_previous2 = $query_previous2->fetch(PDO::FETCH_OBJ);
$previous_count2 = $result_previous2->previousoverdue;

if ($previous_count2 > 0) {
    $percent_change2 = (($current_count2 - $previous_count2) / $previous_count2) * 100;
} else {
    $percent_change2 = 0;
}
?>


                <p class="title">Overdue</p> 
                <i class='bx bx-chart ico rounded title-icon'></i></div><h5 class="mb-2"><?php echo number_format($percent_change2);?></php></php></h5> <p class="fw-light fs-6 mb-1 pnl">
                    	<i class='bx bx-<?php echo ($percent_change2 > 0) ? "trending-down" : "minus"; ?>'><?php echo number_format(abs($percent_change2), 2); ?>%</i><small>Last Month</small></p>
        </div> </div>
        </div>
      </div>

      <div class=" col-sm-7 bg-white  rounded p-3 shadow-sm">
        <p class="pb-2">Monthly Cost Breakdown</p>
      <div class="chart-container">
        <canvas id="myChart" ></canvas>
    </div>

      </div>
    </div>

</div>

<div class="container mt-3 mb-3 second">
  <div class="row gy-4 p-0">
    <div class="col-sm-8 ">
    <div class=" shadow-sm rounded bg-white  p-3">
    <p>Monthly Consumption</p>
    <div class="content">
        <canvas id="myLineChart"></canvas>
    </div></div></div>

    <div class="col-sm-4 shadow-sm  p-0 bg-white" id="queries">
         <div class=" mt-2 p-0 d-flex align-items-start">
            <p class="ps-3 mb-auto p-1">Pending Payments</p>
    </div>
<div class="chats">
<?php $sql ="SELECT *, MONTH(bill.reading_date) AS month FROM bill 
LEFT JOIN consumers ON consumers.serial_no = bill.serial_number
 WHERE bill.payment= 'pending' and consumers.user_id = :user_id ";
$query3 = $dbh->prepare($sql);
$query3->execute(['user_id' => $user]);
$messages=$query3->fetchAll(PDO::FETCH_OBJ);

if ($messages) {
foreach($messages as $result){
    $monthName = DateTime::createFromFormat('!m', $result->month)->format('F');
	$date = new DateTime($result->due_date);
	$formatteddate = $date->format('F j, Y');

?>

        <div class="row m-2 border rounded mb-1">
         <div class="col-10 p-1 border-end ">
            <div class="d-flex justify-content-between">
            <p class="mb-0 p-1 "><?php echo $monthName ?></p>
            <p class="mb-0 p-1 date">Due: <?php echo $formatteddate ?></p>
            </div>
            <p class="mb-0 text-start p-1 truncated-text">₱<?php echo htmlentities($result->total_amount); ?></p>
         </div>
         <div class="col-2 p-0">
         <button type="button" class="border h-100 w-100 d-flex justify-content-center align-items-center" title="Reply Message" data-bs-toggle="modal" data-bs-target="#replymodal-<?php echo htmlentities($result->id); ?>" disabled>
            <i class="bx bx-error text-warning fs-5"></i>
</button>
         </div>
</div>

<!-- 
 <div class="modal fade" id="replymodal-<?php echo htmlentities($result->id); ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header custom-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Send Email</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
	  <div class="modal-body">
				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">  
				<div class="mb-3">
  <label for="name" class="form-label">Name</label>
  <input type="text" class="form-control" name="name" id="name" value="Brgy.Panadtaran Water Works" autocomplete="off" required>
</div>
				<div class="mb-3">
  <label for="email" class="form-label">Email address</label>
  <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlentities($result->email); ?>" autocomplete="off" required >
</div>
<div class="mb-3">
  <label for="subject" class="form-label">Subject</label>
  <input type="text" class="form-control" name="subject" id="subject" value="Response to Your Inquiry,<?php echo htmlentities($result->Fullname); ?>" autocomplete="off" required >
</div>
<div class="mb-3">
  <label for="from" class="form-label">Message</label>
  <textarea class="form-control" name="from"  id="from" rows="2"><?php echo htmlentities($result->message); ?></textarea>
</div>
<div class="mb-3">
  <label for="message" class="form-label">Response</label>
  <textarea class="form-control" name="message" placeholder="Type your response here..." id="message" rows="3" required></textarea>
</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
					<input type="hidden" name="id" value="<?php echo htmlentities($result->id); ?>">
                    <button type="submit" name="sent" class="btn btn-primary">Confirm</button>
</form>
                </div>
  </div>
                </div>

				</div> -->
<!-- send Modal -->
<?php 
} 
}else{
?>
    <div class="row m-2  mb-1">
            <div class="d-flex justify-content-center">
                <p class="mb-0 p-1 date">Empty :(</p>
         
        </div>
    </div>
<?php
}
?>
</div>



  </div>
    </div>
</div>

</main>
<?php include('includes/footer.php');?>
        </section>
</body>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Bar Chart
        const barDataPoints = <?php echo json_encode($dataPoints); ?>;
        const barCtx = document.getElementById('myChart').getContext('2d');

        const barData = {
            labels: barDataPoints.map(dp => dp.label),
            datasets: [{
                data: barDataPoints.map(dp => dp.y),
                backgroundColor: 'rgba(222, 244, 252, 1)',
                borderColor: 'rgba(13, 151, 188, 1)',
                borderWidth: 1,
            }]
        };

        const barConfig = {
    type: 'bar',
    data: barData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString(); 
                    },
                    font: {
                        size: 10,
                        family: 'Arial',
                        style: 'italic',
                        weight: 'bold',
                        lineHeight: 0.2
                    }
                },
                grid: {
                    display: false
                }
            },
            x: {
                barPercentage: 0.5,
                categoryPercentage: 0.5,
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 9,
                        weight: 'bold'
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return '₱' + context.raw.toLocaleString(); 
                    }
                },
                bodyFont: {
                    size: 14,
                    weight: 'bold'
                }
            }
        }
    }
};

        new Chart(barCtx, barConfig);

        // Line Chart
        const lineLabels = <?php echo json_encode($labels); ?>;
        const lineDataPoints = <?php echo json_encode($consumption_current_year); ?>;
        const lineDataPoints2024 = <?php echo json_encode($consumption_prev_year); ?>;
        const lineCtx = document.getElementById('myLineChart').getContext('2d');

        const lineData = {
            labels: lineLabels,
            datasets: [{
                label: 'Current Year',
                data: lineDataPoints,
                fill: false,
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.1,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                pointBackgroundColor: 'rgba(13, 151, 188, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(75, 192, 192, 1)'
            },
            {
                    label: 'Previous Year',
                    data: lineDataPoints2024,
                    fill: false,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    tension: 0.1,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(255, 99, 132, 1)'
                }
        ]
        };

        const lineConfig = {
            type: 'line',
            data: lineData,
            options: {
                responsive: false,
                scales: {
                    x: {

                        beginAtZero: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10,
                                weight: 'bold',
                            }
                        }
                    },
                        
                      y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        },
                        ticks: {
                            callback: function(value) {
                        return  value.toLocaleString() + 'm³'; 
                    },
                            font: {
                                size: 10,
                                weight: 'bold',
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true // Use point style for legend
                        }
                    },
                    tooltip: {
                callbacks: {
                    label: function(context) {
                        return  context.raw.toLocaleString()+ 'm³'; 
                    }
                },
                bodyFont: {
                    size: 14,
                    weight: 'bold'
                }
            }
                }
            }
        };

        new Chart(lineCtx, lineConfig);
    });
</script>
    <script src="script.js"> </script>
</html>