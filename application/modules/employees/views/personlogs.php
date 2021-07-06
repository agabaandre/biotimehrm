

<!-- Main content -->
<section class="content">
     <div class="container-fluid">
       <!-- Main row -->

       <div class="row">
         
         <section class="col-lg-12 ">
        
        
          <?php $staffs=Modules::run('employees/get_employees'); 
          
            
          ?>
                <table id="mytab2" class="table table-bordered table-striped mytable">
                  <thead>
                  <tr>
                      <th> Staff iHRIS ID</th>
                      <th >Name</th>
                      <th >Facility</th>
                     
                      <th>Job</th>
                
                      <th>Card Number</th>
                      <th>Attendance Report</th>
                    
                  </tr>
                  </thead>
                  <tbody>
                  <?php $i=1; foreach ($staffs as $staff) { 
                                                     
                                                     ?>
                                            
                                              <tr>
                                             
                                              <td data-label="Staff iHRIS ID"><?php echo str_replace('person|','',$staff->ihris_pid); ?></td>
                                              <td data-label="NAME"><?php echo $staff->surname. " ". $staff->firstname." ".$staff->othername; ?> 
                                              </td>
                                              <td data-label="FACILITY"><?php echo $staff->facility; ?></td>
                                             
                                              <td data-label="JOB"><?php echo $staff->job; ?></td>
                                            
                                              <td data-label="CARD NUMBER"><?php echo $staff->card_number; ?></td>
                                              <td data-label="ATTENDANCE"><a class="btn btn-sm btn-default btn-outline"
                                                   href="<?php echo base_url(); ?>employees/employeeTimeLogs/<?php echo urlencode($staff->ihris_pid); ?>">
                                                   <i class="fa fa-eye" aria-hidden="true"></i> Report</a></td>
                                             
                                              </tr>
                                              <?php   } ?>
                  
                  </tbody>
                  <tfoot>
                  
                  </tfoot>
                </table>
        
         
         
         </section>
       </div>
       <!-- /.row (main row) -->
     </div><!-- /.container-fluid -->
   </section>
   <!-- /.content -->

   