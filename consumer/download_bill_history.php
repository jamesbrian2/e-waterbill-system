<?php
include 'includes/config.php';
require '../vendor/autoload.php'; // Adjust if not using Composer

use Dompdf\Dompdf;
use Dompdf\Options;
if (isset($_POST['print'])) {
    $id=$_POST['id'];
    $sql = "SELECT * from bill LEFT JOIN  consumers ON  bill.serial_number=consumers.serial_no
    WHERE consumers.user_id =  $id
    ORDER BY bill.reading_date";
    $employee = $dbh->prepare($sql);
    $employee->execute();
    $results = $employee->fetchAll(PDO::FETCH_OBJ);

$options = new Options();
$dompdf = new Dompdf($options);


$html = '
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    h1 {
        text-align: center;
        color: #4CAF50;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        font-family: Arial, sans-serif;
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }
    th {
        background-color: #4CAF50;
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    tr:hover {
        background-color: #ddd;
    }
</style>
<h1>Billing History</h1>
<table>
    <tr>
        <th>Consumption</th>
        <th>Total Amount</th>
        <th>Reading Date</th>
        <th>Due Date</th>
        <th>Status</th>
    </tr>';

foreach ($results as $row) {
    $reading = new DateTime($row->reading_date);
    $reading_date = $reading->format('F j, Y');
    
    $due = new DateTime($row->due_date);
    $due_date = $due->format('F j, Y');

    $html .= '<tr>
                <td>' . $row->consumption . 'm³</td>
                <td>' . number_format($row->total_amount, 2) . '</td>
                <td>' . $reading_date . '</td>
                <td>' . $due_date . '</td>
                <td>' . $row->payment . '</td>
              </tr>';
}


$html .= '</table>';

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$dompdf->stream('Bill History.pdf', ['Attachment' => true]);
exit;
}
?>
