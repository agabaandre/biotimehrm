<!-- Default modal Size -->
<div class="modal fade" id="eddit<?php echo $district->d_id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Edit</b> <i><u><?php echo $district->district; ?></u></i> District</h4>
            </div>
            <form class="update_district" enctype="multipart/form-data" method="post" action="<?php echo base_url(); ?>districts/updateDistrict">
                <div class="modal-body">
                  <strong style="margin-right: 1em;"> District ID </strong> 
                  <input type="text" name="district_d" value="<?php echo $district->district_id; ?>" class="form-control" required/> 
                
                    <strong style="margin-right: 1em;">District Name </strong> 
                    <input type="text" name="district" value="<?php echo $district->district; ?>" class="form-control"  required /> 
                
                </div>
                <div class="modal-footer">

                   <button type="submit"  data-toggle="modal" class="btn btn-info waves-effect">Save Changes</button>
                   <a href="#" class="close btn" data-dismiss="modal">Close</a>
                  
                </div>
              </form>
            </div>
        </div>
    </div>

