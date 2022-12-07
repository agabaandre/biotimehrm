<!-- Default modal Size -->
<div class="modal fade" id="EditModal<?php echo $job->id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Edit</b> <i><u><?php echo $job->job_title; ?></u></i></h4>
            </div>

            <form class="update_district" enctype="multipart/form-data"
                  method="post" action="<?php echo base_url(); ?>lists/updateJob">

                <div class="modal-body">

                  <input type="hidden" name="id" value="<?php echo $job->id; ?>" 
                  class="form-control"/> 
                
                   <strong style="margin-right: 1em;">Job Title </strong> 
                   <input type="text" name="job_title" value="<?php echo $job->job_title; ?>" 
                   class="form-control"  required /> 

                   <strong style="margin-right: 1em;">Job ID </strong> 
                   <input type="text" name="job_id" value="<?php echo $job->job_id; ?>" 
                   class="form-control"  required /> 

                   <strong style="margin-right: 1em;">Details</strong> 
                   <textarea type="text" name="description" class="form-control"><?php echo $job->description; ?></textarea>
                
                </div>
                <div class="modal-footer">
                    <button type="reset"  data-toggle="modal" class="btn btnkey bg-gray-dark color-pale">Reset</button>
                    <button type="submit"  data-toggle="modal" class="btn btn-info btn-outline">Save Changes</button>
                    <a href="#" class="close btn" data-dismiss="modal">Close</a>
                  
                </div>
              </form>
            </div>
        </div>
    </div>

