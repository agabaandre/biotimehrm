<!-- income order visit user End -->

<div class="dashtwo-order-area">
    <div class="container-fluid">
       

    <div class="row">
         
            <div class="col-lg-12">
                    <div class="col-lg-12" style="background: #fff;">

                     <p class="" style="text-align: left; margin-top: 5px; font-weight: bold; font-size: 1rem;">Attendance</p>
                     <hr style="color:#15b178;">
                     <p class="" style="text-align: center; margin-top: 5px;  font-size: 1.4rem;">Key</p>
                  <?php  $colors=Modules::run('schedules/getrosterKey'); ?>
                  <div class="col-lg-12" style="text-align:center;">
                    <p style="text-align:center; font-weight:bold; font:14rem;"></p>

                    <?php foreach ($colors as $color) { ?>
                        <small><button type="button" class="btn btn-sm btnkey" style="background-color:<?php echo $color->color;  ?>;"><?php echo $color->schedule;?>
                        </button> </small>
                    <?php  }?>
                  <style>
                    .btnkey{
                      width:15%;
                      color:#fff;
                      margin:2px;
                              }
                    @media only screen and (max-width: 720px) {
                         .btnkey{

                            width:100%;
                              }
                    }
                  </style>
                  </div>
                  </div>
                <div class="panel panel-default">
                  <div class="panel-heading">
                     
                  <div class="panel-body" style="max-height:550px; overflow-y: scroll;">
                      
                      <!--dashboard calendar--->
                      <div id='calendar'></div>
                  </div>
                </div>
                <span class="base_url" style="display: none;" ><?php echo base_url(); ?></span>
            </div>
            
    </div>
  </div>
  </div>
</div>

