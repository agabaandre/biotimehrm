<div class="dashtwo-order-area" style="margin-top:20px;">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="col-lg-12" style="background: #fff;">
          <p class="" style="text-align: center; margin-top: 5px; font-weight: bold; font-size: 2rem;">Daily Attendance</p>
          <hr style="color:#15b178;">
          <p class="" style="text-align: center; margin-top: 5px;  font-size: 1.4rem;">Key</p>
          <?php $colors = Modules::run('schedules/getattKey'); ?>
          <div class="col-lg-12" style="text-align:center;">
            <p style="text-align:center; font-weight:bold; font:14rem;"></p>
            <?php foreach ($colors as $color) { ?>
              <button type="button" class="btn btn-sm btnkey" style="background-color:<?php echo $color->color;  ?>;"><?php echo $color->schedule; ?>
              </button>
            <?php  } ?>
            <style>
              .btnkey {
                width: 15%;
                color: #fff;
                margin: 2px;
              }

              @media only screen and (max-width: 720px) {
                .btnkey {
                  width: 100%;
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
          <span class="base_url" style="display: none;"><?php echo base_url(); ?></span>
        </div>
      </div>
    </div>
  </div>
  <script src="<?php echo base_url(); ?>assets/js/fullcalendar/fullcalendar.min.js"></script>
  <script type="text/javascript">
    var base_url = $('.base_url').html();
    $('#attcalendar').fullCalendar({
      defaultView: 'basicWeek',
      header: {
        left: 'prev, next, today',
        center: 'title',
        right: 'month, basicWeek, basicDay'
      },
      // Get all events stored in database
      eventLimit: true, // allow "more" link when too many events
      events: base_url + 'calendar/getattEvents',
      selectable: false,
      selectHelper: true,
      editable: false,
      // Mouse over
      eventMouseover: function(calEvent, jsEvent, view) {
        var tooltip = '<div class="event-tooltip">' + calEvent.duty + '</div>';
        $("body").append(tooltip);
        $(this).mouseover(function(e) {
          $(this).css('z-index', 10000);
          $('.event-tooltip').fadeIn('500');
          $('.event-tooltip').fadeTo('10', 1.9);
        }).mousemove(function(e) {
          $('.event-tooltip').css('top', e.pageY + 10);
          $('.event-tooltip').css('left', e.pageX + 20);
        });
      },
      eventMouseout: function(calEvent, jsEvent) {
        $(this).css('z-index', 8);
        $('.event-tooltip').remove();
      },
      // H
    });
  </script>