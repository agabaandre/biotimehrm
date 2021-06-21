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

                                   Staff List
                    
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

                                           <th data-field="Card Number">Card Number</th>
                                           <th> Staff iHRIS ID</th>
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
                                           <td><?php echo $staff->card_number; ?></td>
                                           <td data-label="Staff iHRIS ID"><?php echo str_replace('person|','',$staff->ihris_pid); ?></td>
                                           <td><?php echo $staff->surname . " " . $staff->firstname . " " . $staff->othername; ?>
                                           </td>
                                           <td><?php echo $staff->job; ?></td>
                                           <td><?php echo $staff->department; ?></td>
                                           <td><a class="btn btn-sm btn-default btn-outline"
                                                   href="<?php echo base_url(); ?>employees/employeeTimeLogs/<?php echo urlencode($staff->ihris_pid); ?>">Attendance
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