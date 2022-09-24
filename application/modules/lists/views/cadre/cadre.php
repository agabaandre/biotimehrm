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
                            <h5><i class="fas fa-file"></i> Cadres</h5>

                        </div>
                    </div>
                    <div class="card-body">
                    
                        <table class="table table-bordered table-striped cadreTable dataTable no-footer dtr-inline">
                            <tr>
                                <th style="width:2%;">#</th>
                                <th>Cadres</th>
                                <th>Details</th>
                                <th>Section</th>
                                <th>
                                    <?php print_r($permissions); ?>
                                </th>
                            </tr>

                            <?php  $no=1;   foreach($cadres as $cadre): ?>

                            <tr>
                                <td data-label="#"><?php echo $no; ?>. </td>
                                <td><?php echo $cadre->cadre; ?></td>
                                <td><?php echo $cadre->description; ?></td>
                                <td><?php echo $cadre->sector; ?></td>
                                <td>

                                    <?php //if (in_array('42', $permissions)) { ?>
                                    <button class="btn btn-sm btn  btnkey bg-gray-dark color-pale" data-toggle="modal"
                                        data-target="#EditModal<?php echo $cadre->id; ?>">Edit</button>
                                    <?php //} ?>

                                    <?php //if (in_array('41', $permissions)) { ?>
                                    <button class="btn btn-sm btnkey bg-danger color-pale " data-toggle="modal"
                                        data-target="#delete<?php echo $cadre->id; ?>">Delete</button>
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
                            <h5><i class="fas fa-file"></i> Add New Cadre</h5>

                        </div>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <form class="district_form" method="post" action="<?php echo base_url(); ?>lists/save_cadre">
                        <div class="card-body">

                            <div class="form-group">
                                <label>Cadre Name</label>
                                <input type="text" class="form-control" name="cadre" required>
                            </div>
                            <div class="form-group">
                                <label>Details</label>
                                <textarea type="text" class="form-control" name="description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Section</label>
                                <select type="text" class="form-control" name="sector" required>
                                    <option value="">Select...</option>
                                    <option value="Eduction">Eduction</option>
                                    <option value="Health">Health</option>
                                </select>
                            </div>
                            <!-- /.card-body -->

                            <div class="card-footer">
                                <button type="reset" class="btn bg-gray btn-outline">Reset All</button>
                                <button type="submit" class="btn bg-gray-dark color-pale">Submit</button>
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