<style>
  .highcharts-data-table table {
    border-collapse: collapse;
    border-spacing: 0;
    background: white;
    min-width: 100%;
    margin-top: 10px;
    font-family: sans-serif;
    font-size: 0.9em;
}
.highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {
    border: 1px solid silver;
    padding: 0.5em;
}
.highcharts-data-table tr:nth-child(even), .highcharts-data-table thead tr {
    background: #f8f8f8;
}
.highcharts-data-table tr:hover {
    background: #eff;
}
.highcharts-data-table caption {
    border-bottom: none;
    font-size: 1.1em;
    font-weight: bold;
}

</style>
<section class="col-lg-12 connectedSortable" style="min-height:600px;">
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
              <div id="line_graph" style="width:100%; height:100%;"></div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->


          
          </section>


          <?php 

//$graph=Modules::run("reports/attroData"); 

 ?> 
 <script>
function renderGraph(gdata){
 Highcharts.chart('line_graph', {
     chart: {
         type: 'line'
     },
     data: {
        table: 'datatable'
    },
      title: {
         text: 'Duty Days Vs Days Present'
     },
     subtitle: {
         text: ''
     },
     xAxis: {
         categories: gdata.period
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
     exporting: {
        showTable: true
    },
     series: [{
         name: 'Staff Present',
         data: gdata.adata
     }, {
         name: 'Staff Scheduled',
         data:gdata.ddata
     }]
 })
//console.log(gdata.dperiod);

};
 
$(document).ready(function(){
    renderGraph(0);
$.ajax({
    url:'<?php echo  base_url('reports/attroData'); ?>',
    success:function(response){
        //console.log(response);
        renderGraph(JSON.parse(response));
    }
 });

});
</script>