
<?php 


$departs="";  //to store department options

foreach ($departments as $department) {
	
	if(!empty($department->department))
	{
	$departs.="<option value='".$department->department."'>".$department->department."</option>";
   }
}

?>



<div class="container-fluid">
        <div class="row">
        	<div class="col-md-4">
				<?php


					function isWeekend($date) {

					 $day=intval(date('N', strtotime($date)));

					 if($day>= 6){
					 return 'yes';
					 };

					 return 'no';
					}



					function dayState($day,$scheduled) {
					$user=$_SESSION['role'];
					//its today or day in the past
					if(strtotime($day) < strtotime(date('Y-m-d')) && !empty($scheduled) && $user!=='sadmin'){								
						$state="disabled";
					}
					else if(strtotime($day) < strtotime(date('Y-m-d')) && empty($scheduled) && $user!=='sadmin'){
						$state="";
					}
					//if they are scheduled to work
					if(strtotime($day) > strtotime(date('Y-m-d'))){								
						$state="disabled";
					}
					echo $state;
					}//color
					if(count($duties)>0)
					{
					?>
				<?php } ?>
			</div>
            <div class="col-lg-12">

                <div class="panel panel-default">
                  <div class="panel-body" style="overflow-x: scroll;">
                    <span  style="margin-left:0.6em;">
						<b class="" style="font-weight:bold;font:1.2em;">Legend</b>
						<p class="legend" style="margin:4px;">
					
						<b class="ltab bg-gray-dark">P=Present </b> 
						<b class="ltab bg-gray-dark"> O=Off Duty </b>
						<b class="ltab bg-gray-dark"> R=Official Request </b>
						<b class="ltab bg-gray-dark"> L=Leave </b>
					
						</p>
					</span>
					<hr>
					<div class="col-md-12" style="padding-bottom:0.5em;">
						<form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>rosta/attfrom_report" method="post">
						   <div class="row">
							<div class="col-md-3">

								<div class="control-group">
									<input type="hidden" id="month" value="<?php echo $month; if(!empty($month)){$month=$month;}else{$month=date('Y');}; ?>">
									<select class="form-control select2" name="month" onchange="this.form.submit()" >
										
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

								<?php $employees=Modules::run("employees/get_employees"); ?>
									<select class="form-control select2" name="empid" select2>
											<option value="NULL" selected disabled>Select Employee</option>

											<?php foreach($employees as $employee){  ?>

											<option value="<?php echo $employee->ihris_pid ?>"><?php echo $employee->surname.' '.$employee->firstname.' '.$employee->othername;?></option>

											<?php }  ?>
									</select>
				       
								</div>
							</div>

							<div class="col-md-3">
								<div class="control-group">
									<input type="hidden" id="year" value="<?php echo $year; if(!empty($year)){$year=$year;}else{$year=date('Y');}; ?>">
									<select class="form-control select2" name="year" onchange="this.form.submit()">
										<option><?php echo $year; ?></option>

											<?php for($i=-5;$i<=25;$i++){  ?>

										<option><?php echo 2017+$i; ?></option>

												<?php }  ?>
									</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="control-group">
									<input type="submit" name="" value="Load Month" class="btn bg-gray-dark color-pale">
								</div>
						    </div>
						</div>
						</form>
					</div>
				<div class="col-md-12">

					<div class="panel">

						<div class="panel-header">

							<div class="col-md-2">
									<!-- <?php 
								if(count($duties)>0){

									  $incomplete=0;
									   
									?> -->
									<a href="<?php echo base_url() ?>rosta/print_actuals/<?php echo $year."-".$month; ?>" class="done btn btn-success btn-sm" target="_blank">
									<i class="fa fa-print"></i> Print </a>
										<!-- <?php    } ?>	 -->
								</div>


					<div class="col-md-3"  style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px">
					</div>

					<div class="col-md-8"  style="border-right: 0; border-left: 0; border-top: 0;">
						<h4>
								<?php 
							if($duties[0]['facility']==''){

								echo "<font color='red'> No Schedule Data</font>";
							}
							else{

								?>
								MONTHLY ACTUAL ATTENDANCE REPORT FOR HEALTH PERSONNEL

								<?php
								 echo " - ".$duties[0]['facility']."<br>"; 

								echo "              ".date('F, Y',strtotime($year."-".$month));


								//print_r($nonworkables);

								?>

								<?php 
							} ?>
						</h4>
					</div>
					
					
				<div id="table">   

				<div class="header-row tbrow">
				    <span class="cell stcell tbprimary"># <b id="name"></b></span>
				    <span class="cell">Name</span>
					<span class="cell">Position</span>

					<?php 
						$monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $year); // get days in a month

						for($i=1;$i<($monthdays+1);$i++)
						{
						?>

				<span class="cell" style="padding:0px; text-align: center; border: 1px solid;"><?php echo $i; ?></span>
					
					<?php } ?>
					

				</div>	
				<?php 
				$no=0;

				//$nonworkables contains non duty days
				//$workeddays contains  worked days

				foreach($duties as $singleduty) { 

					$no++;

					if($singleduty['ihris_pid']!=''){//if id is not empty


					?>
				<div class="table-row tbrow  strow">
				    <input type="radio" name="expand" class="fa fa-angle-double-down">
					<span class="cell stcell tbprimary" data-label="#"><?php echo $no;?>. <b id="name"><a href=""><?php echo $singleduty['fullname'];?></a></b>
				</span>
				<span style="text-align:left; padding-left:1em;" class="cell" data-label="NAME" ><a href=""><?php echo $singleduty['fullname'];?></a></span>
				<span class="cell" data-label="POSITION" >
					
					<?php $words=explode(" ",$singleduty['job']);

					$letters="";

					foreach ($words as $word) {

						$letters.=$word[0];
					}

					echo $letters;

					?></span>

				<?php 

					$month_days=$monthdays;//days in a month

					for($i=1;$i<=$month_days;$i++){// repeating td

					$day="day".$i;  //changing day
				?>

				<span class="cell" data-label="<?php echo ucwords($day); ?>">

						<?php if($singleduty[$day]!='')
					{

						$d=$i;

				if($d<10){

					$d="0".$d;
				}
						$dayentry=$singleduty[$day].$singleduty['ihris_pid'];  //entry id

						$ddate=trim(date("Y-m")."-".$d);
				 echo $actuals[$dayentry]; 

				?>
					<?php }
				else{
				    
				    //some day wasn't scheduled
				    
				     echo ""; //show nothing
				     
				    $incomplete +=1;
				}

				echo "</span>";


					

					}//repeat days


				}//if id is set

					 ?>

				</div>

				<?php } ?>
				</div>
					<?php echo $links; ?>

				</div></div>
				</div>

              </div>
          </div>
      </div>
    </div>
</div>



<script type="text/javascript">
	
	var checkdone= <?php echo $incomplete; ?>;

if(checkdone>0){
    
    $('.done').hide();
}
else{
    
   $('.done').show(); 
}


var url=window.location.href;

if(url=='<?php echo base_url(); ?>rosta/actualsreport'){


	$('.sidebar-mini').addClass('sidebar-collapse');
}


</script>
