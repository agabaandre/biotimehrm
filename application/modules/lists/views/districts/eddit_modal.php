<!-- Default modal Size -->
<div class="modal fade" id="EditModal<?php echo $district->id; ?>"  >
    <div class="modal-dialog modal-md" >
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel" style="text-align: center;"><b>Edit</b> <i><u><?php echo htmlspecialchars($district->name, ENT_QUOTES, 'UTF-8'); ?></u></i> District</h4>
            </div>

            <form class="update_district" enctype="multipart/form-data"
                  method="post" action="<?php echo base_url(); ?>lists/updateDistrict">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                <div class="modal-body">

                  <input type="hidden" name="id" value="<?php echo $district->id; ?>" 
                  class="form-control"/> 
                
                   <strong style="margin-right: 1em;">District Name </strong> 
                   <input type="text" name="name" value="<?php echo $district->name; ?>" 
                   class="form-control"  required /> 

                   <strong style="margin-right: 1em;">Region</strong>
                   <?php
                   $district_regions = isset($regions) && is_array($regions) ? $regions : [];
                   $current_region = trim((string) $district->region);
                   if ($current_region !== '' && !in_array($current_region, $district_regions, true)) {
                       $district_regions[] = $current_region;
                       sort($district_regions, SORT_NATURAL | SORT_FLAG_CASE);
                   }
                   ?>
                   <select name="region" class="form-control" required>
                       <option value="">Select region...</option>
                       <?php foreach ($district_regions as $region): ?>
                           <option value="<?php echo htmlspecialchars($region, ENT_QUOTES, 'UTF-8'); ?>"
                               <?php echo $current_region === $region ? 'selected' : ''; ?>>
                               <?php echo htmlspecialchars($region, ENT_QUOTES, 'UTF-8'); ?>
                           </option>
                       <?php endforeach; ?>
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

