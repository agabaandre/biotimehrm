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
					 



					 <div class="col-md-4">
						 <div class="control-group">

					

							 <select class="form-control select2" name="year" onchange="this.form.submit()">
							         <option disabled>SELECT YEAR</option>
									 <option ><?php echo date('Y'); ?></option>

									 <?php for($i=-5;$i<=25;$i++){  ?>

									 <option><?php echo 2017+$i; ?></option>

									 <?php }  ?>
							 </select>
				
						 </div>
					 </div>
					 	 <div class="col-md-4">

						 <div class="control-group">

							 <button type="submit" name=""  class="btn bg-gray-dark color-pale" style="font-size:12px;">Apply</button>
				
           
			<a href="<?php echo base_url() ?>reports/print_average/<?php echo $year?>" style="font-size:12px;" class="btn bg-gray-dark color-pale" target="_blank"><i class="fa fa-print"></i>Print</a>
				

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

	MONTHLY STAFF AVERAGE WOKRING HOURS
		<?php  echo " - ".$_SESSION['facility_name'];
		?>

	</p></div>
<div id="table">
<div class="header-row tbrow">
    <span class="cell stcell  tbprimary cnumber"># <b id="name"></b></span>
    <span class="cell stcell   cname">Month and Year</span>
	<span class="cell stcell">Average Hours</span>
   
	

</div>
<?php  $mydate=$year."-".$month ?>
<?php 
$no=1;

foreach($sums as $sum) {?>
<div class="table-row tbrow strow">
    <input type="radio" name="expand" class="fa fa-angle-double-down trigger">
    <span class="cell stcell  tbprimary" style="cursor:pointer;" data-label="#"><?php echo $no;?>
	<b id="name">. &nbsp;<span onclick="$('.trigger').click();"><?php echo $sum['month_year'];?></span></b>
</span>
    <span class="cell stcell  cname" data-label="Month"><?php echo $sum['month_year']?></span>
	<span class="cell stcell  cname" data-label="Hours"><?php echo $sum['avg_hours'] ?></span>
    
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
