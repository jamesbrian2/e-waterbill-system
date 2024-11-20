
<section id="sidebar">
    <a href="#" class="brand">
        <img src="img/water.png" alt="piglogo">
        <span class="text">Brgy. Panadtaran  <br>&nbsp;Water Works</span>
    </a>
    <ul class="side-menu top">
        <li>
            <a href="dashboard.php" class="active">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="history.php">
                <i class='bx bx-spreadsheet'></i>
                <span class="text">History</span>
            </a>
        </li>
        <li>
            <a href="" id="logoutlinks" class="logout">
            <i class='bx bx-log-out'></i>
                <span class="text">Logout</span>
            </a>
        </li>

    </ul>
</section>

<script>
    document.getElementById("logoutlinks").addEventListener("click", function(e) {
    e.preventDefault(); // prevent the default link click action
    var confirmAction = confirm("Are you sure you want to log out?");
    if (confirmAction) {
        // If user confirms logout, redirect to logout.php
        window.location.href = "logout.php";
    }
});
	
</script>