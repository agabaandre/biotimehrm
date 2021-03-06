
<?php 
$userdata=$this->session->get_userdata(); 
if   (!isset($userdata['names'])){
  redirect('auth');
}

$permissions=$userdata['permissions'];

 ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $setting->title ?> - <?php echo (!empty($title)?$title:null) ?></title>
        <!-- Favicon and touch icons -->
  <link rel="shortcut icon" href="<?php echo base_url(!empty($settings->favicon)?$settings->favicon:"assets/images/icons/favicon.png"); ?>">
       
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url()?>assets/css/responsive2.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/fontawesome-free/css/all.min.css">
 
  <!-- fullCalendar -->
  <link rel="stylesheet" href="<?php echo base_url()?>assets/bower_components/fullcalendar/dist/fullcalendar.min.css">
  <link rel="stylesheet" href="<?php echo base_url()?>assets/bower_components/fullcalendar/dist/fullcalendar.print.min.css" media="print">
  <link rel="stylesheet" href="<?php echo base_url()?>assets/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?php echo base_url()?>assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.min.css">
  
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/adminlte.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/highcharts-more.js"></script>
  <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>

  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
 <link href="<?php echo base_url(); ?>assets/css/fullcalendar.css" rel="stylesheet">
<style>
 @media (max-width: 767px) {
    .hidden-mobile {
      display: none;
    }
  }
 body {
    font-family:  'Arial', 'Cambria', 'Helvetica Neue', 'Source Sans Pro' ,'Helvetica', 'sans-serif';
    overflow-x: hidden;
    overflow-y: auto;
}
.callout.callout-success {
    border-left-color: #207597 !important;
}
  
  page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
  }

.select2-close-mask{
    z-index: 2099;
}
.select2-dropdown{
    z-index: 3051;
}

.dash-icon{
color:#37989d;
font-size:15px;
margin-right:4px;
}
.fa-circle{
color:#37989d;
font-size:6px !important;
}
.nav-drop{
 font-size:10px;
 font-weight:560;
 text-overflow: ellipsis;
 overflow: hidden; 
 

}
.nav-item{
  font-weight:570;
}


body::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
   box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); 
	background-color: #F5F5F5;
}

body::-webkit-scrollbar
{
	width: 0.5em;
	background-color: #F5F5F5;
}

body::-webkit-scrollbar-thumb
{
	background-color: #d2d4dc; 
  height:70%;
	border: 1px solid #555555;
  border-radius:4px;
  scrollbar-width: thin;
}
.sido{
  clear:both;
  overflow:auto;
  background:#222d32;
}
.sido::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3) !important;
       box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3); 
	background-color: #F5F5F5;
  scrollbar-width: thin;
}
  .btn{
    font-size: 12px !important;
  }

  .buttons-html5 {
    font-size: 12px !important;
    background:#343a40!important;
    margin:6px;
    border-radius:4px;

  }
  .buttons-page-length {
    font-size: 12px !important;
    background:#343a40!important;
    margin:6px;
    border-radius:4px;

  }
  .page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #17a2b8;
    border-color: #17a2b8;
}
.sido::-webkit-scrollbar
{
	width: 0.5em;
	background-color: #F5F5F5 !important;
  scrollbar-width: thin;
}

.sido::-webkit-scrollbar-thumb
{
	background-color: #d2d4dc; 
	border: 1px solid #555555 !important;
  border-radius:4px;
}
.btnkey{
  min-width:98px;;
  color:#fff !important;
  margin:2px;
  border-radius:2px;

  }
  .rbtnkey{
  min-width:148px;;
  color:#fff !important;
  margin:2px;
  border-radius:2px;

  }
  .fc-content{
    color:#fff !important;

  }


</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<!-- Site wrapper -->
<div class="wrapper">
