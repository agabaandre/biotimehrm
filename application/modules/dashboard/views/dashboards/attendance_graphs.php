<!-- Attendance Graphs Grid -->
<style>
    .attendance-graphs-container {
        width: 100%;
    }
    .chart-card {
        height: 100%;
        margin-bottom: 1.5rem;
        display: flex;
        flex-direction: column;
    }
    .chart-card .card {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .chart-card .card-body {
        flex: 1;
        min-height: 400px;
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #line_graph_att {
        width: 100% !important;
        height: 400px !important;
        min-width: 0;
    }
    @media (max-width: 992px) {
        .chart-card .card-body {
            min-height: 350px;
        }
        #line_graph_att {
            height: 350px !important;
        }
    }
    @media (max-width: 768px) {
        .chart-card .card-body {
            min-height: 300px;
        }
        #line_graph_att {
            height: 300px !important;
        }
    }
</style>

<div class="attendance-graphs-container">
    <!-- Attendance per Month (full width) -->
    <div class="row">
        <!-- Attendance Chart -->
        <div class="col-12 mb-4">
            <div class="chart-card">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>Attendance per Month
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="line_graph_att" style="width:100%; height:400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Removed: Employees Scheduled per Month chart (per request) -->
</div>
<script src="<?php echo base_url() ?>assets/plugins/moment/moment.min.js"></script>
<script src="<?php echo base_url() ?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
<script type="text/javascript">
    // Wait for Highcharts to be fully loaded before using it
    function waitForHighcharts(callback) {
        if (typeof Highcharts !== 'undefined' && typeof Highcharts.setOptions === 'function' && typeof Highcharts.chart === 'function') {
            callback();
        } else {
            setTimeout(function() {
                waitForHighcharts(callback);
            }, 100);
        }
    }
    
    // Wait for both jQuery and Highcharts
    $(document).ready(function() {
        waitForHighcharts(function() {
            // Set Highcharts options only if Highcharts is available
            try {
                if (typeof Highcharts !== 'undefined' && typeof Highcharts.setOptions === 'function') {
                    Highcharts.setOptions({
                        colors: ['#28a745', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']
                    });
                }
            } catch(e) {
                console.error('Error setting Highcharts options:', e);
            }

            function refreshAttendanceGraph() {
                return $.ajax({
                type: 'GET',
                url: '<?php echo base_url('dashboard/graphsData') ?>',
                dataType: "json",
                timeout: 15000,
                cache: false,
                success: function (data) {
                    if (data.graph && data.graph.period && data.graph.data) {
                        if (typeof Highcharts !== 'undefined' && Highcharts.charts) {
                            var attChart = Highcharts.charts.find(function(chart) {
                                return chart && chart.renderTo && chart.renderTo.id === 'line_graph_att';
                            });
                            if (attChart) {
                                attChart.series[0].setData(data.graph.data);
                                attChart.xAxis[0].setCategories(data.graph.period);
                                if (data.graph.meta && data.graph.meta.mode === 'person') {
                                    attChart.update({
                                        title: { text: 'Days Present per Month (Selected Staff)' },
                                        yAxis: { title: { text: 'Days Present' } },
                                        series: [{ name: 'Days Present', data: data.graph.data }]
                                    }, true, false);
                                } else {
                                    attChart.update({
                                        title: { text: 'Average Daily Attendance (Unique Staff)' },
                                        yAxis: { title: { text: 'Avg Daily Staff' } },
                                        series: [{ name: 'Staff', data: data.graph.data }]
                                    }, true, false);
                                }
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Graphs data load error:', error);
                }
                });
            }

            // Expose a global hook so the dashboard filters can refresh the chart without reloading the page
            window.reloadAttendancePerMonth = function() {
                return refreshAttendanceGraph();
            };

            // Initial load
            refreshAttendanceGraph();

    <?php
    // Attendance per month uses actuals table (FY Jun->May)
    $graph = Modules::run("reports/attendanceActualsGraphData", ($this->session->userdata('year') ?: date('Y')), ($this->session->userdata('month') ?: date('m')), ($this->session->userdata('dashboard_empid') ?: ''));
    ?>
    
    // All Highcharts chart creation must be inside waitForHighcharts callback
    try {
        if (typeof Highcharts !== 'undefined' && typeof Highcharts.chart === 'function') {
        Highcharts.chart('line_graph_att', {
            chart: {
                type: 'line'
            },
            title: {
                text: '<?php echo (!empty($graph["meta"]["empid"])) ? "Days Present per Month (Selected Staff)" : "Average Daily Attendance (Unique Staff)"; ?>'
            },
            subtitle: {
                text: '<?php echo str_replace("'", " ", $_SESSION["facility_name"]); ?>'
            },
            xAxis: {
                categories: <?php echo json_encode($graph['period']); ?>
            },
            yAxis: {
                title: {
                    text: '<?php echo (!empty($graph["meta"]["empid"])) ? "Days Present" : "Avg Daily Staff"; ?>'
                }
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: true
                }
            },
            credits: {
                enabled: false
            },
            series: [{
                name: '<?php echo (!empty($graph["meta"]["empid"])) ? "Days Present" : "Staff"; ?>',
                data: <?php echo json_encode($graph['data'], JSON_NUMERIC_CHECK); ?>
            }]
        });
        }
    } catch(e) {
        console.error('Error creating Highcharts charts:', e);
    }
        
        // Average Monthly Hours gauge removed (per request)
        }); // End of waitForHighcharts
    }); // End of $(document).ready

</script>