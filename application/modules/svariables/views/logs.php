<!-- Main content -->
<div class="card">
  <section class="content">
    <div class="container-fluid">
      <!-- Main row -->

      <div class="row">

        <section class="col-lg-12 connectedSortable" style="min-height:500px;">
          <!-- Custom tabs (Charts with tabs)-->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                Event Logs

              </h3>
            </div>

            <div class="card-body">
              <?php echo Modules::run('svariables/readLogs');


              ?>
            </div>
            <!-- /.card-body -->
          </div>


        </section>


      </div>
      <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
  </section>
</div>
<!-- /.content -->