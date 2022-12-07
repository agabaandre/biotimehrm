<div class="card">
  <div class="col-md-12" style=" background:white; border-radius: 5px;">
    <?php $i = 1;
    //print_r($facilitydata);
    ?>

    <div class="card-header with-border">
      <h5 class="card-title"> Variables</h5>
    </div>
  </div>

  <hr style="border:1px solid rgb(140, 141, 137);" />
  <div class="col-md-12">
    <form method="post" action="<?php echo base_url(); ?>svariables/index" autocomplte="off">

      <?php foreach ($setting as $key => $value) { ?>
        <div id="">
          <label><?php echo strtoupper(str_replace("_", " ", $key)); ?></label>
          <textarea class="form-control" name="<?php echo $key; ?>" style="width:100%;" <?php if ($key == 'id') {
                                                                                          echo "style='display:none;'";
                                                                                        } ?>)><?php echo $value; ?></textarea>
        </div>
      <?php  } ?>


      <div id="footer-buttons" style="clear:both; margin-top:20px; margin-bottom:4px;">
        <button class="btn btn-primary" type="submit"><span class="add"></span>Save</button>
    </form>
  </div>
</div>

</div>
</div>