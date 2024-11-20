<?php 

    $sidebarname = isset($_SESSION['sidebarname']) ? $_SESSION['sidebarname'] : 'Dashboard';
	 ?>


	 <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
	 
<nav>
    <div class="left-side">
        <i class='bx bx-menu'></i>
        <a href="#" class="nav-link"><?php  echo $sidebarname ?></a>
    </div>
    <div class="right-side">
    <button type="button" class="btn  text-primary fs-4 border-0 p-1 m-0">
        <i class="bx bx-bell w-100 h-100"></i>
    </button>

        
    </div>
</nav>

 

    