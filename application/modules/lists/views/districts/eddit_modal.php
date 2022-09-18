<!-- Default modal Size -->
<div class="modal fade" id="EditModal<?php echo $district->id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Edit</b> <i><u><?php echo $district->district; ?></u></i> District</h4>
            </div>

            <form class="update_district" enctype="multipart/form-data"
                  method="post" action="<?php echo base_url(); ?>lists/updateDistrict">

                <div class="modal-body">

                  <input type="hidden" name="id" value="<?php echo $district->id; ?>" 
                  class="form-control"/> 
                
                   <strong style="margin-right: 1em;">District Name </strong> 
                   <input type="text" name="name" value="<?php echo $district->name; ?>" 
                   class="form-control"  required /> 

                   <strong style="margin-right: 1em;">Region</strong> 
                   <input type="text" name="region" value="<?php echo $district->region; ?>" 
                   class="form-control"  required /> 
                
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

