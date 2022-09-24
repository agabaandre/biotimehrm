<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- right column -->
            <div class="col-md-8">
                <!-- Form Element sizes -->
                <div class="card card-default">
                    <div class="card-header">
                        <div class="">
                            <h5><i class="fas fa-file"></i> Jobs</h5>

                        </div>
                    </div>
                    <div class="card-body">
                    
                        <table class="table table-bordered table-striped districtsTable dataTable no-footer dtr-inline">
                            <tr>
                                <th style="width:2%;">#</th>
                                <th>Job Title</th>
                                <th>Job ID</th>
                                <th>Details</th>
                                <th></th>
                            </tr>

                            <?php  $no=1;   foreach($jobs as $job): ?>

                            <tr>
                                <td data-label="#"><?php echo $no; ?>. </td>
                                <td><?php echo $job->job_title; ?></td>
                                <td><?php echo $job->job_id; ?></td>
                                <td><?php echo $job->description; ?></td>
                                <td>

                                    <?php //if (in_array('42', $permissions)) { ?>
                                    <button class="btn btn-sm btn  btnkey bg-gray-dark color-pale" data-toggle="modal"
                                        data-target="#EditModal<?php echo $job->id; ?>">Edit</button>
                                    <?php //} ?>

                                    <?php //if (in_array('41', $permissions)) { ?>
                                    <button class="btn btn-sm btnkey bg-danger color-pale " data-toggle="modal"
                                        data-target="#delete<?php echo $job->id; ?>">Delete</button>
                                    <?php //} ?>
                                </td>
                            </tr>

                            <?php 
                       include('eddit_modal.php');
                       include('delete_modal.php');
                        $no++;
                        endforeach ?>



                        </table>

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- /.card -->
            </div>
            <!--/.col (right) -->

            <!-- right column -->
            <div class="col-md-4">
                <!-- general form elements -->
                <div class="card card-default">
                    <div class="card-header">
                        <div class="">
                            <h5><i class="fas fa-file"></i> Add New Job</h5>

                        </div>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <form class="job_form" method="post" action="<?php echo base_url(); ?>lists/saveJob">
                        <div class="card-body">

                            <div class="form-group">
                                <label>Job Title</label>
                                <input type="text" class="form-control" name="job_title" required>
                            </div>
                            <div class="form-group">
                                <label>Job ID</label>
                                <input type="text" class="form-control" name="job_id" required>
                            </div>

                            <div class="form-group">
                                <label>Details</label>
                                <textarea type="text" name="description" class="form-control"></textarea>
                            </div>

                            <div class="card-footer">
                                <button type="reset" class="btn btn-sm btn-warning clear">Reset All</button>
                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                            </div>
                    </form>
                </div>
                <!-- /.card -->


            </div>
            <!--/.col (right) -->

        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->

<script>
    $(document).ready(function() {
        $('.districtsTable').DataTable({
            dom: 'Bfrtip',
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            lengthMenu: [
                [25, 50, 100, 150, -1],
                ['25', '50', '100', '150', '200', 'Show all']
            ],
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pageLength',
            ]
        });
    });
</script>