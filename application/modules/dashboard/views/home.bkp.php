
<?php 


include("summary_widgets.php");

include("updates.php");
 ?>

<script src="<?php echo base_url();?>assets/js/fullcalendar/fullcalendar.min.js"></script>
 
 <script type="text/javascript">
 	
 	var base_url=$('.base_url').html();



 	 $('#attcalendar').fullCalendar({
        defaultView:'basicWeek',
        header: {
            left: 'prev, next, today',
            center: 'title',
             right: 'month, basicWeek, basicDay'
        },
        // Get all events stored in database
        eventLimit: true, // allow "more" link when too many events
        events:base_url+'calendar/getattEvents',
        selectable: false,
        selectHelper: true,
        editable: false,

         // Mouse over
            eventMouseover: function(calEvent, jsEvent, view){

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