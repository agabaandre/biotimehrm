<!-- Default modal Size -->
<div class="modal fade" id="add_district"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Add</b> <i><u><?php echo $district->district; ?></u></i> District</h4>
            </div>

            <form class="district_form" method="post" 
                action="<?php echo base_url(); ?>districts/save_district">
                <div class="modal-body">
                
                   <strong style="margin-right: 1em;">District Name </strong> 
                   <input type="text" name="name"  class="form-control"  required /> 

                   <strong style="margin-right: 1em;">Region</strong> 
                   <input type="text" name="region" class="form-control"  required /> 
                
                </div>
                <div class="modal-footer">

                   <button type="reset" class="btn btn-sm btn-warning clear">Reset All</button>

                   <button type="submit"  data-toggle="modal" class="btn btn-info waves-effect">Save Changes</button>
                   <a href="#" class="close btn" data-dismiss="modal">Close</a>
                  
                </div>
              </form>
            </div>
        </div>
    </div>