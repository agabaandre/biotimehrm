<!-- Main content -->
<style>
    .info-box-main {
        box-shadow: rgba(110, 68, 68, 0.2);
        background: #00838f;
        text-align: center;
        display: -ms-flexbox;
        display: flex;
        margin-bottom: 1rem;
        min-height: 90px;
        padding: .5rem;
        position: relative;
        color: #FFF;
    }
</style>
<section class="content">

    <section class="col-lg-4 connectedSortable">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="card">
            <div class="card-header">
                <div class="card-tools">
                    <ul class="nav nav-pills ml-auto">
                        <!-- <li class="nav-item">
                      <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
                    </li> -->
                    </ul>
                </div>
            </div><!-- /.card-header -->
            <div class="card-body">
                <div id="line_graph_att"></div>
            </div><!-- /.card-body -->
        </div>
        <!-- /.card -->
    </section>
    <!-- right col -->
    <section class="col-lg-4 connectedSortable">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="card">
            <div class="card-header">
                <div class="card-tools">
                    <ul class="nav nav-pills ml-auto">
                        <!-- <li class="nav-item">
                      <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
                    </li> -->
                    </ul>
                </div>
            </div><!-- /.card-header -->
            <div class="card-body">
                <div id="line_graph_roster" style="width:100%; height:100%;"></div>
            </div><!-- /.card-body -->
        </div>
        <!-- /.card -->
    </section>
    <section class="col-lg-4 connectedSortable">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="card">
            <div class="card-header">

            </div>
            <div class="card-body">
                <div id="container-hours">
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- Custom tabs (Charts with tabs)-->
    </section>
    </div>
    <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
<script src="<?php echo base_url() ?>assets/plugins/moment/moment.min.js"></script>
<script src="<?php echo base_url() ?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
<script type="text/javascript">
    Highcharts.setOptions({
        colors: ['#28a745', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4']
    });

    $(document).ready(function () {
        knobgauge(0);
        $.ajax({
            type: 'GET',
            url: '<?php echo base_url('dashboard/') ?>',
            dataType: "json",
            data: '',
            success: function (data) {
                knobgauge(data.avg_hours);
                console.log(data);
            }
        });
    });

    <?php
    $graph = Modules::run("reports/dutygraphData");
    ?>
    Highcharts.chart('line_graph_roster', {
        chart: {
            type: 'line'
        },
        title: {
            text: 'Average Number of Employees Scheduled per Month <?php echo " " . str_replace("'", " ", $_SESSION["facility_name"]); ?>'
        },
        subtitle: {
            text: ''
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
    <?php
    ?>
    Highcharts.chart('line_graph_att', {
        chart: {
            type: 'line'
        },
        title: {
            text: 'Average Number of Employees Attending per Month - <?php echo " " . str_replace("'", " ", $_SESSION["facility_name"]) ?>'
        },
        subtitle: {
            text: ''
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
    // Average  Hours Gauge
    //chart options
    function knobgauge(gvalue) {
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
                    backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || '#EEE',
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
        //gauge
        var chartSpeed = Highcharts.chart('container-hours', Highcharts.merge(gaugeOptions, {
            title: {
                text: 'Average Monthly Hours-<?php echo " " . str_replace("'", " ", $_SESSION["facility_name"]); ?>',
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
                data: [parseInt(gvalue)],
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
        }))
    };

</script>