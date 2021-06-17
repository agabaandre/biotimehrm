

<?php 

$schedules=Modules::run("schedules/getSchedules","r");
$workers=Modules::run("employees/get_employees");
$facilities=Modules::run("facilities/getFacilities");

?>
            
<?php    include("includes/dashcalendar.php");   ?>

  <?php  $colors=Modules::run('schedules/getrosterKey'); ?>
  
      <!-- Small boxes (Stat box) -->
     
  
        <div class="modal fade calendarmodal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
					 <!-- Notification -->
                <div class="alert" style="z-index: 1000;"></div>
				
                        <div class="error"></div>
                        <form class="form-horizontal" id="crud-form">
                        <input type="hidden" id="start">
                        <input type="hidden" id="end">
                            <div class="form-group">
                                <label class="col-md-6 control-label" style="text-align: left;" for="title">Health Worker</label>
                                <div class="col-md-12">
                                    <select id="user" name="user" class="form-control" >
                                       
                                       <?php foreach($workers as $worker) { ?>
                                       
                                        <option value="<?php echo $worker->ihris_pid; ?>"><?php echo $worker->surname." ".$worker->firstname; ?></option>
                                     
                                            <?php } ?>

                                    </select>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-md-6 control-label" style="text-align: left;" for="duty">Schedule</label>
                                <div class="col-md-12">
                                    <select id="duty" name="duty" class="form-control" >
                                        
                                        <?php foreach($schedules as $duty) { ?>
                                       
                                        <option value="<?php echo $duty->schedule_id; ?>"><?php echo $duty->schedule; ?></option>
                                     
                                            <?php } ?>
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="col-md-6 control-label" style="text-align: left;" for="color">Color</label>
                                <div class="col-md-12">
                                    <input id="color" name="color" type="text" class="form-control input-md" readonly="readonly"/>
                                    <span class="help-block">Duty Color</span>
                                </div>
                            </div>
                    
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
			   </form>
                </div>
            </div>
        </div>
        
        </div> 
      
  <!-- /box-body -->


<script src="<?php echo base_url();?>assets/js/fullcalendar/fullcalendar.min.js"></script>

<!--script src='<?php echo base_url();?>assets/js/rosta.js'></script-->

     
<script type="text/javascript">

    var base_url=$('.base_url').html(); // Here i define the base_url comes from a span in the 
    // Fullcalendar
    $('#calendar').fullCalendar({
        //defaultView:'agendaWeek',
        header: {
            left: 'prev, next, today',
            center: 'title',
             right: 'month, basicWeek, basicDay'
        },
        // Get all events stored in database
        eventLimit: true, // allow "more" link when too many events
        events:base_url+'calendar/getEvents',
        selectable: true,
        selectHelper: true,
        editable: true
    });
    
$('#duty').change(function(){

        var duty=$('#duty').val();

//day
if(duty=='14'){

    var kala='#d1a110';
}

else
    if(duty=='15'){
//even
    var kala='#49b229';
}

else

//night
if(duty=='16'){

    var kala='#29b299';
}
else

//off
if(duty=='17'){

    var kala='#297bb2';
}

else
//annual leave
if(duty=='18'){

    var kala='#603e1f';
}


else

//study leave
if(duty=='19'){

    var kala='#052942';
}

else
//maternity leave
if(duty=='20'){

    var kala='#280542';
}

else
//other
if(duty=='21'){

    var kala='#420524';
}


$('#color').val(kala);
$('#color').css('background-color',kala);


    });




</script>

