

<!-- Main content -->
<section class="content">
     <div class="container-fluid">
       <!-- Main row -->

       <div class="row">
         
         <section class="col-lg-12" style="min-height:450px;">
        
        
          <?php $machines=Modules::run('biotime/getMachines'); 

         
          
            
          ?>
                <table id="mytab2" class="table table-bordered table-striped mytable">
                  <thead>
                  <tr>
                      <th> Serial Number</th>
                      <th >Facility</th>
                      <th >Last Sync</th>
                      <th >Fingerprint Enrolled Users</th>
                      <th>IP Address</th>
                      <th>  Status         </th>
                    
                    
                  </tr>
                  </thead>
                  <tbody>
                  <?php $i=1; 
                  foreach ($machines as $machine) { 
                                                     
                                                     ?>
                                            
                                              <tr>
                                             
                                              <td data-label="Serial Number"><?php echo $machine->sn; ?></td>
                                              <td data-label="Facility"><?php echo $machine->area_name ?> 
                                              </td>
                                              <td data-label="Last Sync"><?php echo $machine->last_activity; ?></td>
                                              <td data-label="Finger prints"><?php echo $machine->user_count; ?></td>
                                              <td data-label="iP Address"><?php echo $machine->ip_address; ?></td>
                                              <td data-label="Status"><?php $todaydate = date('Y-m-d', strtotime($machine->last_activity)); if ($todaydate=date('Y-m-d')){echo "<p style='color:green'>Active</p>"; }else{ "<p style='color:red'>InActive</p>";}?></td>
  
                                             
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

   