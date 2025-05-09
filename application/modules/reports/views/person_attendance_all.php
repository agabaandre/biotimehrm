<style>
.cnumber {
    width: 3%;
}

.cname {
    text-align: left;
    padding-left: 1.5em;
    width: 30%;
}

@media only screen and (max-width: 980px) {
    .cnumber {
        width: 100%;
    }

    .cname {
        padding-left: 0em;
        text-align: left;
        width: 100%;
    }

    .print {
        display: none;
    }
}
</style>
<section class="content">
    <div class="container-fluid">
        <!-- Main row -->
        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-success">


                    <form class="form-horizontal" style="padding-bottom: 2em;"
                        action="<?php echo base_url(); ?>reports/person_attendance_all" method="get">
                        <div class="row">
                            <div class="col-md-2">

                                <div class="control-group">

                                    <input type="hidden" id="month" value="<?php echo @$search->month; ?>">

                                    <select class="form-control select2" name="month" onchange="this.form.submit()">

                                        <option value="<?php echo @$search->month; ?>">
                                            <?php echo strtoupper(date('F',  strtotime('2022-' . @$search->month . '-01'))) . "(Showing below)"; ?>
                                        </option>

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

                                    <select class="form-control" name="year" onchange="this.form.submit()">

                                        <?php for ($i = -3; $i <= 25; $i++) {  ?>

                                        <option <?php echo (@$search->year == 2017 + $i) ? "selected" : ""; ?>>
                                            <?php echo 2017 + $i; ?>
                                        </option>

                                        <?php }  ?>
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="control-group">
                                    <select class="form-control select2" name="facility_name"
                                        onchange="this.form.submit()">
                                        <option value="">All</option>
                                        <?php foreach ($facilities as $key => $value) : ?>
                                        <option value="<?php echo $value->facility; ?>"
                                            <?php echo (@$search->facility_name == $value->facility) ? "selected" : ""; ?>>
                                            <?php echo $value->facility; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="control-group">
                                    <select class="form-control select2" name="district" onchange="this.form.submit()">
                                        <option value="">All</option>
                                        <?php foreach ($districts as $key => $value) : ?>
                                        <option value="<?php echo $value->district; ?>"
                                            <?php echo (@$search->district == $value->district) ? "selected" : ""; ?>>
                                            <?php echo $value->district; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                </div>
                            </div>


                        </div>

                        <div class="row mt-2">
                            <div class="col-md-3">
                                <select class="form-control select2" name="rows" onchange="this.form.submit()">

                                    <?php
									$count = 0;
									for ($i = 15; $i <= 205; $i++) {  ?>

                                    <option value="<?php echo $i; ?>"
                                        <?php echo (@$search->rows == $i) ? "selected" : ""; ?>>
                                        <?php echo ($count == 0) ? "Show " . $i . " rows" : $i; ?>
                                    </option>

                                    <?php $count++;
									}  ?>
                                </select>
                            </div>

                            <div class="col-md-3">

                                <div class="control-group">

                                    <button type="submit" name="" class="btn bg-gray-dark color-pale"
                                        style="font-size:12px;">Apply</button>
                                    <?php
									if (count($records) > 0) {
									?>
                                    <!--<a href="<?php echo base_url() ?>attendance/print_attrowmary/<?php echo @$search->year . "-" . @$search->month; ?>" style="font-size:12px;" class="btn bg-gray-dark color-pale" target="_blank"><i class="fa fa-print"></i>Print</a>-->

                                    <a href="<?php echo full_url('csv=1'); ?>" style="font-size:12px;"
                                        class="btn bg-gray-dark color-pale"><i class="fa fa-file"></i> Export CSV</a>
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

            <div class="col-md-3" style="border-right: 0; border-left: 0; border-top: 0;"><img
                    src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"></div>
            <div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0;">
                <p style="font-size: 16px; font-weight:bold; margin:0 auto; ">
                    <?php
					if (count($records) < 1) {
						echo "<font color='red'> No Schedule Data</font>";
					} else {
					?>
                    MONTHLY ATTENDANCE TO DUTY SUMMARY
                    <?php echo " - "  . date('F, Y', strtotime(@$search->year . "-" . @$search->month));
					}

					?>
                </p>
            </div>
            <div id="table">
                <div class="header-row tbrow">
                    <span class="cell stcell  tbprimary cnumber"># <b id="name"></b></span>
                    <span class="cell stcell">Name</span>
                    <span class="cell stcell">District</span>
                    <span class="cell stcell">Facility</span>
                    <span class="cell stcell">Period</span>
                    <span class="cell stcell ">Present</span>
                    <span class="cell stcell ">Off Duty</span>
                    <span class="cell stcell ">Official Request</span>
                    <span class="cell stcell ">Leave</span>
                    <span class="cell stcell ">Holiday</span>
                    <span class="cell stcell ">Absent</span>
                    <span class="cell stcell ">% Absenteeism</span>
                </div>
               <?php

$mydate = @$search->year . "-" . @$search->month;
$no = (!empty($this->uri->segment(3))) ? $this->uri->segment(3) : 1;

$total_present  = 0;
$total_leave    = 0;
$total_official = 0;
$total_off      = 0;
$total_holiday  = 0;
$total_absent   = 0;
$total_supposed = 0;

foreach ($records as $row) {

    $dates = explode("-", $row->duty_date);
    $month_days  = cal_days_in_month(CAL_GREGORIAN, $dates[1], $dates[0]);

    // Check if $supposed_days is set and not zero
    if (!empty($supposed_days) && $supposed_days > 0) {
        $absentism_rate  = ($row->days_absent / $supposed_days) * 100;
    } else {
        $absentism_rate  = 0; // Default value when division is not possible
    }

    // Continue with the rest of your code
?>

                <div class="table-row tbrow strow">
                    <input type="radio" name="expand" class="fa fa-angle-double-down trigger">
                    <span class="cell stcell" data-label="#">
                        <?php echo $no; ?>
                    </span>
                    <span class="cell stcell" data-label="Name">
                        <?php echo $row->fullname; ?>
                    </span>
                    <span class="cell stcell" data-label="District">
                        <?php echo $row->district; ?>
                    </span>
                    <span class="cell stcell" data-label="Facility">
                        <?php echo $row->facility_name; ?>
                    </span>
                    <span class="cell stcell" data-label="Period">
                        <?php echo $row->duty_date; ?>
                    </span>
                    <span class="cell stcell " data-label="Present">
                        <?php echo $row->P; ?>
                    </span>
                    <span class="cell stcell " data-label="Off Duty">
                        <?php echo $row->O; ?>
                    </span>
                    <span class="cell stcell " data-label="Official Request">
                        <?php echo $row->R; ?>
                    </span>
                    <span class="cell stcell " data-label="Leave">
                        <?php echo $row->L; ?>
                    </span>
                    <span class="cell stcell " data-label="Holiday">
                        <?php echo $row->H; ?>
                    </span>

                    <span class="cell stcell " data-label="% Present"><?php echo $absent = $month_days - ($row->P + $row->O + $row->R + $row->L);
																			?></span>
                    <span class="cell stcell "
                        data-label="% Absent"><?php echo number_format(($absent / $month_days), 1) * 100 ?>%</span>

                </div>
                <?php
					$no++;
				}


				$total_supposed_days = $total_supposed;
				$toal_days_worked         = $total_supposed - $total_absent;

				$total_attendance_rate = ($toal_days_worked / $total_supposed_days) * 100;
				$total_absentism_rate  = ($total_absent / $total_supposed_days) * 100;

				?>

                <div class="header-row tbrow">
                    <span class="cell stcell  tbprimary cnumber"># <b id="name"></b></span>
                    <span class="cell stcell"></span>
                    <span class="cell stcell "><?php ?> </span>
                    <span class="cell stcell "><?php ?></span>
                    <span class="cell stcell "><?php  ?></span>
                    <span class="cell stcell "><?php  ?></span>
                    <span class="cell stcell "><?php  ?> </span>
                    <span class="cell stcell "><?php ?> </span>
                    <span class="cell stcell "><?php ?></span>
                    <span class="cell stcell "><?php  ?></span>
                    <span class="cell stcell "><?php ?></span>
                    <span class="cell stcell "><?php ?></span>
                </div>

            </div>
        </div>
    </div>
    <div class="row pull-right" style="padding: 0.5rem;"> <?php echo $links; ?> </div>
    </div>
    </div>
    </div>
</section>


<script type="text/javascript">
var url = window.location.href;

if (url == '<?php echo base_url(); ?>reports/attendance_aggregate') {
    $('.sidebar-mini').addClass('sidebar-collapse');
}

$('.csv').click(function(e) {
    e.preventDefault();
    $.ajax({
        url: '<?php echo base_url(); ?>attendance/attrows_csv',
        success: function(res) {
            console.log(res);
        }
    })
})
</script>
