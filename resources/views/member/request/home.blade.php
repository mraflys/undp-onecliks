@extends('admin.index')
@section('content')

<ul class="nav nav-tabs  nav-tabs-line" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#kt_tabs_1_1" role="tab">My Service Dashboard</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#kt_tabs_1_2" role="tab">Service Unit On Going Ticket</a>
  </li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="kt_tabs_1_1" role="tabpanel">
    <div class="row">
       <div class="col-sm-5">
         <h4>My Service Dashboard</h4><hr>
         <div id="chartdiv" style="height: 350px;width: 90%"></div>
       </div>
       <div class="col-sm-7">
         <h4>OnGoing Service</h4><hr>
          <table class="table table-striped" id="mytable">
            <thead>
              <tr>
                <th style="width: 3%">Start Date</th>
                <th>Transaction Code</th>
                <th field>Service</th>
                <th>Requester</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
       </div>
    </div>
  </div>

  <div class="tab-pane" id="kt_tabs_1_2" role="tabpanel">
    <div class="row">
      <div class="col-sm-12">
        <center><h4>On Going Ticket as of {{ Date('d F Y')}}</h4></center>
        <div id="chartdiv2" style="height: 350px;width: 95%"></div>
      </div>
    </div>
  </div>
</div>

<?php 
  $base_url_theme = URL::to('theme/demo4/src/');
?>
<script src="<?=asset('/');?>plugins/amcharts/core.js"></script>
<script src="<?=asset('/');?>plugins/amcharts/charts.js"></script>
<script src="<?=asset('/');?>plugins/amcharts/animated.js"></script>

<!-- Chart code -->
<script type="text/javascript">
  function showTable(){
    $("#mytable").DataTable().destroy();
    var oTable = $('#mytable').DataTable({
      dom: 'lBfrtip',
      buttons: [
          { extend: 'excel', text: "<i class='fa fa-download'> Excel</i>" }
      ],
      lengthMenu: [[ 10, 25, 50, -1 ],['10', '25', '50', 'All']],
      processing: true,
      language: { processing: "<?=\App\GeneralHelper::dt_loading_component();?>" },
      serverSide: true,
      ajax: "<?=route('myrequests.ongoing_search_home');?>",
      "columns": [
        { data: 'date_authorized', name: 'date_authorized' },
        { data: 'transaction_code', name: 'transaction_code' },
        { data: 'service_name', name: 'service_name' },
        { data: 'agency_name_buyer', name: 'agency_name_buyer' },
        { data: 'delay', name: 'delay', orderable: false, searchable: false  },
        { data: 'action', name: 'action', orderable: false, searchable: false  },
      ],
      // rowCallback: function( row, data, index ) {
      //   var api = this.api();
      //   $('td:eq(0)', row).html( index + (api.page() * api.page.len()) + 1);
      // },
    });
  }

  function getPieChartData(){
    var delaySummary = 0;
    var ontimeSummary = 0;

    $.ajax({
      url: "{{ URL::to('api/summary/ontime_and_delay') }}" + '/' + <?=session('user_agency_unit_id');?>,
      type: 'GET',
      dataType: 'json',
      beforeSend: function(){
        $("#chartdiv").html("<center><p>Loading data, please wait ...</p></center>");
      },
      success: function(data){
        var result = data.data;
        ontimeSummary = result.ontime
        delaySummary = result.delay;
        generatePieChart(ontimeSummary, delaySummary);
      }
    });
  }

  function getBarChartData(){
    var delaySummary = 0;
    var ontimeSummary = 0;

    $.ajax({
      url: "{{ URL::to('api/summary/ongoing/0') }}",
      type: 'GET',
      dataType: 'json',
      beforeSend: function(){
        $("#chartdiv2").html("<center><p>Loading data, please wait ...</p></center>");
      },
      success: function(data){
        var result = data.data;
        generateBarChart(result);
      }
    });
  }

  $(document).ready(function(){
    showTable();
    getPieChartData();
    getBarChartData();
  });

  function generateBarChart(data){
    am4core.ready(function() {
      am4core.useTheme(am4themes_animated);
      var chart2 = am4core.create("chartdiv2", am4charts.XYChart);

      chart2.data = data
      // Create axes
      var categoryAxis = chart2.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "agency_unit_code";
      categoryAxis.renderer.grid.template.location = 0;
      categoryAxis.renderer.minGridDistance = 30;

      // categoryAxis.renderer.labels.template.adapter.add("dy", function(dy, target) {
      //   if (target.dataItem && target.dataItem.index & 2 == 2) {
      //     return dy + 25;
      //   }
      //   return dy;
      // });

      var valueAxis = chart2.yAxes.push(new am4charts.ValueAxis());

      // Create series
      var series = chart2.series.push(new am4charts.ColumnSeries());
      series.dataFields.valueY = "count";
      series.dataFields.categoryX = "agency_unit_code";
      series.name = "Total";
      series.columns.template.tooltipText = "{categoryX}: [bold]{valueY}[/]";
      series.columns.template.fillOpacity = .8;

      var columnTemplate = series.columns.template;
      columnTemplate.strokeWidth = 2;
      columnTemplate.strokeOpacity = 1;

    }); // end am4core.ready()
  }

  function generatePieChart(ontimeSummary = 0, delaySummary = 0){
    am4core.ready(function() {
      am4core.useTheme(am4themes_animated);
      var chart = am4core.create("chartdiv", am4charts.PieChart3D);
      chart.hiddenState.properties.opacity = 0; // this creates initial fade-in

      chart.legend = new am4charts.Legend();
      chart.data = [
        {
          serviceStatus: "Ontime",
          total: ontimeSummary
        },
        {
          serviceStatus: "Delay",
          total: delaySummary
        },
      ];
      var series = chart.series.push(new am4charts.PieSeries3D());
      series.dataFields.value = "total";
      series.dataFields.category = "serviceStatus";
    }); // end am4core.ready()
  }

</script>
@endsection
