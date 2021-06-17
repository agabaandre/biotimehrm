   
<?php 

$staffs=Modules::run('employees/get_employees'); //print_r($staffs[0]);
//print_r($staffs);
?>
<style>
  table.paleBlueRows {
  font-family: "Times New Roman", Times, serif;
  border: 1px solid #FFFFFF;
  
  text-align: center;
  border-collapse: collapse;
}
table.paleBlueRows td, table.paleBlueRows th {
  border: 1px solid #FFFFFF;
  padding: 3px 2px;
}
table.paleBlueRows tbody td {
  font-size: 13px;
}
table.paleBlueRows tr:nth-child(even) {
  background: #D0E4F5;
}
table.paleBlueRows thead {
  background: #0B6FA4;
  border-bottom: 5px solid #FFFFFF;
}
table.paleBlueRows thead th {
  font-size: 17px;
  font-weight: bold;
  color: #FFFFFF;
  text-align: center;
  border-left: 2px solid #FFFFFF;
}
table.paleBlueRows thead th:first-child {
  border-left: none;
}

table.paleBlueRows tfoot {
  font-size: 14px;
  font-weight: bold;
  color: #333333;
  background: #D0E4F5;
  border-top: 3px solid #444444;
}
table.paleBlueRows tfoot td {
  font-size: 14px;
}
</style>
<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px; min-height: 35em;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                  <div class="panel-heading">
                      <h3 class="panel-title"> Staff List - <?php //echo $staffs[0]->facility; ?></h3>
                  </div>
                  <div class="panel-body">  

                      
                      
                      <!-- <table id="employeestbl" class="table">
                        <thead class="tth" style="color: white;">
                          <th>IPPS</th>
                          <th>Ihris ID</th>
                          <th>Surname</th>
                          <th>First Name</th>
                          <th>Othername</th>
                          <th>Job</th>
                         
                          <th>Facility</th>
                        </thead> -->
<script>
        $(document).ready(function() {
        $('#emptb').DataTable();
} );


 function printDiv(printableDiv){
   
        var printContents =document.getElementById(printableDiv).innerHTML;
				var originalContents= document.body.innerHTML;
				document.body.innerHTML = printContents;
				
				window.print();
				document.body.innerHTML = originalContents;
				}
</script>
<div class="col-sm-12"></div>

<div id="printableArea">
                        <table id="emptb"  class="table paleBlueRows table-striped">
                       
                                  
                          <thead>
                                                <tr>
                                
                                                    <th data-field="ipps">IPPS</th>
                                                    <!-- <th data-field="name" data-editable="false">IHRIS ID</th> -->
                                                    <th data-field="name">Name</th>
                                                 
                                                    <th data-field="job">Job</th>
                                                    <th data-field="department"> Department</th>
                                                    
                                                      </tr>
                                            </thead>
                                            <?php $i=1; foreach ($staffs as $staff) { 
                                                     
                                                     ?>
                                            <tbody>
                                              <tr>
                                              <td><?php echo $staff->ipps; ?></td>
                                              <!-- <td><?php  $staff->ihris_pid; ?></td> -->
                                            <td><?php echo $staff->surname. " ". $staff->firstname." ".$staff->othername; ?> 
                                            </td>
                                              <td><?php echo $staff->job; ?></td>
                                              <td><?php echo $staff->department; ?></td>
                                             
                                              <tr>
                                              <?php   } ?>
                                            </tbody>
                    </table>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {

        $('#employeestbl').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax":{
         "url": "<?php echo base_url('employees/getStaffDatatable'); ?>",
         "dataType": "json",
         "type": "POST",
         "data":{  '<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>' }
                       },
      "columns": [
              
              { "data": "ipps" },
              { "data": "ihris_pid" },
              { "data": "surname" },
              { "data": "firstname" },
              { "data": "othername" },
              { "data": "job" },
              { "data": "facility" },

           ],
           
           
        dom: 'Bfrtip',
        
        buttons: [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]
        
      });


$('#scheduletbl').slimscroll({
  height: '400px',
  size: '5px'
});



//$('.timepicker').timepicker({showInputs:false});




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
  

 var table = $('#emptb').DataTable( {
    buttons: [
        'copy', 'excel', 'pdf'
    ]
} );
  
table.buttons().container()
    .appendTo( $('.col-sm-12:eq(0)', table.table().container() ) );

</script>