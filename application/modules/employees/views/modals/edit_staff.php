<!-- Default modal Size -->
<div class="modal fade" id="EditStaffModal<?php echo str_replace('person|', '', $staff->ihris_pid); ?>"  >
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Edit</b> <i><u> Staff Info </u></i><span class="text text-success"><?php echo str_replace('person|', '', $staff->ihris_pid); ?></span></h4>
            </div>

            <div class="row">
                <div class="col-lg-12" style="margin-top: 20px;">
                    <center>
                        <a class="zwicon-camera new-contact__upload"  onclick="$('#imageClick').click()"></a>
                        <input name="userfile" access="image/*" type="file" id="imageClick" style="display: none;">
                        <img id="imageBlah" src="<?php echo base_url();?>assets/images/staff_images/staff/user.png" class="new-contact__img" width="80px;" onclick="$('#imageClick').click()">
                    </center>
                </div>
            </div>

            <form class="update_Cadre" enctype="multipart/form-data"
                  method="post" action="<?php echo base_url(); ?>employees/updateStaff">

                <div class="modal-body">

                  <input type="hidden" name="id" value="<?php echo $staff->ihris_pid; ?>" 
                  class="form-control"/> 
                
                   <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">SirName </strong> 
                                <input type="text" name="sirName" value="<?php echo $staff->surname; ?>" class="form-control form-control-sm"  required />
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">FirstName </strong> 
                                <input type="text" name="firstName" value="<?php echo $staff->firstname; ?>" class="form-control form-control-sm"  required /> 
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">OtherName </strong> 
                                <input type="text" name="otherName" value="<?php echo $staff->othername; ?>" class="form-control form-control-sm" /> 
                            </div>
                        </div>

                        <!-- Other -->
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">Gender</strong>
                                <select type="text" class="form-control form-control-sm" name="gender" required>
                                    <option value="<?php echo $staff->gender; ?>" selected disabled class="text text-danger"><?php echo $staff->gender; ?></option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">Date of Birth </strong> 
                                <input type="date" name="dob" value="<?php echo $staff->othername; ?>" class="form-control form-control-sm"  required /> 
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">National ID Number </strong> 
                                <input type="text" name="nin" value="<?php echo $staff->nin; ?>" minlength="14" maxlength="14" class="form-control form-control-sm"  required /> 
                            </div>
                        </div>


                        <!-- Other -->
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">Mobile Number </strong> 
                                <input type="text" name="mobile" value="<?php echo $staff->mobile; ?>" class="form-control form-control-sm" /> 
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">Telephone </strong> 
                                <input type="text" name="telephone" value="<?php echo $staff->telephone; ?>" class="form-control form-control-sm" /> 
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">Email </strong> 
                                <input type="email" name="email" value="<?php echo @$staff->email; ?>" class="form-control form-control-sm"/> 
                            </div>
                        </div>


                        <!-- Other -->
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">Department</strong>
                                <select type="text" class="form-control form-control-sm" name="department" required>
                                    <option value="<?php echo $staff->department; ?>" selected disabled class="text text-danger"><?php echo $staff->department; ?></option>
                                    <?php foreach($departments as $dep): ?>
                                        <option value="<?php echo $dep->dep_name; ?>"><?php echo $dep->dep_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">Job </strong> 
                                <select type="text" class="form-control form-control-sm" name="department" required>
                                    <option value="" selected disabled class="text text-danger"><?php echo $staff->job; ?></option>
                                    <?php foreach($jobs as $job): ?>
                                        <option value="<?php echo $job->job_title; ?>"><?php echo $job->job_title; ?></option>
                                    <?php endforeach; ?>
                                </select> 
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <strong style="margin-right: 1em;">Employment Terms</strong>
                                <select type="text" class="form-control form-control-sm" name="department" required>
                                    <option value="" selected disabled class="text text-danger"><?php echo $staff->employment_terms; ?></option>
                                    <?php foreach($terms as $term): ?>
                                        <option value="<?php echo $term->term_title; ?>"><?php echo $term->term_title; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                   </div> 
                    
                
                </div>
                <div class="modal-footer">
                    <!-- <button type="reset"  data-toggle="modal" class="btn btnkey bg-gray-dark color-pale">Reset</button> -->
                    <button type="submit"  data-toggle="modal" class="btn btn-info btn-outline">Save Changes</button>
                    <a href="#" class="close btn" data-dismiss="modal">Close</a>
                  
                </div>
              </form>
            </div>
        </div>
    </div>


    <script>
        var imageClick = document.querySelector('#imageClick');
        var imageBlah = document.querySelector('#imageBlah');
        imageClick.onchange = evt => {
            const [file] = imageClick.files;
                if(file){
                    imageBlah.src = URL.createObjectURL(file);
                }
        }
    </script>

