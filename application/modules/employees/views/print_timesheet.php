
<html>
<head>
    <title>Time Sheet</title>
<style>
body {font-family: Arial;
	font-size: 12pt;
	max-width:21cm;
	max-height:29.7cm;
}
p {	margin: 0pt; }
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
		
		$allcols= cal_days_in_month(CAL_GREGORIAN, $month, $year); 

		
		?>

		<td colspan=<?php echo $allcols; ?> style="border-right: 0; border-left: 0; border-top: 0;">
			<h2>

		MONTHLY TIMESHEET ATTENDANCE REPORT 

			<?php
			$dates=$year.'-'.$month;
			 echo $_SESSION['facility_name']; 

			echo "   ".date('F, Y',strtotime(date($dates)));

			?>

		</h2></td>
    </tr>

<tr>          

	
	<th >Name</th>
	<th>Position</th>
	<?php 



		function isWeekend($date) {

		$day=intval(date('N', strtotime($date)));

		if($day>= 6){
			return 'yes';
		};

		return 'no';
		}

	$monthdays = cal_days_in_month(CAL_GREGORIAN, $month, $year); // get days in a month

	
    for($i=1;$i<($monthdays+1);$i++)
    {		
            
            $dy=$i;

            if($i<10){
                $dy="0".$i;
            }

            $wekday=$year."-".$month."-".$dy;
             
            if(isWeekend($wekday)=='yes'){
                $color="#7a0404; color:#FFFFFF";
            }
            else{
                $color="";
            }

?>

<th class="cell" style="padding:0px; text-align: center; border: 1px solid; background-color: <?php echo $color; ?>"><?php echo $i; ?></th>

<?php } ?>
<th class="cell" style="width:10%;">Hours</th>
<th class="cell" style="width:10%;">Days</th>
<span class="cell" style="width:10%;">% Present</span>
</tr> 	

</thead>

<tbody>



	
<?php 





$no=0;

//$nonworkables contains non duty days
//$workeddays contains  worked days

foreach($workinghours as $hours) { 
	$personhrs=array();

	$no++;

	?>

<tr >
	
	<td class='cost' style="text-align:left;"><?php echo $hours['fullname'].' '.$hours['othername'];?></td>
	<td class='cost'><?php $words=explode(" ",$hours['job']);

	$letters="";

	foreach ($words as $word) {

		$letters.=$word[0];
	}

	echo $letters;

	?></td>

						<?php 

							$month_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);//days in a month

							for($i=1;$i<=$month_days;$i++){// repeating td

							$day="day".$i;  //changing day 
						?>

						<td class="cell" data-label="Day<?php echo $i; ?>" >
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
							</td>
							
						
							
							
							<?php }//repeat days ?>

							<td class="cell" style="width:5%;"><?php  echo array_sum($personhrs); ?></td>
							<?php
							   $mydate=$year."-".$month;
							  $roster=Modules::run('attendance/attrosta',$mydate,urlencode($hours['ihris_pid']));
							  $day=$roster['Day'][0]->days;
							  $eve=$roster['Evening'][0]->days;
							  $night=$roster['Night'][0]->days;
							?>
							<td class="cell" style="width:5%;"><?php  echo $workedfor=count($personhrs)."/".$twdays=($day+$eve+$night); ?></td>
							<td class="cell" style="width:10%;"><?php echo round(($workedfor/$twdays)*100,1); ?></td>


	

</tr>

<?php } ?>



</tbody>





</table>

</body>

</html>

