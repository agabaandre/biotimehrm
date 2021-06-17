<?php

$config= Modules::run("settings/getAll");


?>




<!doctype html>
<html class="no-js " lang="en">

<!-- Developed by Nkuke Henry 0705596470, 2018 for DAS Uganda -->
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">

<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<title><?php echo $config->system_name; ?></title>
<meta name="description" content="WrapTheme, University Admin">
<meta name="keywords" content="Dream,Africa,Schools">

<!-- Favicon-->
<link rel="icon" href="<?php echo base_url(); ?>assets/images/daswhite.png" type="image/x-icon">
<!-- Custom Css -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
<link href="<?php echo base_url(); ?>assets/css/main.css" rel="stylesheet">
<link href="<?php echo base_url(); ?>assets/css/login.css" rel="stylesheet">

<!--  You can choose a theme from css/themes instead of get all themes -->
<link href="assets/css/themes/all-themes.css" rel="stylesheet" />




</head>

<body class="theme-blue">
<div class="authentication">
	<div class="container-fluid">
		<div class="row clearfix">
			<div class="col-lg-9 col-md-8 col-xs-12 p-l-0">
                <div class="l-detail">
                    <h5 class="position">Password Recovery</h5>
                    <h1 class="position"><img src="<?php echo base_url(); ?>assets/images/<?php echo $config->logo; ?>"><span><?php echo $config->system_name; ?></span></h1>
                    <h3 class="position">Enter your email to reset your password</h3>
                    <p class="position">Your new password will be sent to the email you provide, If you don't receive the recovery password, please contact the Admin for support.</p>

                                             
                
                    <ul class="list-unstyled l-menu">
                        <li><a href="#">Contact Admin</a></li>   
                    </ul>
                </div>
            </div>
			<div class="col-lg-3 col-md-5 col-xs-12 p-r-0">
				<div class="card">

                    <?php echo $this->session->flashdata('msg'); ?>
                    
				    <h4 class="l-login">Forgot Password? <div class="msg">Enter your e-mail address below to reset your password.</div></h4>
                    <form class="col-md-12" id="reset" method="POST">

                        <div class="form-group form-float">
                            <div class="form-line">
                                <input type="text" class="form-control" required>
                                <label class="form-label">Username</label>
                            </div>
                        </div>

                        <div class="form-group form-float">
                            <div class="form-line">
                                <input type="email" class="form-control" required>>
                                <label class="form-label">Email</label>
                            </div>
                        </div>            
                        <div class="row">                    
                            <div class="col-xs-12">
                                <button class="btn btn-raised waves-effect btn-danger" type="submit">RESET MY PASSWORD</button>

                                <a href="<?php base_url(); ?>auth" class="btn btn-raised waves-effect">Sign In!</a>
                            </div>                            
                        </div>
                    </form>
				</div>
			</div>
		</div>
	</div>
<div id="instance1"></div>
</div>

<!-- Jquery Core Js --> 
<script src="<?php echo base_url(); ?>assets/bundles/libscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js -->
<script src="assets/bundles/vendorscripts.bundle.js"></script> <!-- Lib Scripts Plugin Js --> 

<script src="<?php echo base_url(); ?>assets/js/pages/login2/jparticles.js"></script>
<script src="<?php echo base_url(); ?>assets/js/pages/login2/particle.js"></script>

<script src="<?php echo base_url(); ?>assets/js/pages/login2/event.js"></script>
<script type="text/javascript">
  

$('#reset').submit(function(e){

    e.preventDefault();

var url="<?php base_url(); ?>auth/reset";

$('.status').html("An Email has been sent to you, Contact the admin if it doesn't arrive in a few minutes");

})



</script>


</body>

</html>
