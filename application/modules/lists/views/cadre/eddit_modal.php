<!-- Default modal Size -->
<div class="modal fade" id="EditModal<?php echo $cadre->id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Edit</b> <i><u><?php echo $cadre->cadre; ?></u></i></h4>
            </div>

            <form class="update_Cadre" enctype="multipart/form-data"
                  method="post" action="<?php echo base_url(); ?>lists/updateCadre">

                <div class="modal-body">

                  <input type="hidden" name="id" value="<?php echo $cadre->id; ?>" 
                  class="form-control"/> 
                
                   <strong style="margin-right: 1em;">Cadre Name </strong> 
                   <input type="text" name="name" value="<?php echo $cadre->cadre; ?>" 
                   class="form-control"  required /> 

                   <strong style="margin-right: 1em;">Description</strong> 
                   <textarea type="text" name="description" 
                   class="form-control"  required ><?php echo $cadre->description; ?></textarea> 

                   <strong style="margin-right: 1em;">Section</strong>
                    <select type="text" class="form-control" name="sector" required>
                        <option value="">Select...</option>
                        <option value="Eduction">Eduction</option>
                        <option value="Health">Health</option>
                    </select>
                    
                
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

