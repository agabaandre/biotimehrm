
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
</style>
</head>
<body>



<table width="100%" class="items" style="font-size: 12pt; border-collapse: collapse; " cellpadding="8">


<thead>
<tr style="border-right: 0; border-left: 0; border-top: 0;">
	<td colspan=4 style="border-right: 0; border-left: 0; border-top: 0;"><img src="<?php echo base_url(); ?>assets/images/MOH.png" width="100px"></td>

	<td colspan=30 style="border-right: 0; border-left: 0; border-top: 0;">
		<h2>

	MONTHLY DUTY ROSTER FOR HEALTH PERSONNEL<br>

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

$no=0;

foreach($duties as $singleduty) { 

	$no++;

	?>

<tr>
	<td class='cost'><?php echo $no;?></td>
	<td class='cost'><?php echo $singleduty['fullname'];?></td>
	<td>
		
<?php $words=explode(" ",$singleduty['job']);

	$letters="";

	foreach ($words as $word) {

		$letters.=$word[0];
	}

	echo $letters;

	?>

	</td>
	<td class='cost'><?php if($singleduty['day1']!='')
	{

		echo $matches[$singleduty['day1'].$singleduty['ihris_pid']]; //$matches['key'=>value] e.g $matches[2017-11-01person|005=>N] and pulling out for N
	}

	;?></td>


	<td class='cost'><?php if($singleduty['day2']!='')
	{

		echo $matches[$singleduty['day2'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day3']!='')
	{

		echo $matches[$singleduty['day3'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day4']!='')
	{

		echo $matches[$singleduty['day4'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day5']!='')
	{

		echo $matches[$singleduty['day5'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day6']!='')
	{

		echo $matches[$singleduty['day6'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day7']!='')
	{

		echo $matches[$singleduty['day7'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day8']!='')
	{

		echo $matches[$singleduty['day8'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day9']!='')
	{

	echo $matches[$singleduty['day9'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day10']!='')
	{
	
	echo $matches[$singleduty['day10'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day11']!='')
	{

		echo $matches[$singleduty['day11'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day12']!='')
	{

		echo $matches[$singleduty['day12'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day13']!='')
	{

		echo $matches[$singleduty['day13'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day14']!='')
	{

		echo $matches[$singleduty['day14'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day15']!='')
	{

		echo $matches[$singleduty['day15'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day16']!='')
	{

		echo $matches[$singleduty['day16'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day17']!='')
	{

		echo $matches[$singleduty['day17'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day18']!='')
	{

		echo $matches[$singleduty['day18'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day19']!='')
	{

		echo $matches[$singleduty['day19'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day20']!='')
	{

		echo $matches[$singleduty['day20'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day21']!='')
	{

		echo $matches[$singleduty['day21'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day22']!='')
	{

		echo $matches[$singleduty['day22'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day23']!='')
	{

		echo $matches[$singleduty['day23'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day24']!='')
	{

		echo $matches[$singleduty['day24'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day25']!='')
	{

		echo $matches[$singleduty['day25'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day26']!='')
	{

		echo $matches[$singleduty['day26'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day27']!='')
	{

		echo $matches[$singleduty['day27'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day28']!='')
	{

		echo $matches[$singleduty['day28'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day29']!='')
	{

		echo $matches[$singleduty['day29'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day30']!='')
	{

		echo $matches[$singleduty['day30'].$singleduty['ihris_pid']];
	}

	;?></td>

	<td class='cost'><?php if($singleduty['day31']!='')
	{

		echo $matches[$singleduty['day31'].$singleduty['ihris_pid']];
	}

	;?></td>


	

</tr>

<?php } ?>

<tr><td colspan="33"><p>A=Annual Leave, D=Day, N=Night, E=Evening, O=Off-duty,S=Study leave,M=Maternity leave,Z=Other leave</p></td></tr>



</tbody>





</table>



</body>

</html>
