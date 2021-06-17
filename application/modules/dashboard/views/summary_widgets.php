<?php
   $widgets= Modules::run('attendance/getWidgetData');
  print_r ($permissions=$userdata['permissions']);

?>

      <!-- search Area and Page names -->
<div class="breadcome-area mg-b-30 small-dn">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcome-list map-mg-t-40-gl shadow-reset">
                    <div class="row">
                    
                    <!-- <a href="#switch" class="btn btn-sm btn-primary pull-right" data-toggle="modal">Change Department/Division/Unit</a> 
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                            <ul class="breadcome-menu">
                                <li><a href="#">Home</a> <span class="bread-slash">/</span>
                                </li>
                                <li><span class="bread-blod"><?php $this->load->helper('url');  $segment2=ucwords($this->uri->segment(2)); if ($segment2!=""){ echo $segment2; } else { echo ucwords($this->uri->segment(1));} ?></span>
                                </li>
                            </ul>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- <script>
function getDivisions(val) {
   
    $.ajax({          
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_divisions",
            data:'depart_data='+val,
            success: function(data){
                //alert(data);
                $("#division").html(data);
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
                $("#unit").html(data);
            }
    });
}

</script> -->


<!-- income order visit user Start -->
<div class="income-order-visit-user-area">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3">
                <div class="income-dashone-total income-monthly shadow-reset nt-mg-b-30">
                    <!--div class="income-title">
                        <div class="main-income-head">
                            <div class="main-income-phara">
                                <span class="counter" style="color: white;"><?php echo Modules::run('requests/countPending'); ?></span>
                                <p>Requests</p>
                            </div>
                        </div>
                    </div-->
                    <div class="income-dashone-pro">
                        <div class="income-rate-total">
                            <div class="price-adminpro-rate">
                                <h3><span class="counter"><?php echo Modules::run('requests/countPending'); ?></span></h3>
                            </div>
                        </div>
                        <div class="income-range">
                            <p><i class="fa fa-calendar"></i> Pending Requests</p>

                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="income-dashone-total orders-monthly shadow-reset nt-mg-b-30">
                    <!--div class="income-title">
                        <div class="main-income-head">
                            <div class="main-income-phara order-cl">
                                <span class="counter" style="color: white;"><?=$widgets['users']?></span>
                                <p>System users</p>
                            </div>
                        </div>
                    </div-->
                    <div class="income-dashone-pro">
                        <div class="income-rate-total">
                            <div class="price-adminpro-rate">
                                <h3><span class="counter"><?=$widgets['users']?></span></h3>
                            </div>
                        </div>
                        <div class="income-range order-cl">
                            <p><i class="fa fa-users"></i> Users</p>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="income-dashone-total user-monthly shadow-reset nt-mg-b-30">
                    <!--div class="income-title">
                        <div class="main-income-head">
                            <div class="main-income-phara low-value-cl">
                                <span class="counter" style="color: white;"><?php echo Modules::run('departments/countDepart'); ?></span>
                                <p>Departments</p>
                            </div>
                        </div>
                    </div-->
                    <div class="income-dashone-pro">
                        <div class="income-rate-total">
                            <div class="price-adminpro-rate">
                                <h3><span class="counter"><?php echo Modules::run('departments/countDepart'); ?></span></h3>
                            </div>
                        </div>
                        <div class="income-range low-value-cl">
                            <p><i class="fa fa-institution"></i> Departments</p>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="income-dashone-total visitor-monthly shadow-reset nt-mg-b-30">
                    <!--div class="income-title">
                        <div class="main-income-head">
                            <div class="main-income-phara visitor-cl">
                                <span class="counter" style="color: white;"><?php echo Modules::run('employees/count_Staff'); ?></span>
                                <p>Staff</p>
                            </div>
                        </div>
                    </div-->
                    <div class="income-dashone-pro">
                        <div class="income-rate-total">
                            <div class="price-adminpro-rate">
                                <h3><span class="counter"><?php echo Modules::run('employees/count_Staff'); ?></span></h3>
                            </div>
                        </div>
                        <div class="income-range visitor-cl">
                            <p><i class="fa fa-briefcase"></i> Employees</p>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 



<div id="switch" class="modal fade" role="dialog">
  <div class="modal-dialog modal-md">

    <div class="modal-content">
      
    <form class="form form-horizontal" action="<?php echo base_url(); ?>departments/switchDepartment" method="post">
          
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" style="text-align: center;">Change Department</h4>
      
      </div>
      <div class="modal-body" style="padding-left:1em;">
     
            <div class="col-md-4">
                <div class="form-group" style="padding-right:;">
                    <label>Department</label>
                    <select id="depart" name="department" onChange="getDivisions($(this).val());" class="form-control">
                    <option value="">All</option>

                          
                            <?php
                                // onchange="this.form.submit()" 
                            //."_".$department->department
                            $departments=Modules::run("departments/getDepartments");
                               foreach ($departments as $department){
                            ?>
                            <option value="<?php echo $department->department_id."_".$department->department; ?>"><?php echo ucwords($department->department); ?></option>
                            <?php }   ?>

                    </select>
                </div>
            </div>

            <div class="col-md-1">
            </div>

            <div class="col-md-3">
                <div class="form-group" style="padding-right:;">
                    <label>Division</label>
                    <select id="division" class="form-control" onChange="getUnits($(this).val());" name="division">
                    <option value="">All</option>
                    </select>
              </div>
           </div>

           <div class="col-md-1">
            </div>

            <div class="col-md-3">
                  <div class="form-group" style="padding-right:;">
                          <label>Unit</label>
                    <select id="unit" name="unit" onchange="this.form.submit()" class="form-control form-control">
                    <option value="">All</option>
                         
                    </select>
                </div>
            </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info"><i class="fa fa-paper-plane" aria-hidden="true">Apply</i></button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times">Close</i></button>
      </div>

        </form>

    </div>

  </div>
</div> -->
