<?php 
  $timelogs=Modules::run('employees/getTimeLogs');
?>
<html>
<head>
    <title>Time Logs</title>
<style>
body {font-family: Arial;
  font-size: 12pt;
  max-width:21cm;
  max-height:29.7cm;
}
p { margin: 0pt; }
table.items {
  border: 0.1mm solid #000000;
}
td { vertical-align: top; }
.items td {
  border-left: 0.2mm solid #000000;
  border-right: 0.2mm solid #000000;
}
table thead th { background-color: #EEEEEE;
  text-align: center;
  border: 0.1mm solid #000000;
  /*font-variant: small-caps;*/
}
.items tr td {
  border: 0.2mm solid #000000;
}
.items td.blanktotal {
  background-color: #EEEEEE;
  border: 0.1mm solid #000000;
  background-color: #FFFFFF;
  border: 0mm none #000000;
  border-top: 0.1mm solid #000000;
  border-right: 0.1mm solid #000000;
}
.items td.totals {
  text-align: right;
  border: 0.1mm solid #000000;
}
.items td.cost {
  text-align: "." center;
}
.logo{
margin-top:0em;
margin-left:20%;
margin-right:20%;
margin-bottom:0.5em;
}
.heading{
margin-top:0.4em;
margin-left:20%;
margin-right:10%;
margin-bottom:0.1em;
}
.title{
margin-top:0.0em;
margin-left:30%;
margin-right:10%;
margin-bottom:0.1em;
}
tr:nth-child(odd){
    background-color: #e1f4f7;
}
td{
    padding: 5px;
}
</style>
</head>
<body>
<table  width="100%" class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8">
<thead>
    <tr style="border-right: 0; border-left: 0; border-top: 0;">
    <td colspan=3 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>
    <?php
    ?>
    <td colspan=4 style="border-right: 0; border-left: 0; border-top: 0;">
      <h2>
      STAFF TIME LOG REPORT <br>
      <?php
      echo $_SESSION['facility_name']."<br>"; 
      ?>
    </h2></td>
    </tr>
                    <tr>
                        <th>#</th>
                        <th>NAME</th>
                        <th>JOB</th>
                        <th>DATE</th>
                        <th>TIME IN</th>
                        <th>TIME OUT</th>
                        <th width="30%">HOURS WORKED</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php 
                   //print_r($logs);
                    $no=0;
                    foreach($logs as $timelog) {
                      $no++;
                     ?>
                    <tr>
                      <td><?php echo $no; ?></td>
                      <td><?php echo $timelog->surname." ".$timelog->firstname; ?></td>
                      <td><?php echo $timelog->job; ?></td>
                      <td><?php echo $timelog->date; ?></td>
                      <td><?php echo date('H:i:s', strtotime($timelog->time_in)); ?></td>
`                     <td><?php  if (!empty($time_out=$timelog->time_out)) { echo date('H:i:s', strtotime($time_out=$timelog->time_out));} ?></td>
                      <td> <?php  
                               $initial_time = strtotime( $timelog->time_in)/ 3600;
                               $final_time = strtotime($timelog->time_out)/ 3600;
                                  if(($initial_time)==0 || ($final_time)==0){ 
                                    $hours_worked=0; 
                                  } 
                                  elseif($initial_time==$final_time){ 
                                    $hours_worked=0; 
                                  } 
                                  else{
                                    $hours_worked = round(($final_time - $initial_time), 1);   
                                  }
                                  if ($hours_worked<0){ 
                                    echo ($hours_worked*-1) .'hr(s)'; 
                                  } 
                                  else { 
                                    echo $hours_worked.'hr(s)'; 
                                  } ?>
                      </td>
                    </tr>
                      <?php  } ?>
                  </tbody>
                </table>
              </div>
              <!-- /.panel-body -->
              <div class="panel-footer">
              </div>
          </div>
          <!-- /.panel -->
        </div><!--col-md-12-->
            </div>
  <!-- /.content-row -->
   </section>
    <!-- /.section-->
  </div>
  <!-- /.content-wrapper -->
<!-- 
<script type="text/javascript">
  $(document).ready(function(){
$('#thistbl').slimscroll({
  height: '400px',
  size: '5px'
});
});
</script> -->