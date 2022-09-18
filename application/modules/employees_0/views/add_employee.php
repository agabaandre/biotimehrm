<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- right column -->
            <div class="col-md-8">
                <!-- Form Element sizes -->
                <div class="card card-default">
                    <div class="card-header">
                        <div class="callout callout-success">
                            <h5><i class="fas fa-file"></i> Districts</h5>

                        </div>
                    </div>
                    <div class="card-body">
                    
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
                        <div class="callout callout-success">
                            <h5><i class="fas fa-file"></i> Add New District</h5>

                        </div>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <form class="district_form" method="post" action="<?php echo base_url(); ?>lists/save_district">
                        <div class="card-body">

                            <div class="form-group">
                                <label>District Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="form-group">
                                <label>Region</label>
                                <input type="text" class="form-control" name="region" required>
                            </div>
                            <!-- /.card-body -->

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