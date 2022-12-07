      <div class="row">
      <section class="col-lg-12 connectedSortable">
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
              <div id="line_graph"></div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->

              
              </section>
        </div>

    <?php 

    $graph=Modules::run("reports/graphData"); 

    ?> 
    <script>
    Highcharts.chart('line_graph', {
        chart: {
            type: 'line'
        },
        title: {
            text: 'Attendace Reporting Rate'
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
            data: <?php echo json_encode($graph['data'],JSON_NUMERIC_CHECK); ?>
        }, {
            name: 'Target',
            data: <?php echo json_encode($graph['target'],JSON_NUMERIC_CHECK); ?>
        }]
    });

    </script>