

<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px; min-height: 35em;">
  <div class="container-fluid">
      <div class="row">
          <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Upload Schedules <h3>
                      <center><b><?php echo $this->session->flashdata('msg'); ?></b></center>
                </div>
                <div class="panel-body">

                  <div class="col-md-8 offset-2">
                    <form class="reason_form" action="<?php echo base_url(); ?>rosta/upload_rota" method="post"  enctype="multipart/form-data">
                        <input type="file" class="form" name="rota">
                        <br/><br/>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i>Upload Rosta</button>
                    </form>
                  </div>
                  <div class="col-md-4 offset-2">
                    <br/><br/><br/>

                     <a href="<?php echo base_url(); ?>assets/sample.xlsx"  class="btn btn-primary"><i class="fa fa-download"></i>Download Template</a>
                     <!--rosta/excel_template--> 
                  </div>
              </div>
          </div>
      </div>
    </div>
</div>
