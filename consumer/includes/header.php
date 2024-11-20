<?php 

    $sidebarname = isset($_SESSION['sidebarname']) ? $_SESSION['sidebarname'] : 'Dashboard';
	 ?>


	 <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
	 
<nav>
    <div class="left-side">
        <i class='bx bx-menu'></i>
        <a href="#" class="nav-link"><?php  echo $sidebarname ?></a>
    </div>
    <!-- <div class="right-side">
        <a href="#" class="profile">
            <img src="img/user.png" alt="human">
        </a>
    </div> -->
</nav>

 

    