<!-- Default modal Size -->
<div class="modal fade" id="user<?php echo $user->user_id; ?>"  >
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
           
                <h4 class="modal-title" id="defaultModalLabel">Update <?php echo $user->name; ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body"> 

            <span class="status" style="margin:0 auto;"></span>

              <form class="update_user" enctype="multipart/form-data" method="post" action="<?php echo base_url(); ?>auth/updateUser">
            <div class="col-md-12">
              <strong style="margin-right: 1em;"> Name </strong> 
                  <input type="text" name="name" value="<?php echo $user->name; ?>" class="form-control" style="width:100%" required> 
                
                <strong style="margin-right: 1em;">User Name </strong> 
                    <input type="text" name="username" value="<?php echo $user->username; ?>" class="form-control" style="width:100%" readonly> 
                    <strong style="margin-right: 1em;">Email </strong> 
                    <input type="text" name="email" value="<?php echo $user->email; ?>" class="form-control" style="width:100%"> 
                
                <strong style="margin-right: 1em;">User Group </strong>  
                <select name="role"  style="width:100%;" class="form-control role select2"  required>
              
                    <?php  foreach($usergroups as $usergroup): 
                                  ?>
                    <option value="<?php echo $usergroup->group_id; ?>" <?php if($user->role==$usergroup->group_id){ echo "selected";} ?> ><?php echo $usergroup->group_name; ?>
                        
                    </option>
                    <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-12" style="margin: 0 auto">
                <strong style="margin-right: 1em;">District </strong> 
                <select onchange="getFacs($(this).val());" name="district_id"  class="form-control select2 sditrict" style="width:100%;">
          
                    <?php  foreach($districts as $district): 
                                  ?>
                    <option value="<?php echo $district->district_id; ?>" <?php if($user->district==$district->district){ echo "selected";} ?> ><?php echo $district->district; ?></option>
                                <?php endforeach; ?>
                    </select>
                   
                    <br>
                <strong style="margin-right: 1em;">Facility</strong> 
                <select onchange="getDeps($(this).val());" name="facility_id" class="form-control select2 sfacility" style="width:100%;" >
              
         
                    <option value="<?php echo $user->facility_id; ?>" >
                        <?php echo $user->facility; ?>
      
                </option>
           

            </select>
         

                <br><br>
                <strong style="margin-right: 1em;">Department </strong> 
                <select name="department_id"  class="form-control select2 sdepartment" style="width:100%;">
                    
                <option value="<?php echo $user->department_id; ?>" >
                        <?php echo $user->department_id; ?>
                </option>
                </select>
        
             
            
                  <input type="hidden" name="user_id" value="<?php echo $user->user_id; ?>">

                   <button type="submit"  data-toggle="modal" class="btn btn-info">Save Changes</button>
                  
          
             </div>
            <div class="modal-footer">

                </div>
              </form>
            </div>
        </div>
    </div>
</div>
