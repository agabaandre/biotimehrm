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