<?php

date_default_timezone_set('Africa/Kampala');
        //Enter your code here, enjoy!
function isWeekend($date) {
 $day=intval(date('N', strtotime($date)));
 return ($day>= 6);
}
$year=date('Y');
for($m=12;$m<=12;$m--){
    
$month_days = cal_days_in_month(CAL_GREGORIAN, $m,$year); 

for($d=1;$d<=$month_days;$d++){//

$dayDate = $year."-".$m."-".$d;

if (isWeekend($dayDate)){
    
    echo $dayDate."<br>Yeah, go ahead and rest";
}
else{
    echo $dayDate."<br>Go work";
}

}//

}