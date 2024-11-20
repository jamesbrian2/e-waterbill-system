<?php
include 'admin/includes/config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve input data and sanitize them
    $userName = strip_tags(trim($_POST["name"]));
    $userEmail = filter_var(strip_tags(trim($_POST["email"])), FILTER_SANITIZE_EMAIL);
    $userMessage = strip_tags(trim($_POST["message"]));

    try {
        // Check if data is valid
        if (!empty($userName) && !empty($userEmail) && !empty($userMessage)) {
            // SQL query
            $sql = "INSERT INTO messages (Fullname, email, message) VALUES (:user_name, :user_email, :user_message)";

            // Prepare statement
            $stmt = $dbh->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':user_name', $userName);
            $stmt->bindParam(':user_email', $userEmail);
            $stmt->bindParam(':user_message', $userMessage);

            // Execute statement
            $stmt->execute();

            $success = "Message Sent Succesfully";
        } else {
            echo "Invalid input.";
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


$query = $dbh->prepare("SELECT * FROM contacts ");
try {
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $ex) {
  echo $ex->getTraceAsString();
  echo $ex->getMessage();
  exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Brgy.Panadtaran Water Works</title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="admin/img/favicon.ico">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <!-- FontAwesome JS-->
	<script defer src="assets/fontawesome/js/all.min.js"></script>
    <!-- Global CSS -->
    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.min.css">   
    <!-- Theme CSS -->  
    <link id="theme-style" rel="stylesheet" href="assets/css/styles.css">
    
</head> 

<body>
    <!-- ******HEADER****** --> 
    <header id="header" class="header">  
        <nav class="main-nav navbar-expand-md" role="navigation">  
	        <div class="container-fluid position-relative">
                <a class="logo navbar-brand scrollto" href="#hero">
                    <span class="logo-icon-wrapper"><img class="logo-icon" src="assets/images/logo-icon.svg" alt="icon"></span>
                    <span class="text"><span class="highlight">Brgy.Panadtaran Water Works</span>
                </a>
                <!--//logo-->
            
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-collapse"  aria-controls="navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button><!--//nav-toggle-->
                
                <div id="navbar-collapse" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav ms-md-auto">
                    <li class="nav-item"><a class="nav-link text-white bg-info rounded px-3 py-2 mt-2 " href="./consumer/index.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link scrollto" href="#about">About</a></li>
                        <li class="nav-item"><a class="nav-link scrollto" href="#team">Email Us</a></li>
                        <li class="nav-item"><a class="nav-link scrollto" href="#pricing">Rates</a></li>
                        <li class="nav-item"><a class="nav-link scrollto" href="#contact">Contact</a></li>
                    </ul><!--//nav-->
                </div><!--//navabr-collapse-->
            </div><!--//container-->
            
        </nav><!--//main-nav-->                     
    </header><!--//header-->
    
    <div id="hero" class="hero-section">
        
        <div id="hero-carousel" class="hero-carousel carousel carousel-fade slide" data-bs-ride="carousel" data-bs-interval="10000">
            
            <div class="figure-holder-wrapper">
        		<div class="container">
            		<div class="row justify-content-end">
                		<div class="figure-holder" style="background-color:#0000;">
                	        <img class="figure-image img-fluid"  src="panadtaranlogo.png" alt="images"/>
                        </div><!--//figure-holder-->
            		</div><!--//row-->
        		</div><!--//container-->
    		</div><!--//figure-holder-wrapper-->
			<!-- Wrapper for slides -->
			<div class="carousel-inner">
    			
				<div class="carousel-item item-1 active">
					<div class="item-content container">
    					<div class="item-content-inner">
				            <h2 class="heading">Streamline Your Water Billing: Simplify Payments, Track Usage, and Ensure Transparency</h2>
				            <p class="intro">"Empowering communities with efficient water management. From accurate usage tracking to timely billing, we make water management simple, transparent, and hassle-free."</p>
				            <a class="btn btn-primary btn-cta" href="signin.php" target="_blank">Register Now</a>
    					</div><!--//item-content-inner-->
					</div><!--//item-content-->
				</div><!--//item-->
				
			</div><!--//carousel-inner-->

		</div><!--//carousel-->
    </div><!--//hero-->
    
    <div id="about" class="about-section">
        <div class="container text-center">
            <h2 class="section-title text-dark">Steps For Acquiring Water Connection</h2>
            <div class="items-wrapper row" >
                <div class="item col-md-4 col-12">
                    <div class="item-inner">
                        <div class="figure-holder">
                            <img class="figure-image" src="assets/images/figure-1.png" alt="image">
                        </div><!--//figure-holder-->
                        <h3 class="item-title">Permit</h3>
                        <div class="item-desc mb-3 text-black">
                        Get a barangay permit and the owner of the consumer
                        </div><!--//item-desc-->
                    </div><!--//item-inner-->
                </div><!--//item-->
                <div class="item col-md-4 col-12">
                    <div class="item-inner">
                        <div class="figure-holder">
                            <img class="figure-image" src="assets/images/figure-2.png" alt="image">
                        </div><!--//figure-holder-->
                        <h3 class="item-title">Materials</h3>
                        <div class="item-desc mb-3 text-dark">
                        Tell the plumber and ask for the materials</div><!--//item-desc-->
                    </div><!--//item-inner-->
                </div><!--//item-->
                <div class="item col-md-4 col-12">
                    <div class="item-inner">
                        <div class="figure-holder">
                            <img class="figure-image" src="assets/images/figure-3.png" alt="image">
                        </div><!--//figure-holder-->
                        <h3 class="item-title">Install</h3>
                        <div class="item-desc mb-3 text-dark">
                        Stallar or for installment</div>
                    </div><!--//item-inner-->
                </div><!--//item-->
            </div><!--//items-wrapper-->
        </div><!--//container-->
    </div><!--//about-section-->
    

    
    

                
            </div><!--//tabbed-area-->
            
        </div><!--//container-->
    </div><!--//features-->
    
    <div class="team-section" id="team">
        <div class="container text-center">
            <h2 class="section-title">Email US</h2>
            <div class="members-wrapper row">
              
                <div class="item col-md-12 col-12">
                    <div class="item-inner">

                    <div class="contains">
  <div class="text-column">
<form action="<?=$_SERVER['PHP_SELF']?>" class="row g-3" method="post">
<br>
<div class="col-md-12">
    <label for="name" class="form-label">Full Name</label>
    <input type="text" name="name" class="form-control" id="name" required/>
    <br>
  </div>
  <div class="col-md-12">
    <label for="inputEmail4" class="form-label">Email Address</label>
    <input type="email"  name="email" class="form-control" id="inputEmail4" required/>
    <br>
  </div>
  
  <div class="col-md-12">
    <label class="form-label">Message</label>
    <div class="form-floating">
  <textarea class="form-control" id="floatingTextarea" name="message" required></textarea>
  <label for="floatingTextarea">Type here...</label>
</div>
  </div>
  <div class="form-group">
        <input type="submit" class="bg-info text-white px-3 py-2 fs-7 rounded border-0" value="Send">
      </div>
</form>
</div>
</div>
                    </div><!--//item-inner-->
                </div><!--//item-->
            </div><!--//members-wrapper-->
        </div>
    </div><!--//team-section-->
    
    <div id="pricing" class="pricing-section">
        <div class="container text-center">
            <h2 class="section-title">Pricing</h2>
            <div class="pricing-wrapper row">
                <div class="item item-1 col-md-4 col-12">
                    <div class="item-inner">
                    <h3 class="item-heading">MINIMUM CONSUMPTION RATE<br><span class="item-heading-desc">0-10 Cubic Meter</span></h3>
                        <div class="price-figure">
                            <span class="currency">₱</span><span class="number">150</span>
                        </div><!--//price-figure-->
                        <br>
                    </div><!--//item-inner-->
                </div><!--//item-->
                <div class="item item-2 col-md-4 col-12">
                    <div class="item-inner">
                        <h3 class="item-heading">AVERAGE CONSUMPTION RATE<br><span class="item-heading-desc">11-20 Cubic Meter</span></h3>
                        <div class="price-figure">
                            <span class="currency">₱</span><span class="number">15/m³</span>
                        </div><!--//price-figure-->
                        <br>
                    </div><!--//item-inner-->
                </div><!--//item-->
                
                <div class="item item-3 col-md-4 col-12">
                    <div class="item-inner">
                        <h3 class="item-heading">HIGHER CONSUMPTION RATE<br><span class="item-heading-desc">21↑  Cubic Meter</span></h3>
                        <div class="price-figure">
                            <span class="currency">₱</span><span class="number">16/m³</span>
                        </div><!--//price-figure-->
                        <br>
                    </div><!--//item-inner-->
                </div><!--//item-->
            </div><!--//pricing-wrapper-->
            
        </div><!--//container-->
    </div><!--//pricing-section-->
    <div id="contact" class="contact-section">
        <div class="container">
            <h2 class="section-title text-center">Contact Us</h2>
            <br>
            <div class="row text-center">
            <div class="col-6 contact-content">
                <h5>Email Address</h5>
                <p><?php echo htmlspecialchars($result['email']);?></p>
                
            </div>
            <div class="col-6  contact-content">
            <h5>Telephone Number</h5>
                <p><?php echo htmlspecialchars($result['contact']);?></p>
            </div>
            </div>
        </div><!--//container-->
    </div><!--//contact-section-->
    
    <footer class="footer text-center">
        <div class="container">
            <!--/* This template is free as long as you keep the footer attribution link. If you'd like to use the template without the attribution link, you can buy the commercial license via our website: themes.3rdwavemedia.com Thank you for your support. :) */-->
            <p class="copy-right text-white">
          <span class="logo">
            <img src="admin/img/water.png" alt="Logo" width="35px" height="35px">
          </span>
          <span class="line">|</span>
          Copyright <span class="text-white">&copy;</span> <?php echo date('Y');?>
          <a>Brgy.Panadtaran Water Works</a>. All rights reserved.
        </p>
        </div><!--//container-->
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
            );
      </script>

  <?php }

    
    
    ?>
    <!-- Javascript -->          
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/plugins/gumshoe/gumshoe.polyfills.min.js"></script> 
    <script src="assets/js/main.js"></script> 
    <script src="assets/js/swal.js"></script> 
       
</body>
</html> 

