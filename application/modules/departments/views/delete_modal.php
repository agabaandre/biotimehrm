<!-- Default modal Size -->
<div class="modal fade" id="delete<?php echo $department->dprt_id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Are you sure you want to Delete</b>  <i><u><?php echo $department->department; ?></u></i>  Department ?</h4>
            </div>

            <form class="update_department" enctype="multipart/form-data" method="post" action="<?php echo base_url(); ?>departments/deleteDepartment">
            <div class="modal-body"> 

              <div style="text-align: center; color: red;">
                 Confirming Delete will remove the department Permanently
              </div>
                
             </div>
                <div class="modal-footer">

                   <button type="submit"  data-toggle="modal" class="btn btn-info waves-effect">Yes, Delete</button>
                   <a href="#" class="close btn" data-dismiss="modal">Close</a>
                  
                </div>
              </form>
            </div>
        </div>
    </div>
