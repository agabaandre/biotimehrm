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
    #line_graph_att, #line_graph_roster {
        width: 100% !important;
        height: 400px !important;
        min-width: 0;
    }
    #container-hours {
        width: 100% !important;
        height: 400px !important;
        min-width: 0;
    }
    @media (max-width: 992px) {
        .chart-card .card-body {
            min-height: 350px;
        }
        #line_graph_att, #line_graph_roster, #container-hours {
            height: 350px !important;
        }
    }
    @media (max-width: 768px) {
        .chart-card .card-body {
            min-height: 300px;
        }
        #line_graph_att, #line_graph_roster, #container-hours {
            height: 300px !important;
        }
    }
</style>

<div class="attendance-graphs-container">
    <!-- First Row: Attendance per Month (8 cols) and Average Hours (4 cols) -->
    <div class="row">
        <!-- Attendance Chart -->
        <div class="col-lg-8 col-md-8 col-sm-12 mb-4">
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
        
        <!-- Average Hours Gauge -->
        <div class="col-lg-4 col-md-4 col-sm-12 mb-4">
            <div class="chart-card">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2"></i>Average Monthly Hours
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="container-hours" style="width:100%; height:400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Duty Roster (Full Width) - Separate Row -->
    <div class="row">
        <!-- Roster Chart -->
        <div class="col-12 mb-4">
            <div class="chart-card">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-2"></i>Employees Scheduled per Month
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="line_graph_roster" style="width:100%; height:400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

            // Initialize with 0, will be updated when data loads
            // This prevents blocking - chart shows immediately
            knobgauge(0);
            
            // Load graph data asynchronously with optimized timeout
            $.ajax({
                type: 'GET',
                url: '<?php echo base_url('dashboard/graphsData') ?>',
                dataType: "json",
                timeout: 20000, // Reduced timeout to prevent hanging
                cache: false,
                success: function (data) {
                    if (data.avg_hours !== undefined && data.avg_hours !== null) {
                        // Update gauge with actual value
                        var hours = parseFloat(data.avg_hours) || 0;
                        // Ensure value is within valid range (0-24)
                        hours = Math.max(0, Math.min(24, hours));
                        knobgauge(hours);
                    }
                    
                    // Update graph data if available
                    if (data.graph && data.graph.period && data.graph.data) {
                        // Update the existing charts if they exist
                        if (typeof Highcharts !== 'undefined' && Highcharts.charts) {
                            var rosterChart = Highcharts.charts.find(function(chart) {
                                return chart && chart.renderTo && chart.renderTo.id === 'line_graph_roster';
                            });
                            if (rosterChart) {
                                rosterChart.series[0].setData(data.graph.data);
                                rosterChart.xAxis[0].setCategories(data.graph.period);
                            }
                            
                            var attChart = Highcharts.charts.find(function(chart) {
                                return chart && chart.renderTo && chart.renderTo.id === 'line_graph_att';
                            });
                            if (attChart) {
                                attChart.series[0].setData(data.graph.data);
                                attChart.xAxis[0].setCategories(data.graph.period);
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Graphs data load error:', error);
                    // Keep default values
                }
            });

    <?php
    $graph = Modules::run("reports/dutygraphData");
    ?>
    
    // All Highcharts chart creation must be inside waitForHighcharts callback
    try {
        if (typeof Highcharts !== 'undefined' && typeof Highcharts.chart === 'function') {
            Highcharts.chart('line_graph_roster', {
        chart: {
            type: 'line'
        },
        title: {
            text: 'Employees Scheduled per Month'
        },
        subtitle: {
            text: '<?php echo str_replace("'", " ", $_SESSION["facility_name"]); ?>'
        },
        xAxis: {
            categories: <?php echo json_encode($graph['period']); ?>
        },
        yAxis: {
            title: {
                text: 'Staff'
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
            name: 'Staff',
            data: <?php echo json_encode($graph['data'], JSON_NUMERIC_CHECK); ?>
        }]
        });
        
        Highcharts.chart('line_graph_att', {
            chart: {
                type: 'line'
            },
            title: {
                text: 'Employees Attending per Month'
            },
            subtitle: {
                text: '<?php echo str_replace("'", " ", $_SESSION["facility_name"]); ?>'
            },
            xAxis: {
                categories: <?php echo json_encode($graph['period']); ?>
            },
            yAxis: {
                title: {
                    text: 'Staff'
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
                name: 'Staff',
                data: <?php echo json_encode($graph['data'], JSON_NUMERIC_CHECK); ?>
            }]
        });
        }
    } catch(e) {
        console.error('Error creating Highcharts charts:', e);
    }
        
        // Average Hours Gauge function - optimized to prevent re-rendering
        var gaugeChart = null; // Store chart instance to update instead of recreating
        
        function knobgauge(gvalue) {
            if (typeof Highcharts === 'undefined' || typeof Highcharts.chart !== 'function') {
                console.warn('Highcharts not available for gauge chart');
                return;
            }
            
            // Validate and clamp value
            var value = parseFloat(gvalue) || 0;
            value = Math.max(0, Math.min(24, value)); // Clamp between 0 and 24
            
            try {
                // If chart already exists, just update the value instead of recreating
                if (gaugeChart && gaugeChart.series && gaugeChart.series[0]) {
                    gaugeChart.series[0].setData([value], true); // true = redraw
                    return;
                }
                
                // Create chart only if it doesn't exist
                var gaugeOptions = {
                chart: {
                    type: 'solidgauge',
                    height: 400,
                    width: 350
                },
                pane: {
                    center: ['50%', '50%'],
                    size: '100%',
                    startAngle: 0,
                    endAngle: 360,
                    background: {
                        backgroundColor: (Highcharts.defaultOptions && Highcharts.defaultOptions.legend && Highcharts.defaultOptions.legend.backgroundColor) ? Highcharts.defaultOptions.legend.backgroundColor : '#EEE',
                    innerRadius: '60%',
                    outerRadius: '100%',
                    shape: 'arc'
                }
            },
            exporting: {
                enabled: true
            },
            tooltip: {
                enabled: false
            },
            // the value axis
            yAxis: {
                stops: [
                    [0.1, '#DF5353'], // red
                    [0.2, '#DDDF0D'], // yellow
                    [0.3, '#55BF3B'] // green
                ],
                lineWidth: 0,
                tickWidth: 0,
                minorTickInterval: null,
                tickAmount: 2,
                title: {
                    y: -70
                },
                labels: {
                    y: 16
                }
            },
            plotOptions: {
                solidgauge: {
                    dataLabels: {
                        y: 5,
                        borderWidth: 0,
                        useHTML: true
                    }
                    }
                }
            };
            //gauge - create chart and store reference
            gaugeChart = Highcharts.chart('container-hours', Highcharts.merge(gaugeOptions, {
                title: {
                    text: 'Average Monthly Hours',
                },
                subtitle: {
                    text: '<?php echo str_replace("'", " ", $_SESSION["facility_name"]); ?>'
                },
                yAxis: {
                    min: 0,
                    max: 24,
                },
                credits: {
                    enabled: false
                },
                series: [{
                    name: 'Hours',
                    data: [value],
                    dataLabels: {
                        format: '<div style="text-align:center">' +
                            '<span style="font-size:12px">{y}</span><br/>' +
                            '<span style="font-size:12px;opacity:0.4">Hrs</span>' +
                            '</div>'
                    },
                    tooltip: {
                        valueSuffix: ' Hours'
                    }
                }]
            }));
            } catch(e) {
                console.error('Error creating gauge chart:', e);
            }
        }
        }); // End of waitForHighcharts
    }); // End of $(document).ready

</script>