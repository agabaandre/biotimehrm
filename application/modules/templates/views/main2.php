<?php
    date_default_timezone_set('Africa/Kampala');
    require_once("includes/header.php");
    require_once("includes/navtop.php");

?>
<!-- Content Wrapper. Contains page content -->

<?php
    $this->load->view($module . "/" . $view);
?>

<!-- /.content-wrapper -->
<?php
    require_once("includes/footer.php");
?>