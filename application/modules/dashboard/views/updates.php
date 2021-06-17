<!-- income order visit user End -->

<div class="dashtwo-order-area">
    <div class="container-fluid">
       

    <div class="row">
         

            
            <div class="col-lg-12">
                    <div class="col-lg-12" style="background: #fff;">

                     <p class="" style="text-align: center; margin-top: 5px; font-weight: bold; font-size: 2rem;">Daily Attendance</p>
                     <hr style="color:#15b178;">
                     <p class="" style="text-align: center; margin-top: 5px; font-weight:; font-size: 1.4rem;">Key</p>
                  <?php  $colors=Modules::run('schedules/getattKey'); ?>
                  <div class="col-lg-12" style="text-align:center;">
                    <p style="text-align:center; font-weight:bold; font:14rem;"></p>

                    <?php foreach ($colors as $color) { ?>
                        <button type="button" class="btn btn-sm btnkey" style="background-color:<?php echo $color->color;  ?>;"><?php echo $color->schedule;?>
                        </button>  
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
                      <div id='attcalendar'></div>
                  </div>
                </div>
                
            </div>
            
    </div>
  </div>
  </div>
  

