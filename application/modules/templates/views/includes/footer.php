</div>
  <!-- /.content-wrapper -->

  <footer class="main-footer" style="background: #005662;
    color: #FFFFFF; text-align:center;" >
                <div class="col-lg-12">
                    <div class="footer-copy-right">
                    <!-- <img src="https://upload.wikimedia.org/wikipedia/commons/1/17/USAID-Identity.svg" style="width:180px; height:50px;">
                          <a href="http://health.go.ug" target="_blank"> <img src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Coat_of_arms_of_Uganda.svg" style="width:80px; height:50px;"> </a> -->
                    <p >&copy; <?php  echo date('Y'); ?>, Ministry of Health -Uganda. <strong>All Rights Reserved</strong></p>
                    </div>
                </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

<!-- Bootstrap 4 -->
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url() ?>assets/plugins/summernote/summernote-bs4.min.js"></script>
<!-- AdminLTE App -->
<!-- Select2 -->
<script src="<?php echo base_url() ?>assets/plugins/select2/js/select2.full.min.js"></script>
<script src="<?php echo base_url(); ?>assets/dist/js/adminlte.min.js"></script>

<script src="<?php echo base_url(); ?>assets/dist/js/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="<?php echo base_url(); ?>assets/dist/js/demo.js"></script>


<script src="<?php echo base_url(); ?>/assets/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?php echo base_url(); ?>/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- date-range-picker -->
<script src="<?php echo base_url() ?>assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="<?php echo base_url() ?>assets/plugins/summernote/summernote-bs4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo base_url(); ?>/assets/plugins/select2/js/select2.full.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/notify.min.js"></script>
<!-- fullCalendar 2.2.5 -->
<script src="<?php echo base_url()?>assets/plugins/moment/moment.min.js"></script>
<script src="<?php echo base_url()?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
 <!-- counterup JS
		============================================ -->
<script src="http://localhost/mohattendance_dev/assets/js/counterup/jquery.counterup.min.js"></script>

<script src="http://localhost/mohattendance_dev/assets/js/counterup/counterup-active.js"></script>
<!-- DataTables  & Plugins -->
<script src="<?php echo base_url(); ?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/jszip/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<div class="control-sidebar-bg"></div>
</div>

<script>
$( document ).ready(function() {
  $.fn.datepicker.defaults.format = "yyyy-mm-dd";
    $('.datepicker').datepicker({
        todayHighlight: true,
        autoclose: true,
   
    });
  });
</script>
<script>
// Radialize the colors
$( document ).ready(function() {
Highcharts.setOptions({
    colors: Highcharts.getOptions().colors.map(function(color) {
        return {
            radialGradient: {
                cx: 0.5,
                cy: 0.3,
                r: 0.7
            },
            stops: [
                [0, color],
                [1, Highcharts.color(color).brighten(-0.3).get('rgb')] // darken
            ]
        };
    })
});
});


</script>

<script>
 $(document).ready(function() {
    $('.mytable').DataTable( {
        dom: 'Bfrtip',
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        lengthMenu: [
            [ 25, 50, 100,150, -1 ],
            [ '25', '50', '100','150','200', 'Show all' ]
        ],
      
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pageLength',
            
            
        ]
    } );
});
</script>

<script>
 $(document).ready(function() {
    $('#timelogs').DataTable( {
    
        "paging": false,
        "lengthChange": true,
        "searching": false,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
     
    } );
});
</script>


<script type="text/javascript">
        
        $(document).ready(function(){

           // $.notify("Hello","success");

            var isPassChanged="1";

            if(isPassChanged!=1){

                $('#changepass').modal('show');
            }

            var url="<?php echo $this->uri->segment(2); ?>";

            if(url=="tabular" || url=="actuals"||  url=="fetch_report"|| url=="actualsreport"|| url=="tabular#" || url=="timesheet" || url=="attfrom_report"){

                $('body').addClass('sidebar-collapse');
                $('#sidebar').toggleClass('active');

            };


        } );


    </script>
 

<!-- ./wrapper -->
<?php
$uri = $_SERVER['REQUEST_URI'];
 $uri; // Outputs: URI
 
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
 
$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
 $linkquery=$url; // Outputs: Full URL
 // Outputs: Query String
?>

<!-- Modal -->
<div class="modal fade" id="switch" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Switch Facility</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form class="form form-horizontal" action="<?php echo base_url(); ?>departments/switchDepartment" method="post">
      <div class="row">
      <div class="col-md-12">
                
                    <label>District</label>
                   
                    
                    <select class="sdistrict form-control select2dist" id="district" name="district" onChange="getFacs($(this).val());"  <?php if(!(in_array('34', $permissions))){ echo "disabled"; }?>>
                    <option value="<?php echo urlencode(str_replace(" ","",($_SESSION['district_id'])))."_".urlencode(str_replace(" ","",$_SESSION['district'])); ?>" ><?php echo $_SESSION['district']; ?></option>
                            <?php
                           
                            $districts=Modules::run("districts/getDistricts");
                               foreach ($districts as $district){
                            ?>
                            <option value="<?php echo urlencode(str_replace(" ","",$district->district_id))."_".urlencode(str_replace(" ","",$district->district)); ?>"><?php echo ucwords($district->district); ?></option>
                            <?php }   ?>

                    </select>
                </div>

 
      <div class="col-md-12">
                <div class="form-group" >
                    <label>Facility</label>
                    <select  name="facility" onChange="getDeps($(this).val());" class="sfacility form-control select2dist" required>
                    <option value="" disabled>All</option>
    
                    </select>
                </div>
            </div>
    </div>
    
    <div class="row">
   
            <div class="col-md-12">
                <div class="form-group" >
                    <label>Department</label>
                    <select  name="department" onChange="getDivisions($(this).val());" class="sdepartment form-control select2dist">
                    <option value="">All</option>
                    </select>
                </div>
            </div>
  
            <input type="hidden" name="direct" value="<?php echo $linkquery; ?>" >
           

            <div class="col-md-12" style="display:none;">
                <div class="form-group">
                    <label>Division</label>
                    <select id="division" class="sdivison form-control select2dist" onChange="getUnits($(this).val());" name="division">
                    <option value="">All</option>
                    </select>
              </div>
           </div>
  </div>

  <div class="row" style="display:none;">
  <div class="col-md-6">
                <!-- < needs fixing> -->
                <div class="form-group">
                    <label>Section</label>
                    <select id="section" class="ssection form-control select2dist" onChange="getUnits($(this).val());" name="section">
                    <option value="">All</option>
                    </select>
              </div>
           </div>

          

            <div class="col-md-6">
                  <div class="form-group">
                          <label>Unit</label>
                    <select id="unit" name="unit" onchange="this.form.submit()" class="sunit form-control select2dist">
                    <option value="">All</option>
                         
                    </select>
                </div>
            </div>
  </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info"><i class="fa fa-paper-plane" aria-hidden="true">Switch</i></button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times">Close</i></button>
      </div>

        </form>

    </div>

  </div>
</div>



  <!-- change password modal at ones own wish -->
  <div class="modal" id="changepassword" data-backdrop="false">
                <div class="modal-dialog">
                    <div class="modal-content" >
                        <form method="post" action="<?php echo base_url(); ?>auth/changePass">
                        <div class="modal-header bg-default text-center">
                            <h3>Change Password</h3>
                            <h4 style="color:blue;"><?php echo $userdata['names']; ?> </h4>
                            <?php echo $this->session->flashdata('msg'); ?>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label>Old Password</label>
                                <input type="password" class="form-control" name="oldpass">
                            </div>
                            <div class="form-group">
                                <label>New password</i></label>
                                <input type="password" class="form-control" name="newpass">
                            </div>



                        </div>
                        <div class="modal-footer">
                            <input type="submit" value="Submit" class="btn btn-success">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>


              <!--change password--modal for first logins (as a MUST)-->

    <div class="modal" id="changepass" data-backdrop="true">

    
                <div class="modal-dialog">
                    <div class="modal-content" >
                        <form method="post" action="<?php echo base_url(); ?>auth/changePass">
                        <div class="modal-header bg-default text-center">
                            <h2>Change  Password</h2>
                            <?php echo $this->session->flashdata('msg'); ?>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label>Old password</label>
                                <input type="password" class="form-control" name="oldpass">
                            </div>
                            <div class="form-group">
                                <label>New password></label>
                                <input type="password" class="form-control" name="newpass">
                            </div>



                        </div>
                        <div class="modal-footer">
                            <input type="submit" value="Submit" class="btn btn-success">
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /change password--modal for first logins (as a MUST)-->

 



</body>
</html>
<script>

$(function () {
    $('.select2').select2()
    $('.select2dist').select2({ dropdownParent: "#switch" });
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
});
</script>

<script>
$("document").ready(function() {
    $(".sdistrict").change();
    //$(".sfacility").change();
    
   // console.log(time);
 
});
function getFacs(val) {
   
   $.ajax({          
           method: "GET",
           url: "<?php echo base_url(); ?>departments/get_facilities",
           data:'dist_data='+val,
           success: function(data){
               //alert(data);
               $(".sfacility").html(data);
           }
   });
}

function getDeps(val) {
   
   $.ajax({          
           method: "GET",
           url: "<?php echo base_url(); ?>departments/get_departments",
           data:'fac_data='+val,
           success: function(data){
               //alert(data);
               $(".sdepartment").html(data);
           }
         //  console.log('iwioowiiwoow');
   });
}
function getDivisions(val) {
   
    $.ajax({          
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_divisions",
            data:'depart_data='+val,
            success: function(data){
                // alert(data);
                $(".sdivision").html(data);
            }
    });
}


function getUnits(val) {
   
    $.ajax({          
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_units",
            data:'division='+val,
            success: function(data){
                //alert(data);
                $(".sunit").html(data);
            }
    });
}

</script>

