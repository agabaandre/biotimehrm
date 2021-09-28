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
<section class="content">
     <div class="container-fluid">
       <!-- Main row -->
<div class="row">
<div class="col-md-12">
<div class="callout callout-success">
             

			 <form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>attendance/attendance_summary" method="post">
			 <div class="row">
					 <div class="col-md-2">

						 <div class="control-group">

							 <input type="hidden" id="month" value="<?php echo $month; ?>">

							 <select class="form-control select2" name="month"  onchange="this.form.submit()">

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


					 <div class="col-md-2">
						 <div class="control-group">

							 <input type="hidden" id="year" value="<?php echo $year; ?>">

							 <select class="form-control select2" name="year" onchange="this.form.submit()">
									 <option><?php echo $year; ?></option>

									 <?php for($i=-5;$i<=25;$i++){  ?>

									 <option><?php echo 2017+$i; ?></option>

									 <?php }  ?>
							 </select>
				
						 </div>
					 </div>
					 <div class="col-md-4">
						 <div class="control-group">
					   

						 <?php 
						 
						 $facility=$this->session->userdata['facility'];
						 //print_r($facility);
						 $employees=Modules::run("employees/get_employees"); ?>
						 <select class="form-control select2" name="empid" select2>
									 <option value="" selected disabled>Select Employee</option>

									 <?php foreach($employees as $employee){  ?>

									 <option value="<?php echo $employee->ihris_pid ?>"><?php echo $employee->surname.' '.$employee->firstname.' '.$employee->othername;?></option>

									 <?php }  ?>
							 </select>
				
						 </div>
					 </div>

					 <div class="col-md-4">

						 <div class="control-group">

							 <button type="submit" name=""  class="btn bg-gray-dark color-pale" style="font-size:12px;">Apply</button>
							 <?php 
		if(count($sums)>0)
		{
?>
			<a href="<?php echo base_url() ?>attendance/print_attsummary/<?php echo $year."-".$month; ?>" style="font-size:12px;" class="btn bg-gray-dark color-pale" target="_blank"><i class="fa fa-print"></i>Print</a>
					<?php } ?>

					<?php 
					if(count($sums)>0)
					{
					?>
								<a href="<?php echo base_url(); ?>attendance/attsums_csv/<?php echo $year."-".$month; ?>" style="font-size:12px;" class="btn bg-gray-dark color-pale"><i class="fa fa-file"></i> Export CSV</a>
					<?php } ?>
        </div>

						 </div>

					 </div>
			 </div>
				 </form>
	  </div>

			</div>

<div class="panel-body">
<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
<?php 
//print_r($sums);   //raw data
?>
<div class="col-md-3" style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></div>
	<div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0;">
		<p style="font-size: 16px; font-weight:bold; margin:0 auto; ">
<?php 
if(count($sums)<1)
{
echo "<font color='red'> No Schedule Data</font>";
}
else{
?>
	MONTHLY ATTENDANCE TO DUTY SUMMARY
		<?php  echo " - ".$sums[0]['facility']." ".date('F, Y',strtotime($year."-".$month));
		?>
<?php } ?>
	</p></div>
<div id="table">
<div class="header-row tbrow">
    <span class="cell stcell  tbprimary cnumber"># <b id="name"></b></span>
    <span class="cell stcell   cname">Name</span>
	<span class="cell stcell">Job</span>
    <span class="cell stcell ">Present</span>
	<span class="cell stcell ">Off Duty</span>
	<span class="cell stcell ">Official Request</span>
	<span class="cell stcell ">Leave</span>
	<span class="cell stcell ">Holiday</span>
	<span class="cell stcell ">Day Schedule</span>
	<span class="cell stcell ">Evening Schedule</span>
	<span class="cell stcell ">Night Schedule</span>
	<span class="cell stcell ">% Present</span>
</div>
<?php  $mydate=$year."-".$month ?>
<?php 
$no=(!empty($this->uri->segment(3)))?$this->uri->segment(3):1;
foreach($sums as $sum) {?>
<div class="table-row tbrow strow">
    <input type="radio" name="expand" class="fa fa-angle-double-down trigger">
    <span class="cell stcell  tbprimary" style="cursor:pointer;" data-label="#"><?php echo $no;?>
	<b id="name">. &nbsp;<span onclick="$('.trigger').click();"><?php echo $sum['fullname'];?></span></b>
</span>
    <span class="cell stcell  cname" data-label="Name"><?php echo $sum['fullname'].' '.$sum['othername'];?></span>
	<span class="cell stcell  cname" data-label="Name"><?php echo $sum['job'] ?></span>
    <span class="cell stcell " data-label="P"><?php if(!empty($present=$sum['P'])){ echo $present; } else{ echo 0; }?></span>
    <span class="cell stcell " data-label="O"><?php if(!empty($O=$sum['O'])){ echo $present; } else{ echo 0; }?></span>
	<span class="cell stcell " data-label="R"><?php if(!empty($R=$sum['R'])){ echo $present; } else{ echo 0; }?></span>
	<span class="cell stcell " data-label="L"><?php if(!empty($L=$sum['L'])){ echo $present; } else{ echo 0; }?></span>
	<span class="cell stcell " data-label="L"><?php if(!empty($H=$sum['H'])){ echo $present; } else{ echo 0; }?></span>
	<span class="cell stcell " data-label="D"><?php $roster=Modules::run('attendance/attrosta',$mydate,urlencode($sum['ihris_pid'])); ?><?php echo $day=$roster['Day'][0]->days; ?></span>
	<span class="cell stcell " data-label="E"><?php echo $eve=$roster['Evening'][0]->days; ?></span>
	<span class="cell stcell " data-label="N"><?php echo $night=$roster['Night'][0]->days;?></span>
	<span class="cell stcell " data-label="Percentage Pr"><?php $per= round(($present/($day+$night+$eve))*100,1); if(is_infinite($per)||is_nan($per)){ echo  0; } else{ echo $per; } ?> % </span>
</div>
<?php
$no++; 
} ?>
</div>
</div></div>
<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
</div>
</div>
</div>
</section>


<script type="text/javascript">
var url=window.location.href;
if(url=='<?php echo base_url(); ?>attendance/attendance_summary'){
	$('.sidebar-mini').addClass('sidebar-collapse');
}
$('.csv').click(function(e){
    e.preventDefault();
    $.ajax({
        url:'<?php echo base_url(); ?>attendance/attsums_csv',
        success:function(res){
            console.log(res);
        }
    })
})
</script>
