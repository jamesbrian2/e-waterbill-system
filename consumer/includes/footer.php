<footer>
<div class="footer-bottom">
  <div class="con">
    <div class="row justify-content-center align-items-center">
      <div class="col-md-8 col-md-pull-8 text-center">
        <p class="copy-right text-white">
          <span class="logo">
            <img src="./img/water.png" alt="Logo" width="35px" height="35px">
          </span>
          <span class="line">|</span>
          Copyright <span class="text-white">&copy;</span> <?php echo date('Y');?>
          <a>Brgy.Panadtaran Water Works</a>. All rights reserved.
        </p>
      </div>
    </div>
  </div>
</div>
</footer>

   <!--Load Swal-->
   <?php if (isset($success)) { ?>
        <!--This code for injecting success alert-->
        <script>
            setTimeout(function() {
                    swal("Success", "<?php echo $success; ?>", "success");
                },
                100);
        </script>

    <?php }
    
  if (isset($error)) { ?>
      <!--This code for injecting success alert-->
      <script>
          setTimeout(function() {
                  swal("Error", "<?php echo $error; ?>", "error");
              },
              100);
      </script>

  <?php }

    
    
    ?>