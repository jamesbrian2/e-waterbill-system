<?php
// Redirect to login page if usertype is not set
if (!isset($_SESSION['usertype'])) {
    header("Location: index2.php");
    exit();
}
$usertype = $_SESSION['usertype'];
?>

<section id="sidebar">
    <a href="#" class="brand">
        <img src="img/water.png" alt="logo">
        <span class="text">Brgy. Panadtaran <br>&nbsp;Water Works</span>
    </a>

    <ul class="side-menu top">
        <!-- Dashboard -->
        <li class="<?= in_array($usertype, ['admin', 'Encoder']) ? '' : 'd-none'; ?>">
            <a href="dashboard.php" class="active">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>

        <!-- Reading -->
        <li class="<?= $usertype == 'Reader' ? '' : 'd-none'; ?>">
            <a href="reading.php">
                <i class='bx bx-spreadsheet'></i>
                <span class="text">Reading</span>
            </a>
        </li>

        <li class="<?= $usertype == 'admin' ? '' : 'd-none'; ?>">
            <a href="readinghistory.php">
                <i class='bx bx-spreadsheet'></i>
                <span class="text">Reading History</span>
            </a>
        </li>
   
   

        <!-- Payment -->
        <li class="<?= $usertype == 'Encoder'? '' : 'd-none'; ?>">
            <a href="readinglists.php">
                <i class='bx bx-list-ul'></i>
                <span class="text">Payment</span>
            </a>
        </li>

        <li class="<?= $usertype == 'admin' ? '' : 'd-none'; ?>">
            <a href="paymenthistory.php">
                <i class='bx bx-list-ul'></i>
                <span class="text">Payment History</span>
            </a>
        </li>

        <!-- Employees -->
        <li class="<?= $usertype == 'admin' ? '' : 'd-none'; ?>">
            <a href="employee.php">
                <i class='bx bx-group'></i>
                <span class="text">Employees</span>
            </a>
        </li>

        <!-- Consumers -->
        <li class="<?= in_array($usertype, ['admin', 'Encoder']) ? '' : 'd-none'; ?>">
            <a href="consumers.php">
                <i class='bx bx-universal-access'></i>
                <span class="text">Consumers</span>
            </a>
        </li>

        <!-- Overdue & Warnings  -->
        <li class="<?= in_array($usertype, ['admin', 'Encoder']) ? '' : 'd-none'; ?>">
            <a href="overdue.php">
                <i class='bx bx-alarm-exclamation'></i>
                <span class="text">Overdue & Warnings</span>
            </a>
        </li>

        <!-- Terminated Consumers  -->
        <li class="<?= in_array($usertype, ['admin', 'Encoder']) ? '' : 'd-none'; ?>">
            <a href="terminated.php">
                <i class='bx bx-block'></i>
                <span class="text">Terminated Consumers</span>
            </a>
        </li>

        <!-- Maintenance/Repairs  repairs.php-->
        <!-- <li class="<?= in_array($usertype, ['admin', 'Encoder']) ? '' : 'd-none'; ?>">
            <a href="">
                <i class='bx bx-wrench'></i>
                <span class="text">Maintenance/Repairs</span>
            </a>
        </li> -->
    </ul>

    <ul class="side-menu">
        <!-- Manage Page  -->
        <li class="<?= $usertype == 'admin' ? '' : 'd-none'; ?>">
            <a href="managepage.php">
                <i class='bx bxs-cog'></i>
                <span class="text">Manage Page</span>
            </a>
        </li>

        <!-- Logout -->
        <li>
            <a href="" id="logoutlink" class="logout">
                <i class='bx bx-log-out'></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>

<script>
    document.getElementById("logoutlink").addEventListener("click", function(e) {
        e.preventDefault(); 
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "logout.php";
        }
    });
</script>
