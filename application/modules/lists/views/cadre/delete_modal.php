<!-- Default modal Size -->
<div class="modal fade" id="delete<?php echo $district->id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Are you sure you want to Delete</b>  <i><u><?php echo $district->district; ?></u></i>  district ?</h4>
            </div>

            <form enctype="multipart/form-data" method="post" 
                    action="<?php echo base_url(); ?>lists/deleteDistrict">

            <div class="modal-body"> 

              <div style="text-align: center; color: red;">

                 Confirming Delete will remove the district Permanently
                 <input type="hidden" name="id" value="<?php echo $district->id; ?>">

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
