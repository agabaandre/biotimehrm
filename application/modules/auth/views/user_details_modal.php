<!-- Default modal Size -->
<div class="modal fade" id="user<?php echo $user->user_id; ?>"  >
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel">Update <?php echo $user->name; ?></h4>
            </div>
            <div class="modal-body"> 

              <form class="update_user" enctype="multipart/form-data" method="post" action="<?php echo base_url(); ?>auth/updateUser">
            <div class="col-md-12">
              <strong style="margin-right: 1em;"> Name </strong> 
                  <input type="text" name="name" value="<?php echo $user->name; ?>" class="form-control" style="width:100%" required> 
                
                <strong style="margin-right: 1em;">User Name </strong> 
                    <input type="text" name="username" value="<?php echo $user->username; ?>" class="form-control" style="width:100%" required> 
                
                <strong style="margin-right: 1em;">User Group </strong>  
                    <select name="group_id"  class="form-control" style="width:100%" required>
                      <option value="<?php echo $usergroup->group_id; ?>"><?php echo $user->group_name; ?></option>
                        <?php  foreach($usergroups as $usergroup): 
                                    ?>
                      <option value="<?php echo $usergroup->group_id; ?>"><?php echo $usergroup->group_name; ?>
                          
                      </option>
                      <?php endforeach; ?>
                    </select>
              </div>
              <div class="col-md-12" style="margin: 0 auto">
                <strong style="margin-right: 1em;">District </strong> 
                    <select name="district_id"  class="form-control" style="width:100%" >
                        <option value="<?php echo $district->district_id; ?>"><?php echo $user->district; ?>
                        </option>
                            <?php  foreach($districts as $district):?>
                        <option value="<?php echo $district->district_id; ?>"><?php echo $district->district; ?></option>
                                    <?php endforeach; ?>
                    </select> 
            
                <strong style="margin-right: 1em;">Department </strong> 
                    <select name="department"  class="form-control" style="width:100%" >
                      <option value="<?php echo $department->department_id; ?>"><?php echo $user->department; ?></option>
                      <?php  foreach($departments as $department): 
                                  ?>
                    <option value="<?php echo $department->department_id; ?>"><?php echo $department->department; ?></option>
                                <?php endforeach; ?>
                    </select>
                <strong style="margin-right: 1em;">Facility</strong> 
                    <select name="facility" class="form-control" style="width:100%" >
                        <option value="<?php echo $facility->facility_id; ?>"><?php echo $user->facility; ?>
                        </option>
                          <?php  foreach($facilities as $facility):  ?>
                        <option value="<?php echo $facility->facility_id; ?>"><?php echo $facility->facility; ?>   
                        </option>
                        <?php endforeach; ?>
                    </select>

                <br><br>
            
                  <input type="hidden" name="user_id" value="<?php echo $user->user_id; ?>">

                   <button type="submit"  data-toggle="modal" class="btn btn-info waves-effect">Save Changes</button>
                  

                <a href="#" class="close btn" data-dismiss="modal">Close</a>
             </div>
            <div class="modal-footer">

                </div>
              </form>
            </div>
        </div>
    </div>
</div>
