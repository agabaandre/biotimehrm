   <?php

    ?>
   <style type="">
     .modal{
    clear:both;
    position: fixed;
    margin-left:100px;
    margin-top:120px;
    z-index: 10040;
    overflow-x: auto;
    overflow-y: auto;
}
</style>
   <div class="card">
     <!-- Contains page content -->
     <div class="dashtwo-order-area" style="padding-top: 10px;">
       <div class="container-fluid">
         <div class="row">
           <div class="col-lg-12">
             <div class="panel panel-default">
               <div class="panel-heading">
                 <h3 class="panel-title"><?php echo $title; ?><h3>

               </div>
               <div class="panel-body">
                 <div><a class="pull-right btn btn-info btn-sm" data-toggle="modal" data-target="#clearlogs">Clear Logs</a></div>
                 <br><br>

                 <table class="table table-striped thistbl">
                   <thead class="tth" style="color: white;">
                     <th>#</th>
                     <th>Action</th>
                     <th>Date</th>
                     <th>User</th>
                     <th width="30%"></th>
                   </thead>

                   <tbody>

                     <?php $no = 0;
                      foreach ($logs as $logEntry) {
                        $no++; ?>
                       <tr id="user<?php echo $logEntry->log_id; ?>">
                         <td><?php echo $no; ?>. </td>
                         <td>
                           <?php echo substr(ucwords($logEntry->activity), 0, 35) . "..."; ?>
                         </td>
                         <td><?php echo $logEntry->created_at; ?></td>
                         <td><?php echo ucwords($logEntry->username); ?></td>

                         <td>
                           <a href="#" data-toggle="modal" data-target="#check<?php echo $no; ?>"><i class="glyphicon glyphicon-edit"></i> More... </a>
                         </td>
                       </tr>


                       <!-- Modal -->
                       <div id="check<?php echo $no; ?>" class="modal fade" role="dialog">
                         <div class="modal-dialog">

                           <!-- Modal content-->
                           <div class="modal-content">
                             <div class="modal-header">
                               <button type="button" class="close" data-dismiss="modal">&times;</button>
                               <h4 class="modal-title">Activity Log</h4>
                             </div>
                             <div class="modal-body">
                               <h5><?php echo ucwords($logEntry->username); ?></h5>

                               <hr>

                               <p> <?php echo $logEntry->activity; ?></p>

                               <br>

                               <small>Date: <?php echo $logEntry->created_at; ?></small>

                             </div>
                             <div class="modal-footer">
                               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                             </div>
                           </div>

                         </div>
                       </div>

                     <?php  } ?>


                   </tbody>


                 </table>

               </div>
             </div>
           </div>
         </div>
       </div>
     </div>
   </div>

   <!-- confirm clear log Modal -->
   <div id="clearlogs" class="modal fade" role="dialog">
     <div class="modal-dialog modal-sm">

       <!-- Modal content-->
       <div class="modal-content">
         <div class="modal-header">
           <button type="button" class="close" data-dismiss="modal">&times;</button>
           <h4 class="modal-title">Clear Activity Log</h4>
         </div>
         <div class="modal-body">
           <br>
           <h3>Do you what to clear all logs?</h3>
           <br>

         </div>
         <div class="modal-footer">
           <a href="<?php echo base_url(); ?>admin/clearLogs" class="btn btn-danger">Yes, Clear All</a>

         </div>
       </div>

     </div>
   </div>




   </div>
   <!-- /.content-row -->
   </section>
   <!-- /.section-->
   </div>

   <!-- /.content-wrapper -->
   <?php


    ?>

   <script type="text/javascript">
     $(document).ready(function() {

       $('#scheduletbl').slimscroll({
         height: '400px',
         size: '5px'
       });

     });
   </script>