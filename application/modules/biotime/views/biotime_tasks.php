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
               iHRIS Data vs Bio-Time Status
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

             <?php $machines = Modules::run('biotime/getMachines');




              ?>
             <table id="mytab2" class="table table-bordered table-striped">
               <thead>
                 <tr>
                   <th>DATA SET</th>
                   <th>IHRIS COUNT</th>
                   <th>LAST SYNC</th>
                   <th>BIOTIME COUNT</th>
                   <th>LAST SYNC</th>
                   <th>GAP</th>
                   <th> ACTION </th>


                 </tr>
               </thead>
               <tbody>



                 <tr>
                   <?php $activity = Modules::run('biotime/bioihriscontrol');
                    //print_r($activity);

                    ?>
                   <th data-label="Serial Number">DEPARTMENTS</th>
                   <td data-label="Facility"><?php echo $activity['ihrisdeps']; ?> </td>
                   <td data-label="Facility"><?php echo $activity['ilastsync']; ?> </td>
                   <td data-label="Last Sync"><?php echo $activity['biodeps']; ?></td>
                   <td data-label="Facility"><?php echo $activity['blastdepssync']; ?> </td>
                   <td data-label="Last Sync"><?php echo $activity['depsgap']; ?></td>
                   <td data-label="Last Sync"><a href="" class="btn bg-gray-dark color-pale"><span class="fa fa-sync"> </span> Sync from Biotime<a></td>


                 </tr>
                 <tr>

                   <th data-label="Serial Number">FACILITIES /AREAS</th>
                   <td data-label="Facility"><?php echo $activity['ihrisfacs']; ?>
                   </td>
                   <td data-label="Facility"><?php echo $activity['ilastsync']; ?> </td>
                   <td data-label="Last Sync"><?php echo $activity['biofacs']; ?></td>
                   <td data-label="Facility"><?php echo $activity['blastfacsync']; ?> </td>
                   <td data-label="Last Sync"><?php echo $activity['facsgap']; ?></td>
                   <td data-label="Last Sync"><a href="" class="btn bg-gray-dark color-pale"><span class="fa fa-sync"> </span> Sync from Biotime<a></td>


                 </tr>

                 <tr>

                   <th data-label="Serial Number">JOB / POSITION</th>
                   <td data-label="Facility"><?php echo $activity['ihrisjobs']; ?>
                   </td>
                   <td data-label="Facility"><?php echo $activity['ilastsync']; ?> </td>
                   <td data-label="Last Sync"><?php echo $activity['biojobs']; ?></td>
                   <td data-label="Facility"><?php echo $activity['blastjobssync']; ?> </td>
                   <td data-label="Last Sync"><?php echo $activity['jobsgap']; ?></td>
                   <td data-label="Last Sync"><a href="biotimejobs/" class="btn bg-gray-dark color-pale"><span class="fa fa-sync"> </span> Sync from Biotime<a></td>



                 </tr>
                 <tr>

                   <th data-label="Serial Number">EMPLOYEES</th>
                   <td data-label="Facility"><?php echo $activity['ihrisusers']; ?>
                   </td>
                   <td data-label="Facility"><?php echo $activity['ilastsync']; ?> </td>
                   <td data-label="Last Sync"><?php echo $activity['biousers']; ?></td>
                   <td data-label="Facility"><?php echo $activity['biouserssync']; ?> </td>
                   <td data-label="Last Sync"><?php echo $activity['usersgap']; ?></td>
                   <td data-label="Last Sync"><a href="<?php echo base_url() ?>biotimejobs/saveEnrolled" target="_blank" class="btn bg-gray-dark color-pale"><span class="fa fa-sync"> </span> Sync from Biotime<a></td>



                 </tr>


               </tbody>
               <tfoot>

               </tfoot>
             </table>




           </div><!-- /.card-body -['
            </div>
           /.card -->


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

             <?php $machines = Modules::run('biotime/getMachines');




              ?>
             <table id="mytab2" class="table table-bordered table-striped">
               <thead>
                 <tr>
                   <th> Serial Number</th>
                   <th>Facility</th>
                   <th>Last Sync</th>
                   <th>Number of Records</th>
                   <th>IP Address</th>
                   <th>Status</th>
                   <th>Manual Sync</th>


                 </tr>
               </thead>
               <tbody>
                 <?php $i = 1;
                  foreach ($machines as $machine) {

                  ?>

                   <tr>

                     <td data-label="Serial Number"><?php echo $machine->sn; ?></td>
                     <td data-label="Facility"><?php echo $machine->area_name ?>
                     </td>
                     <td data-label="Last Sync"><?php echo $machine->last_activity; ?></td>
                     <td data-label="Finger prints"><?php echo $machine->user_count; ?></td>
                     <td data-label="iP Address"><?php echo $machine->ip_address; ?></td>
                     <td data-label="Status"><?php $todaydate = date('Y-m-d', strtotime($machine->last_activity));
                                              if ($todaydate = date('Y-m-d')) {
                                                echo "<p style='color:green'>Active</p>";
                                              } else {
                                                "<p style='color:red'>InActive</p>";
                                              } ?></td>
                     <td data-label="Synch">
                       <!-- Trigger button to open the modal -->
                       <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#syncModal">
                         Open Sync Modal
                       </button>

                       <!-- Bootstrap 4 Modal -->
                       <div class="modal fade" id="syncModal" tabindex="-1" role="dialog" aria-labelledby="syncModalLabel" aria-hidden="true">
                         <div class="modal-dialog" role="document">
                           <div class="modal-content">
                             <div class="modal-header">
                               <h5 class="modal-title" id="syncModalLabel">Sync Time Data</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                 <span aria-hidden="true">&times;</span>
                               </button>
                             </div>
                             <div class="modal-body">
                               <!-- Form inside the modal -->
                               <form action="" method="POST" id="biotimesync"> 
                                 <div class="form-group">
                                   <label for="end_date">Date Before</label>
                                   <input type="date" name="end_date" class="form-control">
                                   <input type="hidden" name="terminal_sn" class="form-control" value="<?php echo $machine->sn; ?>" readonly>
                                 </div>

                                 <!-- Add any additional form fields or content here if needed -->

                                 <div class="modal-footer">
                                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                   <button type="submit" class="btn btn-success">Sync</button>
                                 </div>
                               </form>
                             </div>
                           </div>
                         </div>
                       </div>

                     </td>


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