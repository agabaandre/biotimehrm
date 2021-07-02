<style>

    @media only screen and (max-width: 980px)  {
    .field{
        
        height:2em;
        width:5em;
        margin-top:1em;
    }
    
    }
    
</style>

<!-- Contains page content -->
<div class="dashtwo-order-area" style="padding-top: 10px;">
    <div class="container-fluid">
        <div class="row">
        	<div class="col-md-12">
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
					}
					//color
					if(count($workinghours)>0){ ?>
				<?php } ?>
			</div>
            <div class="col-lg-12">

                <div class="panel panel-default">
                 
                   
                  
                  <div class="panel-body" style="overflow-x: scroll;">
                   
					<div class="col-md-12" style="padding-bottom:0.5em;">
					
					<div class="callout callout-success">
             

			 <form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>employees/timesheet" method="post">
			 <div class="row">
					 <div class="col-md-3">

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


					 <div class="col-md-3">
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
					 <div class="col-md-3">
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

					 <div class="col-md-3">

						 <div class="control-group">

							 <button type="submit" name=""  class="btn bg-gray-dark color-pale" style="font-size:12px;">Apply</button>
							 <button type="submit" name=""  class="btn bg-gray-dark color-pale" style="font-size:12px;">Print</button>

						 </div>
						 <?php //echo $this->uri->segment(2); ?>
					 </div>
					
			 </div>
				 </form>

	  </div>
					</div>

					
<h4 class="panel-title">	MONTHLY TIMESHEET 	

<?php
echo " - ".$_SESSION['facility_name']." "; 

echo "              ".date('F, Y',strtotime($year."-".$month));

?>
</h4>
				<?php if(count($workinghours)>0){ ?> 

<?php //print_r($workinghours['0']); ?>

					<br><br>
					<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
						<div id="table" >   

						<div class="header-row tbrow ">
						    <span class="cell tbprimary"><b id="name"></b>#</span>
						    <span class="cell" style="width:10%;"  >Name</span>
							<span class="cell">Position</span>
							
							<?php 

							//print_r($workinghours);

									$monthh=date('m');

									$yearh=date('Y');

									$monthdays = cal_days_in_month(CAL_GREGORIAN, $monthh, $yearh); // get days in a month


									for($i=1;$i<($monthdays+1);$i++)
									{		
										    
										    $dy=$i;

											if($i<10){
												$dy="0".$i;
											}

											$wekday=$year."-".$month."-".$dy;
											 
											if(isWeekend($wekday)=='yes'){
												$color="red";
											}
											else{
												$color="";
											}

							?>

								<span class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $i; ?></span>
							
							<?php } ?>
							<span class="cell" style="width:10%;">Total</span>
							<!-- <span class="cell" style="width:10%;">Expected Hours</span>
							<span class="cell" style="width:10%;">%ge Presence</span> -->

						</div>

												
						<?php 
						$no=0;

						//$nonworkables contains non duty days
						//$workeddays contains  worked days

						foreach($workinghours as $hours) { 
							$personhrs=array();

							$no++;

							?>

						<div class="table-row tbrow">
						   <span class="cell" data-label="No" ><?php echo $no;?></span>
						   <span class="cell" data-label="Name" style="text-align:left; padding-left:1em;" >
						   	<?php echo $hours['fullname'];?>

						   </span>
						   <span class="cell" data-label="Position" ><?php $words=explode(" ",$hours['job']);

								$letters="";

								foreach ($words as $word) {

									$letters.=$word[0];
								}

								echo $letters;

								?>
								</span>
						

						<?php 

							$month_days=date('t');//days in a month

							for($i=1;$i<=$month_days;$i++){// repeating td

							$day="day".$i;  //changing day 
						?>

						<span class="cell" data-label="Day<?php echo $i; ?>" >
							<?php 

								 $hours_data =$hours[$day]; 


								 if(!empty($hours_data))
								 {

									 $Time_data= array();
								     $Time_data=explode("|",$hours_data);


								      $starTime=@$Time_data[0];
								      $endTime=@$Time_data[1];

								      $initial_time = strtotime($starTime)/ 3600;
									  $final_time = strtotime($endTime)/ 3600;

									  if(empty($initial_time)|| empty($final_time)){ 
										$hours_worked=0; 
									  } 
									  elseif($initial_time==$final_time){ 
										$hours_worked=0; 
									  } 
									  else{
									   
										$hours_worked = round(($final_time - $initial_time),1);   
										
									  }

									if ($hours_worked<0){ 
										echo $hours_worked=$hours_worked*-1; 
									} 
									elseif ($hours_worked==-0){ 
										echo $hours_worked=0; 
									} 
									else { 
										echo $hours_worked; 
									} 
									array_push($personhrs,$hours_worked);
										
							 	}

							?>
							
							</span>
							
							
							<?php }//repeat days ?>

							<span class="cell" style="width:5%;"><?php  echo array_sum($personhrs); ?></span>
							<!-- <span class="cell" style="width:10%;">Expected Hours</span>
							<span class="cell" style="width:10%;">%ge Presence</span> -->

						</div>

						<?php } ?>
						

						</div>

						<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>

						<?php }
							else{  
								echo "<center><span style='color: red;' >No Timesheet for this Month!</span></center>";
							} 
						?> 
					</div>
				</div>

			</div>
		</div>
	</div>
</div>


