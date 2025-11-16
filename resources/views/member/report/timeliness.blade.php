@extends('admin.index')
@section('content')
<script type="text/javascript" src="{{ asset('plugins/chartjs/highcharts.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/chartjs/jquery.highchartTable.js') }}"></script>

<form class="form-horizontal m-b-10" role="form" method="POST" action="{{ route('myreport.timeliness_post') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label class="col-sm-3 control-label">Finished Date Started from</label>
        <div class="col-sm-7">
            <div class="input-group input-daterange">
                <input type="text" name="date1" class="form-control" value="<?=Date('Y-m-d', strtotime('first day of this month'));?>"> &nbsp;&nbsp;  
                <input type="text" name="date2" class="form-control" value="<?=Date('Y-m-d', strtotime('last day of this month'));?>">
                &nbsp; <button class="btn btn-primary" id="btnSearch">Show Report</button>
            </div>
        </div>
        <div class="col-sm-2">
        </div>
    </div>
</form>
<div class="row">
    <div class="col-sm-12">
        <div id="timeliness_analysis_bar"></div>
    </div>

    <div class="col-sm-12" style="padding-top: 10px;">
      <hr>
      <div class="table-generator">
        @if (isset($table))
          <h3 style="text-align: center;">TIMELINESS REPORT : Completion of Workflow *</h3>
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>&nbsp;</th>
                  <?php
                  foreach($table["frequent"] as $key=>$row) {
                  ?>
                  <th><?php echo $key;?></th>
                  <?php  
                  } 
                  ?>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Most Frequent Delayed Process</td>
                  <?php
                  foreach($table["frequent"] as $key=>$row) {
                  ?>
                  <td><?php echo $row;?></td>
                  <?php  
                  } 
                  ?>
                </tr>
                <tr>
                  <td>Most Longest Delayed Process</td>
                  <?php
                  foreach($table["longest"] as $key=>$row) {
                  ?>
                  <td><?php echo $row;?></td>
                  <?php  
                  } 
                  ?>
                </tr>
                <tr>
                  <td>Most Frequent On Time/Faster Process</td>
                  <?php
                  foreach($table["ontime"] as $key=>$row) {
                  ?>
                  <td><?php echo $row;?></td>
                  <?php  
                  } 
                  ?>
                </tr>
              </tbody>
            </table>

            *) This report demonstrates the most frequent on time/faster/delay workflow per service unit within specified period
          @endif
        </div>
  </div>

</div>
<?php 
// dd($value) ;
?>
<script type="text/javascript">
  @if (isset($categories))
    $('#timeliness_analysis_bar').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: 'TIMELINESS REPORT : Completion of Ticket *'
        },
        subtitle: {
          text: "Period : <?=isset($period) ? $period : '';?>"
        },
        credits: {
            enabled: false
        },
        xAxis: {
            categories: [<?php echo $categories?>]
        },
        yAxis: {
            min: 0,
            max: 105,
            endOnTick: false,
            title: {
                text: 'Number of Requests'
            },
            stackLabels: {
                enabled: true,
                style: {
                    fontWeight: 'bold',
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                }
            }
        },
        legend: {
            align: 'center',
            x: -30,
            verticalAlign: 'bottom',
            y: 25,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.x + '</b><br/>' +
                    this.series.name + ': ' + this.y + '<br/>' +
                    'Total: ' + this.point.stackTotal;
            }
        },
        plotOptions: {
            column: {
                stacking: 'percent',
                dataLabels: {
                    enabled: true,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                    style: {
                        textShadow: '0 0 3px black',
                        background: 'white'
                    },
                   formatter : function() {
                                            if (this.point.y == 0) {
                                              return '';
                                            } else {
                                              return this.point.y;
                                            }
                                          }
                }
            }
        },
        series: [{
            name: 'On Time / Faster',
            data: <?php echo $value["ontime"]["data"]?>,
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#00FF00').brighten(-0.5).get('rgb')],
                    [0.5, '#00FF00'],
                    [1, Highcharts.Color('#00FF00').brighten(-0.5).get('rgb')]
                ]
            }                    
        }, {
            name: 'Delay',
            data: <?php echo $value["delay"]["data"]?>,
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#FEB301').brighten(-0.5).get('rgb')],
                    [0.5, '#FEB301'],
                    [1, Highcharts.Color('#FEB301').brighten(-0.5).get('rgb')]
                ]
            }                    
        }]
    });
  @endif

  $(document).ready(function () {
    $('.input-daterange').datepicker({ format: 'yyyy-mm-dd'});
});
</script>

@endsection