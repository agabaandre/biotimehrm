
<html>
<head>
    <title>Rota Report</title>
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
	<td colspan=4 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></td>

	<td colspan=30 style="border-right: 0; border-left: 0; border-top: 0;">
		<h2>

	MONTHLY DAILY ATTENDANCE REPORT FOR HEALTH PERSONNEL<br>

		<?php
		 echo $duties[0]['facility']."<br>"; 

		echo "   ".date('F, Y',strtotime(date($dates)));

		?>

	</h2></td>
</tr>

<tr>          

	<th>#</th>
	<th >Name</th>
	<th>Position</th>
	<th>01</th>
	<th>02</th>
	<th>03</th>
	<th>04</th>
	<th>05</th>
	<th>06</th>
	<th>07</th>
	<th>08</th>
	<th>09</th>
	<th>10</th>
	<th>11</th>
	<th>12</th>
	<th>13</th>
	<th>14</th>
	<th>15</th>
	<th>16</th>
	<th>17</th>
	<th>18</th>
	<th>19</th>
	<th>20</th>
	<th>21</th>
	<th>22</th>
	<th>23</th>
	<th>24</th>
	<th>25</th>
	<th>26</th>
	<th>27</th>
	<th>28</th>
	<th>29</th>
	<th>30</th>
	<th>31</th>
</tr> 	

</thead>

<tbody>



	
<?php 

//color the duty date according to whether one worked

	function dayColor($day,$dayletter,$leaves) {

		//if they did not work in past days

		if(strtotime($day) <= strtotime(date('Y-m-d'))){
		    
		    
		    if(in_array($dayletter,$leaves)){
		        
		        $color="#000";
		        
		    }
				else{
				    
					$color="red";
				}


				}

				//if they are scheduled to work

		if(strtotime($day) > strtotime(date('Y-m-d'))){
																		
					$color="navy";
				}

				$font="<font color='".$color."'><b>";

				return $font;

			}//color
			



$no=0;

//$nonworkables contains non duty days
//$workeddays contains  worked days

foreach($duties as $singleduty) { 

	$no++;

	?>

<tr >
	<td class='cost'><?php echo $no;?></td>
	<td class='cost'><?php echo $singleduty['fullname'];?></td>
	<td class='cost'><?php $words=explode(" ",$singleduty['job']);

	$letters="";

	foreach ($words as $word) {

		$letters.=$word[0];
	}

	echo $letters;

	?></td>

<?php 

$month_days=date('t');//days in a month

for($i=1;$i<=$month_days;$i++){// repeating td

$day="day".$i;  //changing day

$was_present="<font color='green'><b>P</b></font>"; //employee worked

$leave_taken="<font color='green'><b>" ;//employee took leave

?>

	<td class='cost'>

		<?php if($singleduty[$day]!='')
	{
		$dayentry=$singleduty[$day].$singleduty['ihris_pid'];  //duty letter for day

		
		
			if(!in_array($dayentry,$nonworkables) && in_array($dayentry,$workeddays) ){

		echo $was_present; //employee did not work  on a their duty day
	}

		else if( (!in_array($dayentry,$nonworkables) && !in_array($dayentry,$workeddays)) ){

			$coloredday=dayColor($singleduty[$day],$dayentry,$nonworkables); //.coloring the letter comes with <font color="color"><b>

		echo $coloredday.$matches[$dayentry]."</b></font>"; //employee worked/ is scheduled to work their duty day...coloring the letter
	}

		else if( in_array($dayentry,$nonworkables) && !in_array($dayentry,$workeddays) ){

		echo $leave_taken.$matches[$dayentry]."</b></font>";//employee took their leave day with green color
	}

	else if(in_array($dayentry,$nonworkables) && in_array($dayentry,$workeddays) ){

		echo $was_present; //user worked on a leave day
	}





	}

	;?></td>


	<?php }//repeat days ?>

	

</tr>

<?php } ?>



</tbody>





</table>

</body>

</html>

