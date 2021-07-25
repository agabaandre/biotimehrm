

<!-- Main content -->
<section class="content">
     <div class="container-fluid">
       <!-- Main row -->

       <div class="row">
         
         <section class="col-lg-12 ">
        
        
          <?php $staffs=Modules::run('biotime/get_enrolled'); 
          
            
          ?>
                <table id="mytab2" class="table table-bordered table-striped mytable">
                  <thead>
                  <tr> 
                       <th>#</th>
                      <th> Staff iHRIS ID</th>
                      <th >Name</th>
                      <th >Facility</th>
                      <th >Device</th>
                      <th>Job</th>
                
                      <th>Card Number</th>
                      <th>Status</th>
                    
                  </tr>
                  </thead>
                  <tbody>
                  <?php $i=1; foreach ($staffs as $staff) { 
                                                     
                                                     ?>
                                            
                                              <tr>
                                              <td data-label="No"><?php echo $i++; ?> </td>
                                              <td data-label="Staff iHRIS ID"><?php echo str_replace('person|','',$staff->ihris_pid); ?></td>
                                              <td data-label="NAME"><?php echo $staff->fullname." ".$staff->othername; ?> 
                                              </td>
                                              <td data-label="FACILITY"><?php echo $staff->facility; ?></td>
                                              <td data-label="DEVICE"><?php echo $staff->device; ?></td>
                                             
                                              <td data-label="JOB"><?php echo $staff->job; ?></td>
                                             
                                              <td data-label="CARD NUMBER"><?php echo $staff->card_number; ?></td>
                                              <td data-label="ATT STATUS"><?php  if ($staff->att_status==1) echo "<p style='color:green;'>Active</p>"; else{  echo "<p style='color:green;'>In-Active</p>"; }; ?></td>
                                              
                                             
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

   