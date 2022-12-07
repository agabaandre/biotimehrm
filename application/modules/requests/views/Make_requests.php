<section class="content">


  <div class="card">

    <div class="card-body row">

      <form method="post" class="requestForm" action="<?php echo base_url(); ?>requests/saveRequest" enctype="multipart/form-data" autocomplete="off">

        <div class="col-md-12">


          <div class="form-group">
            <label>From:</label>
            <div class="input-group-prepend">
              <span class="input-group-text">
                <i class="far fa-calendar-alt"></i>
              </span>

              <input type="text" class="form-control datepicker" name="dateFrom" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
          </div>

        </div>
        <div class="col-md-12">
          <div class="form-group">
            <label>To:</label>
            <div class="input-group-prepend">
              <span class="input-group-text">
                <i class="far fa-calendar-alt"></i>
              </span>

              <input type="text" class="form-control datepicker" value="<?php echo date('Y-m-d'); ?>" name="dateTo" required>
            </div>


          </div>
        </div>

        <div class="col-md-12">
          <div class="form-group">
            <label>Reason:</label>
            <select name="reason_id" class="form-control select2" required>
              <option value="" disabled selected>Select Out of Station Reason</option>
              <?php foreach ($reasons as $reason) : ?>
                <option value="<?php echo $reason->id; ?>"><?php echo $reason->reason; ?></option>

              <?php endforeach; ?>

            </select>
          </div>
        </div>

        <div class="col-md-12">
          <div class="form-group col-md-12">
            <label>Remarks</label>
            <textarea name="remarks" rows="5" class="form-control" required></textarea>
          </div>

          <div class="form-group col-md-12">
            <label>Attach Supporting Files</label>
            <input type="file" name="files" class="form-control">
          </div>
        </div>
        <div class="form-group">
          <button class="btn bg-gray-dark color-pale" type="submit" style="margin-top:1.7em;">Submit</button>
        </div>

      </form>
    </div>
  </div>
  </div>
</section>