         <?php

            $requests = Modules::run('requests/myRequests');

            $user = $this->session->get_userdata();

            ?>

         <link href="<?php echo base_url(); ?>assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

         <div class="card">
             <div class="wrapper wrapper-content animated fadeInRight">
                 <div class="row">
                     <div class="col-lg-12">
                         <div class="ibox ">
                             <div class="ibox-title">
                                 <h5><?php echo $title; ?></h5>
                                 <div class="ibox-tools">
                                     <a class="collapse-link">
                                         <i class="fa fa-chevron-up"></i>
                                     </a>
                                     <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                         <i class="fa fa-wrench"></i>
                                     </a>
                                     <a class="close-link">
                                         <i class="fa fa-times"></i>
                                     </a>
                                 </div>
                             </div>
                             <div class="ibox-content">

                                 <div class="table-responsive">
                                     <table class="table table-striped table-bordered table-hover dataTables-example">
                                         <thead>
                                             <tr>
                                                 <th>#</th>
                                                 <th>Staff Name</th>
                                                 <th>Staff .ID</th>
                                                 <th>Facility</th>
                                                 <th>Request</th>
                                                 <th>Request Date</th>
                                                 <th>Duration</th>
                                                 <th>Action</th>
                                             </tr>
                                         </thead>
                                         <tbody>
                                             <?php
                                                $i = 1;
                                                foreach ($requests as $request) : ?>

                                                 <tr class="table-row tbrow content strow">
                                                     <td><?php echo $i ?></td>
                                                     <td><a><?php echo $request->surname . " " . $request->firstname . " " . $request->othername; ?></a></td>
                                                     <td><?php echo  str_replace("person|", "", $request->ihris_pid); ?></td>
                                                     <td><?php echo $request->facility; ?></td>
                                                     <td><?php echo $request->reason; ?></td>
                                                     <td><?php echo $request->date; ?></td>
                                                     <td><?php echo "<b><i> from:</i></b>  " . $request->dateFrom . " " . "<b><i>to:</b></i>  " . $request->dateTo; ?></td>
                                                     <td width="150px;">

                                                         <?php if ($request->status == "Pending") {

                                                                if ($user['ihris_pid'] !== $request->ihris_pid) {

                                                            ?>
                                                                 <a href="<?php echo base_url(); ?>requests/acceptRequest/<?php echo str_replace('|', '_', $request->entry_id); ?>" style="" class="print btn btn-success btn-sm btn-outline">Accept
                                                                 </a>
                                                                 <a href="<?php echo base_url(); ?>requests/rejectRequest/<?php echo str_replace('|', '_', $request->entry_id); ?>" style="" class="print btn btn-danger btn-sm btn-outline">Reject
                                                                 </a>
                                                             <?php }
                                                            } else { ?>

                                                             <label class="badge"><?php echo $request->status; ?></label>

                                                         <?php } ?>

                                                     </td>
                                                 </tr>
                                             <?php
                                                    $i++;
                                                endforeach;

                                                if (count($requests) == 0) {

                                                    echo "<tr><td colspan='8'><center><h3 class='text-danger'>No pending requests</h3></center></td></tr>";
                                                }
                                                ?>
                                         </tbody>
                                         <tfoot>

                                         </tfoot>
                                     </table>
                                 </div>

                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>

         <script src="<?php echo base_url(); ?>assets/js/plugins/dataTables/dTb/datatables.min.js"></script>
         <script src="<?php echo base_url(); ?>assets/js/plugins/dataTables/dTb/dataTables.bootstrap4.min.js"></script>