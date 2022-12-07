<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">

            <!-- right column -->
            <div class="col-md-9">
                <!-- Form Element sizes -->
                <div class="card card-default">
                    <div class="card-header">
                        <div class="">
                            <h5><i class="fas fa-file"></i> Facilities</h5>

                        </div>
                    </div>
                    <div class="card-body">
                    
                        <table class="table table-bordered table-striped districtsTable dataTable no-footer dtr-inline">
                            <tr>
                                <th style="width:2%;">#</th>
                                <th>Facility/Institution</th>
                                <th>District</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Level</th>
                                <th>
                                </th>
                            </tr>

                            <?php  $no=1; foreach($facilities as $facility): ?>

                            <tr>
                                <td data-label="#"><?php echo $no; ?>. </td>
                                <td><?php echo $facility->facility; ?></td>
                                <td><?php echo $facility->name; ?></td>
                                <td><?php echo $facility->institution_cateegory; ?></td>
                                <td><?php echo $facility->institution_type; ?></td>
                                <td><?php echo $facility->institution_level; ?></td>
                                <td>

                                    <?php //if (in_array('42', $permissions)) { ?>
                                    <button style="color: blue;" data-toggle="modal"
                                        data-target="#EditModal<?php echo $facility->id; ?>"><i class="fa fa-edit"></i></button>
                                    <?php //} ?>

                                    <?php //if (in_array('41', $permissions)) { ?>
                                    <button style="color: red;" data-toggle="modal"
                                        data-target="#delete<?php echo $facility->id; ?>"><i class="fa fa-trash"></i></button>
                                    <?php //} ?>
                                </td>
                            </tr>

                            <?php 
                                include('editModal.php');
                                include('deleteModal.php');
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
            <div class="col-md-3">
                <!-- general form elements -->
                <div class="card card-default">
                    <div class="card-header">
                        <div class="">
                            <h5><i class="fas fa-file"></i> Add New Facility</h5>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <form class="" method="post" action="<?php echo base_url(); ?>lists/saveFacility">
                        <div class="card-body">
                            <div class="form-group">
                                <label>District</label>
                                <select type="text" class="form-control select2" name="district_id" required>
                                <option disabled>Select ...</option>
                                <?php foreach($districts as $district){ ?>
                                    <option value="<?php echo $district->id; ?>"><?php echo $district->name; ?></option>
                                <?php } ?>

                                </select>
                            </div>

                            <div class="form-group">
                                <label>Facility Name</label>
                                <input type="text" class="form-control" name="facility" required>
                            </div>
                            <div class="form-group">
                                <label>Code</label>
                                <input type="text" class="form-control" name="facility_id" required>
                            </div>
                            <div class="form-group">
                                <label>Instution Cateegories</label>
                                <select type="text" class="form-control select2" name="institution_cateegory" required>
                                    <option value="">Select ...</option>
                                    <option value="Central Government">Central Government</option>
                                    <option value="Local Government (LG)">Local Government (LG)</option>
                                    <option value="Private for Profit (PFPs)">Private for Profit (PFPs)</option>
                                    <option value="Private not for Profit (PNFPs)">Private not for Profit (PNFPs)</option>
                                    <option value="Security Forces">Security Forces</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Instutution Types</label>
                                <select type="text" class="form-control select2" name="institution_type" required>
                                    <option value="">Select ...</option>
                                    <option value="City">City</option>
                                    <option value="Civil Society Organisations (CSO)">Civil Society Organisations (CSO)</option>
                                    <option value="District">District</option>
                                    <option value="Ministry">Ministry</option>
                                    <option value="Municipalit">Municipality</option>
                                    <option value="National Referral Hospital">National Referral Hospital</option>
                                    <option value="Regional Referral Hospital">Regional Referral Hospital</option>
                                    <option value="Specialised Facility">Specialised Facility</option>
                                    <option value="UBTS">UBTS</option>
                                    <option value="UCBHCA">UCBHCA</option>
                                    <option value="UCMB">UCMB</option>
                                    <option value="UMMB">UMMB</option>
                                    <option value="UOMB">UOMB</option>
                                    <option value="UPMB">UPMB</option>
                                    <option value="Uganda Healthcare Federation (UHF)">Uganda Healthcare Federation (UHF)</option>
                                    <option value="Uganda Peoples Defence Force (UPDF)">Uganda Peoples Defence Force (UPDF)</option>
                                    <option value="Uganda Police">Uganda Police</option>
                                    <option value="Uganda Prison Services">Uganda Prison Services</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Institution Level</label>
                                <select type="text" class="form-control select2" name="institution_level" required>
                                    <option value="">Select ...</option>
                                    <option value="Primary School">Primary School</option>
                                    <option value="Secondary School">Secondary School</option>
                                    <option value="Tertiary Instution">Tertiary Instution</option>
                                    <option value="Universiyty">Universiyty</option>
                                    <option value="Blood Bank Main Office">Blood Bank Main Office</option>
                                    <option value="Blood Bank Regional Office">Blood Bank Regional Office</option>
                                    <option value="City Health Office">City Health Office</option>
                                    <option value="Clinic/ Medical Centre">Clinic/ Medical Centre</option>
                                    <option value="DHOs Office">DHOs Office</option>
                                    <option value="General Hospital">General Hospital</option>
                                    <option value="HCII">HCII</option>
                                    <option value="HCIII">HCIII</option>
                                    <option value="HCIV">HCIV</option>
                                    <option value="Medical Bureau Main Office">Medical Bureau Main Office</option>
                                    <option value="Ministry">Ministry</option>
                                    <option value="Municipal Health Office">Municipal Health Office</option>
                                    <option value="National Referral Hospital">National Referral Hospital</option>
                                    <option value="Regional Referral Hospital">Regional Referral Hospital</option>
                                    <option value="Security Forces Main Office">Security Forces Main Office</option>
                                    <option value="Specialised National Facility">Specialised National Facility</option>
                                    <option value="Town Council Office">Town Council Office</option>
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