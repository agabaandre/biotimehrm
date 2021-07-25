

<!-- Main content -->
<section class="content">
     <div class="container-fluid">
       <!-- Main row -->

       <div class="row" style="min-height:550px">
         
         <section class="col-lg-12 ">
        
        
          <?php $staffs=Modules::run('biotime/get_new_users'); 
          
            
          ?>
                <table id="mytab2" class="table table-bordered table-striped mytable">
                  <thead>
                  <tr> 
                       <th>#</th>
                      <th> Staff iHRIS ID</th>
                      <th >Name</th>
     
                      <th>Job</th>
                
                      <th>Card Number</th>
                      <th>Enroll</th>
                    
                  </tr>
                  </thead>
                  <tbody>
                  <?php $i=1; foreach ($staffs as $staff) { 
                                                     
                                                     ?>
                                            
                                              <tr>
                                              <td data-label="No"><?php echo $i++; ?> </td>
                                              <td data-label="Staff iHRIS ID"><?php echo str_replace('person|','',$staff->ihris_pid); ?></td>
                                              <td data-label="NAME"><?php echo $staff->surname." ".$staff->firstname; ?> 
                                              </td>

                     
                                             
                                              <td data-label="JOB"><?php echo $staff->job; $job_id=$staff->job_id ?></td>
                                             
                                              <td data-label="CARD NUMBER"><?php echo $card_number=$staff->card_number;  ?></td>
                                              <?php
                                                   $dep=$staff->department_id;
                                                   $facility_id=$staff->facility_id;
                                                   $surname=$staff->surname;
                                                   $firstname=$staff->firstname
                                             ?>
                                              <td data-label="ATT STATUS"><a href="<?php echo base_url("cronjobs/biotimejobs/create_new_biotimeuser/").$firstname.'/'.$surname.'/'.$card_number.'/'.urlencode($facility_id).'/'.urlencode('dep'.$dep).'/'.urlencode($job_id); ?>" class="btn btn-default" target="_blank"><i class="fa fa-fingerprint"></i>Enroll</button></t)d>
                                              
                                             
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

   