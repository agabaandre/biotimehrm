<!-- Modal -->
<div id="permsModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-sm">

    <div class="modal-content">
      
        <form action="<?php echo base_url(); ?>auth/savePermissions" method="post">
          
      <div class="modal-header">
        
        <h4 class="modal-title" style="text-align: center;">Add permission</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" style="padding-left:3em;">
     
            <div class="form-group">
              <label>Definition</label>
              <input type="text" name="definition" class="form-control" title="Permission Description">

            </div>
            <div class="form-group">
              <label>Permission</label>
              <input type="text" name="name" class="form-control" title="Note: No Spaces Allowed!">
               <small class="help-block">No spaces allowed</small>
            </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info" >Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>

        </form>

    </div>

  </div>
</div>