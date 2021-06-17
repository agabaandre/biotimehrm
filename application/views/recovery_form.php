<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?php echo (!empty($setting->title)?$setting->title:null) ?> :: <?php echo (!empty($title)?$title:null) ?></title>

        <!-- Favicon and touch icons -->
        <link rel="shortcut icon" href="<?php echo base_url((!empty($setting->favicon)?$setting->favicon:'assets/img/icons/favicon.png')) ?>" type="image/x-icon">
        
        <!-- Start Global Mandatory Style -->
        <!-- Bootstrap -->
        <link href="<?php echo base_url('assets/css/bootstrap.min.css') ?>" rel="stylesheet" type="text/css"/>
        <!-- Bootstrap rtl -->
        <!-- Pe-icon -->
        <link href="<?php echo base_url('assets/css/pe-icon-7-stroke.css') ?>" rel="stylesheet" type="text/css"/>
        
        <!-- Theme style -->
        <link href="<?php echo base_url('assets/css/custom.min.css') ?>" rel="stylesheet" type="text/css"/>
        <!-- Theme style rtl -->

    </head>
    <body>
        <!-- Content Wrapper -->
        <div class="login-wrapper"> 
            <div class="container-center">
                <div class="panel panel-bd">
                    <div class="panel-heading">
                        <div class="view-header">
                            <div class="header-icon">
                                <i class="pe-7s-key"></i>
                            </div>
                            <div class="header-title">
                                <h3><?php echo (!empty($title)?$title:null) ?></h3>
                               
                               
                            </div>
                        </div>
                        <div class="">
                            <!-- alert message -->
                            <?php if ($this->session->flashdata('message') != null) {  ?>
                            <div class="alert alert-info alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <?php echo $this->session->flashdata('message'); ?>
                            </div> 
                            <?php } ?>
                            
                            <?php if ($this->session->flashdata('exception') != null) {  ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <?php echo $this->session->flashdata('exception'); ?>
                            </div>
                            <?php } ?>
                            
                            <?php if (validation_errors()) {  ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <?php echo validation_errors(); ?>
                            </div>
                            <?php } ?> 
                        </div>
                    </div>


                    <div class="panel-body">
                        <?php echo form_open('Api/recovery_submit','id="recoveryForm" novalidate'); ?>
                       
                            <div class="form-group">
                                <label class="control-label" for="password"><?php echo display('new_password') ?></label>
                                <input type="password"  placeholder="<?php echo display('new_password') ?>" name="password" id="password" class="form-control"> 
                                <span id="password_validation"></span>
                            </div>
                          
                            <div class="text-right"> 
                               
                                <button  type="button" onclick="recovery_formsubmit()" class="btn btn-success"><?php echo display('send') ?></button> 
                            </div>
                        </form>
                    </div>

                

                </div>
            </div>
        </div>
        <!-- /.content-wrapper -->

    </body>
</html>