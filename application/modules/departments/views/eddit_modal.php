<!-- Default modal Size -->
<div class="modal fade" id="eddit<?php echo $department->dprt_id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Edit</b> <i><u><?php echo $department->department; ?></u></i> Department</h4>
            </div>

            <form class="update_department" enctype="multipart/form-data" method="post" action="<?php echo base_url(); ?>departments/updateDepartment">
                <div class="modal-body"> 

                  <strong style="margin-right: 1em;"> department ID </strong> 
                  <input type="text" name="department_d" value="<?php echo $department->department_id; ?>" class="form-control" required/> 
                
                    <strong style="margin-right: 1em;">Department Name </strong> 
                    <input type="text" name="department" value="<?php echo $department->department; ?>" class="form-control"  required /> 
                
             </div>
                <div class="modal-footer">

                   <button type="submit"  data-toggle="modal" class="btn btn-info waves-effect">Save Changes</button>
                   <a href="#" class="close btn" data-dismiss="modal">Close</a>
                  
                </div>
              </form>
            </div>
        </div>
    </div>
</div>
