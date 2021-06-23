
<?php 
// include_once("includes/head.php");
// include_once("includes/topbar.php");
// include_once("includes/sidenav.php");
// //include_once("");



$departs="";  //to store department options

foreach ($departments as $department) {
	
	if(!empty($department->department))
	{
	$departs.="<option value'".$department->department."''>".$department->department."</option>";
   }
}



?>
<style>

.cnumber{
    
    width:3%;
}

.cname{
    text-align: left;
    padding-left:1.5em;
    width:30%;
    
}
    	
@media only screen and (max-width: 980px)  {
    
    .cnumber{
    
    width:100%;
}
    
    .cname{
    padding-left:0em;
    text-align: left;
    width:100%;
    
}

.print{
    
    display:none;
}
    	    
    	    
    	}
    
    
</style>
  

  <div class="dashtwo-order-area" style="margin:15px;">
  <div class="container-fluid">
    <div class="row">
      <!-- Small boxes (Stat box) -->
             <div class="row">





                		   <div class="col-md-8" style="padding-bottom:0.5em;">
		    <form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>attendance/attendance_summary" method="post">
				<div class="col-md-3">

				<div class="control-group">

					<input type="hidden" id="month" value="<?php echo $month; ?>">

				<select class="form-control" name="month" onchange="this.form.submit()" >

					<option value="<?php echo $month; ?>"><?php echo strtoupper(date('F', mktime(0, 0, 0, $month, 10)))."(Showing below)"; ?></option>
					
<option value="01">JANUARY</option>
<option value="02">FEBRUARY</option>
<option value="03">MARCH</option>
<option value="04">APRIL</option>
<option value="05">MAY</option>
<option value="06">JUNE</option>
<option value="07">JULY</option>
<option value="08">AUGUST</option>
<option value="09">SEPTEMBER</option>
<option value="10">OCTOBER</option>
<option value="11">NOVEMBER</option>
<option value="12">DECEMBER</option>
				</select>

				</div>

</div>


			<div class="col-md-3">

				<div class="control-group">

						<input type="hidden" id="year" value="<?php echo $year; ?>">

				<select class="form-control" name="year" onchange="this.form.submit()">
				<option><?php echo $year; ?></option>

<?php for($i=-5;$i<=25;$i++){  ?>

<option><?php echo 2017+$i; ?></option>

<?php }  ?>

				</select>

				</div>

</div>



			<div class="col-md-3">

				<div class="control-group">

		<input type="submit" name="" value="Load Month" class="btn btn-success">

				</div>

</div>

			</form>
			</div>
			
			<div class="col-md-4">
			    
			 
			    
			    <?php 
if(count($sums)>0)
{



?>
			<a href="<?php echo base_url() ?>attendance/print_attsummary/<?php echo $year."-".$month; ?>" style="" class="print btn btn-success btn-sm" target="_blank"><i class="fa fa-print"></i>Print</a>


<?php } ?>
			    
		
	</div>



<div class="dashtwo-order-area" style="padding-top: 10px;">
  <div class="container-fluid">
      <div class="row">
<div class="col-md-12">

	<div class="panel">

		<div class="panel-header">

<?php 
if(count($sums)>0)
{



?>
			<a href="<?php echo base_url(); ?>attendance/attCsv/<?php echo $year."-".$month; ?>" style="margin-top:1em; margin-left:1em;" class="btn btn-success btn-sm"><i class="fa fa-file"></i> Export CSV</a>


<?php } ?>
		</div>
		<div class="panel-body">

<?php 

//print_r($sums);   //raw data

?>



<div class="col-md-3" style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></div>

	<div class="col-md-9" style="border-right: 0; border-left: 0; border-top: 0;">
		<h4>
<?php 
if(count($sums)<1)
{

echo "<font color='red'> No Schedule Data</font>";
}
else{

?>
	MONTHLY ATTENDANCE TO DUTY SUMMARY

		<?php
		 echo " - ".$sums[0]['facility']."<br>"; 

		echo "              ".date('F, Y',strtotime($year."-".$month));

		?>

<?php } ?>

	</h4></div>


<div id="table">

<div class="header-row tbrow">
    <span class="cell stcell  tbprimary cnumber"># <b id="name"></b></span>
    <span class="cell stcell   cname">Name</span>
    <span class="cell stcell ">Present</span>
	<span class="cell stcell ">Off Duty</span>
	<span class="cell stcell ">Official Request</span>
	<span class="cell stcell ">Leave</span>
	<span class="cell stcell ">Day Schedule</span>
	<span class="cell stcell ">Evening Schedule</span>
	<span class="cell stcell ">Night Schedule</span>
	<span class="cell stcell ">% Present</span>

	

	

</div>
<?php  $mydate=$year."-".$month ?>


<?php 

$no=1;



foreach($sums as $sum) {?>

<div class="table-row tbrow strow">
    <input type="radio" name="expand" class="fa fa-angle-double-down trigger">
    <span class="cell stcell  tbprimary" style="cursor:pointer;" data-label="#"><?php echo $no;?>
	<b id="name">. &nbsp;<span onclick="$('.trigger').click();"><?php echo $sum['person'];?></span></b>
</span>
    <span class="cell stcell  cname" data-label="Name"><?php echo $sum['person'];?></span>
    <span class="cell stcell " data-label="P"><?php echo $present=$sum['P'];?></span>
    <span class="cell stcell " data-label="O"><?php echo $sum['O'];?></span>
	<span class="cell stcell " data-label="R"><?php echo $sum['R'];?></span>
	<span class="cell stcell " data-label="L"><?php echo $sum['L'];?></span>
	<span class="cell stcell " data-label="D"><?php $roster=Modules::run('attendance/attrosta',$mydate,urlencode($sum['person_id'])); ?><?php echo $day=$roster['Day'][0]->days; ?></span>
	<span class="cell stcell " data-label="E"><?php echo $eve=$roster['Evening'][0]->days; ?></span>
	<span class="cell stcell " data-label="N"><?php echo $night=$roster['Night'][0]->days;?></span>
    
	<span class="cell stcell " data-label="Percentage Pr"><?php $per= round(($present/($day+$night+$eve))*100,1); if(is_infinite($per)||is_nan($per)){ echo  0; } else{ echo $per; } ?> % </span>



	

</div>

<?php

$no++; 

} ?>



</div>


</div></div>

</div></div>


</div><!--col 12-->

            </div>
  <!-- /.content-row -->
  
    <!-- /.section-->
  </div>
  </div>
  </div>
  </div>
  
  <!-- /.content-wrapper -->
 <?php 

// include_once("includes/footermain.php");
// include_once("includes/rightsidebar.php");
// include_once("includes/footer.php");



?>

<script type="text/javascript">
	

var url=window.location.href;

if(url=='<?php echo base_url(); ?>rosta/fetch_report'){


	$('.sidebar-mini').addClass('sidebar-collapse');
}


$('.csv').click(function(e){
    
    e.preventDefault();
    
    $.ajax({
        url:'<?php echo base_url(); ?>rosta/bundleCsv',
        success:function(res){
            
            console.log(res);
        }
    })
    
})

</script>
