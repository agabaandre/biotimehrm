 
<?php 


$departs="";  //to store department options

foreach ($departments as $department) {
	
	if(!empty($department->department))
	{
	$departs.="<option value'".$department->department."''>".$department->department."</option>";
   }
}


//print_r($this->session->userdata);

?>
<!-- Contains page content -->
<div class="container-fluid">
      <div class="row">
          <div class="col-lg-12">
              <div class="panel panel-default">
                <div class="panel-body" style="overflow-x: scroll;">
				
				<div class="callout callout-success">
             

                	<form class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo base_url(); ?>rosta/fetch_report" method="post">
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

								    <?php 
									if(count($duties)>=0)
									{

									?>
										<a href="<?php echo base_url() ?>rosta/print_roster/<?php echo $year."/".$month; ?>" class="btn bg-gray-dark color-pale" target="_blank" ><i class="fa fa-print"></i>Print</a>

									<?php   } ?>
								</div>

							</div>
					</div>
						</form>


			    

			    

 </div>

						
<div class="callout callout-success">
		<p class="" style="text-align: center; margin-top: 5px; font-weight:bold; font-size: 1rem;"> Duty Roster Key</p>
		<hr style="color:#15b178;">
		<?php  $colors=Modules::run('schedules/getrosterKey'); ?>
		<div class="col-lg-12" style="text-align:center;">
		<p style="text-align:center; font-weight:bold; font:14rem;"></p>

		<?php foreach ($colors as $color) { ?>
		<button type="button" class="btn btn-sm btnkey bg-gray-dark color-pale" ><?php echo $color->schedule;?> (<?php echo $color->letter;?>)
		</button>  
		<?php  }?>
</div>
</div>
				<style>
                      .btnkey{
                      min-width:100px;;
                      color:#fff;
                      margin:2px;
					  font-size: 11px;
					  overflow:hidden;
                              }
					.tabtable {
					zoom: 85%;
					}
                    @media only screen and (max-width: 600px) {
                       .btnkey{
                       width:100%;
                              }
                              }

                </style>

							<?php 
							 function isWeekend($date) {

								$day=intval(date('N', strtotime($date)));
							   
								if($day>= 6){
								return 'yes';
								};
							   
								return 'no';
							   }
							?>
						    
			<div class="row">
            
			<div class="col-md-12">
			 	<img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px" style="float:left;">
			</div>

					<div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0; text-align:center;">
								<h4>
						<?php 
						if(count($duties)<1 || $duties[0]['facility']=='')
						{

						echo "<font color='red'> No Schedule Data</font>";
						}
						else{

						?>
						<p style="text-align:center; font-weight:bold; font-size:20px;">
	
							MONTHLY DUTY ROSTER FOR 

							<?php
							echo " - ".$_SESSION['facility_name'];

							echo "              ".date('F, Y',strtotime($year."-".$month));

	                    ?></p>


						<?php } ?>

							</h4></div>
							<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> 
							<form method="post" action="<?php echo base_url();?>rosta/fetch_report">
							<input type="hidden" name='all' value="all">
                        <!-- <button class="btn btn-success" type="submit" >Show All</button> -->
						<form> 
						</div> 

						<div id="table">   

							<div class="header-row tbrow ">
							    <span class="cell stcell  stcell tbprimary" style="width: 30px;"># <b id="name"></b></span>
							    <span class="cell stcell " style="width: 140px;">Name</span>
							    <span class="cell stcell " style="width: 0px;">Position</span>

								<?php 
								
								$incomplete=0; //checks whether scheduling for this month has been fully done

								$monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $year); // get days in a month

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
							</div>

							
							
							<?php 

							$no=0;

							foreach($duties as $singleduty) { 

								$no++;

								if($singleduty['ihris_pid']!=''){//if id is not empty

								?>

							<div class="table-row tbrow strow">
							    <input type="radio" name="expand" class="fa fa-angle-double-down">
								<span class="cell stcell  stcell tbprimary" data-label="#"><?php echo $no;?>. <b id="name"><a href=""><?php echo $singleduty['fullname'];?></a></b>
								</span>
								<span class="cell stcell " data-label="NAME" ><a href=""><?php echo $singleduty['fullname'];?></a></span>
								<span class="cell stcell " data-label="POSITION" ><?php $words=explode(" ",$singleduty['job']);

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
								<span class="cell stcell " data-label="<?php echo ucwords($day); ?>" >
								<?php if($singleduty[$day]!='')
								{

									echo $matches[$singleduty[$day].$singleduty['ihris_pid']]; //$matches['key'=>value] e.g $matches[2017-11-01person|005=>N] and pulling out for N
								}
								
								else{
							    
							    //some day wasn't scheduled
							    
							    $incomplete +=1;
							    
							    
								}

								?></span>


								<?php }


								}//end for id not empty ?> 
								
							</div>

							<?php } ?>

						</div>
						<div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> 
					   
					
					  </div> 
						

						</div>
						

					</div>

              </div>
          </div>
      </div>
    </div>
</div>



<script type="text/javascript">

var checkdone= '<?php echo $incomplete; ?>';

if(checkdone>0){
    
    $('.done').hide();
}
else{
    
   $('.done').show(); 
}
	

var url=window.location.href;

if(url=='<?php echo base_url(); ?>rosta/fetch_report' || url=='<?php echo base_url(); ?>rosta/fetch_report#' ){


	$('.fixed-top').addClass('mini-navbar');
}



</script>
