   <?php

    $staffs = Modules::run('employees/get_employees'); //print_r($staffs[0]);
    //print_r($staffs);
    ?>

   <!-- Contains page content -->
   <div class="dashtwo-order-area" style="padding-top: 10px; min-height: 35em;">
       <div class="container-fluid">
           <div class="row">
               <div class="col-lg-12">
                   <div class="panel panel-default">
                       <div class="panel-heading">
                           <h3 class="panel-title">

                                   Staff List for -
                                       <?php echo $_SESSION['department']; ?> <b><span style="color: green">/</span></b>
                                       <?php echo $_SESSION['division']; ?> <b><span style="color: green">/</span></b>
                                       <?php echo $_SESSION['unit']; ?>
                    
                           </h3>
                       </div>
                       <div class="panel-body">


                           <script>
                           $(document).ready(function() {
                               $('#emptb').DataTable();
                           });


                           function printDiv(printableDiv) {

                               var printContents = document.getElementById(printableDiv).innerHTML;
                               var originalContents = document.body.innerHTML;
                               document.body.innerHTML = printContents;

                               window.print();
                               document.body.innerHTML = originalContents;
                           }
                           </script>
                           <div class="col-sm-12"></div>
                           <input type="text" id="search" class="form-control" placeholder="Search Employee"
                               style="width:35%; margin-bottom:2px; border-radius:7px">
                         
                               <table id="table" class="table table-striped">


                                   <thead>
                                       <tr>
                                           <th data-field="ipps">No</th>

                                           <th data-field="ipps">IPPS</th>
                                           <!-- <th data-field="name" data-editable="false">IHRIS ID</th> -->
                                           <th data-field="name">Name</th>

                                           <th data-field="job">Job</th>
                                           <th data-field="department"> Department</th>
                                           <th data-field="department"> Attendance Report</th>

                                       </tr>
                                   </thead>
                                   <?php $i = 1;
                    foreach ($staffs as $staff) {

                      ?>
                                   <tbody>
                                       <tr>
                                           <td><?php echo $i++; ?></td>
                                           <td><?php echo $staff->ipps; ?></td>
                                           <!-- <td><?php $staff->ihris_pid; ?></td> -->
                                           <td><?php echo $staff->surname . " " . $staff->firstname . " " . $staff->othername; ?>
                                           </td>
                                           <td><?php echo $staff->job; ?></td>
                                           <td><?php echo $staff->department; ?></td>
                                           <td><a class="btn btn-sm btn-default btn-outline"
                                                   href="<?php echo base_url(); ?>employees/employeeTimeLogs/<?php echo $staff->ipps; ?>">Attendance
                                                   Report</a></td>


                                       <tr>
                                           <?php   } ?>
                                   </tbody>
                               </table>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>

   <script>
var $rows = $('#table tr');
$('#search').keyup(function() {
    var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

    $rows.show().filter(function() {
        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
        return !~text.indexOf(val);
    }).hide();
});
   </script>