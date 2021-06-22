 <!-- Main content -->
 <section class="content">
      <div class="container-fluid">
        <!-- Main row -->


  

        <div class="row">
        
              <!-- Left col -->
            <section class="col-lg-12 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                 iHRIS Data Bio-Time Status
                </h3>
                <div class="card-tools">
                  <ul class="nav nav-pills ml-auto">
                    <!-- <li class="nav-item">
                      <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
                    </li> -->
                  </ul>
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">

              <?php $machines=Modules::run('biotime/getMachines'); 

         
          
            
?>
      <table id="mytab2" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th >DATA SET</th>
            <th >IHRIS COUNT</th>
            <th >BIOTIME COUNT</th>
            <th>  ACTION  </th>
          
          
        </tr>
        </thead>
        <tbody>
      
     
                                  
                                    <tr>
                                   
                                    <th data-label="Serial Number">DEPARTMENTS</th>
                                    <td data-label="Facility"><?php echo $machine->area_name ?> 
                                    </td>
                                    <td data-label="Last Sync"><?php echo $machine->last_activity; ?></td>
                                    <td data-label="Last Sync"><?php echo $machine->last_activity; ?></td>
                                    
                                   
                                    </tr>
                                    <tr>
                                   
                                   <th data-label="Serial Number">FACILITIES /AREAS</th>
                                   <td data-label="Facility"><?php echo $machine->area_name ?> 
                                   </td>
                                   <td data-label="Last Sync"><?php echo $machine->last_activity; ?></td>
                                   <td data-label="Last Sync"><?php echo $machine->last_activity; ?></td>
                                   
                                  
                                   </tr>

                                   <tr>
                                   
                                   <th data-label="Serial Number">JOB / POSITION</th>
                                   <td data-label="Facility"><?php echo $machine->area_name ?> 
                                   </td>
                                   <td data-label="Last Sync"><?php echo $machine->last_activity; ?></td>
                                   <td data-label="Last Sync"><?php echo $machine->last_activity; ?></td>
                                   
                                  
                                   </tr>
                                    
        
        </tbody>
        <tfoot>
        
        </tfoot>
      </table>

              
              

              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->


            <!-- calender key -->
          </section>

          


          <!-- right col -->
          <section class="col-lg-12 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                 Attendance Sync
                </h3>
                <div class="card-tools">
                  <ul class="nav nav-pills ml-auto">
                    <!-- <li class="nav-item">
                      <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
                    </li> -->
                  </ul>
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">

              <?php $machines=Modules::run('biotime/getMachines'); 

         
          
            
?>
      <table id="mytab2" class="table table-bordered table-striped">
        <thead>
        <tr>
            <th> Serial Number</th>
            <th >Facility</th>
            <th >Last Sync</th>
            <th >Number of Records</th>
            <th>IP Address</th>
            <th>Manual Sync</th>
          
          
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



                  
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->

          
          </section>


        


          
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
