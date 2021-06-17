











              <table id="employeestbl" class="table table-striped">
                <thead>
                  <th>Ihris ID</th>
                  <th>Surname</th>
                  <th>First Name</th>
                  <th>Othername</th>
                  <th>Job</th>
                  <th>IPPS</th>
                  <th>Facility</th>
                </thead>
              </table>





            </div>
  <!-- /.content-row -->
   </section>
    <!-- /.section-->
  </div>
  


<script>
    $(document).ready(function () {

        $('#employeestbl').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
         "url": "<?php echo base_url('employees/getStaffDatatable') ?>",
         "dataType": "json",
         "type": "POST",
         "data":{  '<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>' }
                       },
      "columns": [
              { "data": "ihris_pid" },
              { "data": "surname" },
              { "data": "firstname" },
              { "data": "othername" },
              { "data": "job" },
              { "data": "ipps" },
              { "data": "facility" },

           ],
          //dom: 'Bfrtip',
        /*buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]*/
      });


$('#scheduletbl').slimscroll({
  height: '400px',
  size: '5px'
});


$('.timepicker').timepicker({showInputs:false});




$('#schedule_form').submit(function(e){

  e.preventDefault();

  var data=$(this).serialize();
  var url='<?php echo base_url(); ?>schedules/add_schedule'

  $.ajax({url:url,
method:"post",
data:data,
success:function(res){

  console.log(res);

  $('.suc').html("<center>Schedule Added</center>");

  $('#reset').click();


}//success

}); // ajax



});//form submit



$('.delete').click(function(e){

  e.preventDefault();

  var id=$(this).attr('id');
  var url='<?php echo base_url(); ?>schedules/delete_schedule/'+id;

  $.ajax({url:url,
success:function(res){

  console.log(res);

  $('#row'+id).remove();

  $('#dela'+id).html("<font color='green'>"+res+"</font>");

  setTimeout(function(){

    $('#dela'+id).html("");

    $('#del'+id).modal('hide');
  },1500);


}//success

}); // ajax



});//form submit


  });//doc
  



</script>