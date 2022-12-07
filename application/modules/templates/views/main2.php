<?php
    date_default_timezone_set('Africa/Kampala');
    require_once("includes/header.php");
    require_once("includes/navtop.php");
    require_once("includes/sidenav.php");
    //db connection
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<?php
    $this->load->view($module . "/" . $view);
?>
</div>
<!-- /.content-wrapper -->
<?php
    require_once("includes/footer.php");
?>