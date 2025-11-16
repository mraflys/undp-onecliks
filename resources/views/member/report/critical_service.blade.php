@extends('admin.index')
@section('content')
<link href="{{ asset('plugins/chartjs/chart.min.css') }}" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{{ asset('plugins/chartjs/highcharts.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/chartjs/jquery.highchartTable.js') }}"></script>

<form class="form-horizontal m-b-10" role="form" method="POST" action="{{ route('myreport.critical_service_post') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label class="col-sm-3 control-label">Period Date Started from</label>
        <div class="col-sm-7">
            <div class="input-group input-daterange">
                <input type="text" name="date1" class="form-control" value="<?=$start_date;?>"> &nbsp;&nbsp;  
                <input type="text" name="date2" class="form-control" value="<?=$end_date;?>">
                &nbsp; <button class="btn btn-primary">Show Report</button>
            </div>
        </div>
    </div>
</form>

<div class="col-sm-12">
    <div id="myHighChart"></div>
</div>

<div class="col-sm-12">
    <hr>
    <div class="table-generator">
      <table style="width: 100%;" class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>&nbsp;</th>
              <?php 
                foreach ($table["New Request"] as $code =>$row) {
                  echo "<th style='text-align: center'>".$code."</th>";
                }
              ?>            
          </tr>
        </thead>
        <tbody>
          <?php
            foreach ($table as $stat => $row) {
              echo "<tr>";
              echo "<td>".$stat."</td>";
              
              foreach ($row as $code => $val) {
                echo "<td align='center'>".number_format($val, 2)."</td>";
              } 
              echo "</tr>";
            }
          ?>
        </tbody>
      </table>
    </div>
    <p>*) This report demonstrates number of critical service managed by service unit during specified period</p>
</div>

<script type="text/javascript">
     $('#myHighChart').highcharts({
        chart: {
            type: 'column',
            spacingBottom: 30
        },
        title: {
            text: 'Progress of Critical Services *'
        },
        subtitle: {
          text: 'Period : <?=isset($period) ? $period : '';?>'
        },
                credits: {
                        enabled: false
                },
        xAxis: {
            categories: [<?php echo $categories;?>]
        },
        yAxis: {
            min: 0,
            max: 105,
            endOnTick: false,
            title: {
                text: 'Percentage of Requests'
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
            y: 30,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
        },
        tooltip: {
          pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
          shared: true         
        },
        plotOptions: {
            column: {
                stacking: 'percent',
                dataLabels: {
                    enabled: true,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'black',
                    style: {
                        textShadow: '0 0 0px black'
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
            name: 'Completed',
            data: [<?php echo implode(',', $value["Completed"]["data"]);?>],
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#00FF00').brighten(-0.5).get('rgb')],
                    [0.5, '#00FF00'],
                    [1, Highcharts.Color('#00FF00').brighten(-0.5).get('rgb')]
                ]
            }                    
        }, {
            name: 'In Progress',
            data: [<?php echo implode(',', $value["On-going"]["data"]);?>],
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#FFFF00').brighten(-0.5).get('rgb')],
                    [0.5, '#FFFF00'],
                    [1, Highcharts.Color('#FFFF00').brighten(-0.5).get('rgb')]
                ]
            }                
        }, {
            name: 'Cancelled',
            data: [<?php echo implode(',', $value["Cancelled"]["data"]);?>],
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#FF9900').brighten(-0.5).get('rgb')],
                    [0.5, '#FF9900'],
                    [1, Highcharts.Color('#FF9900').brighten(-0.5).get('rgb')]
                ]
            }                
        }, {
            name: 'Rejected',
            data: [<?php echo implode(',', $value["Rejected"]["data"]);?>],
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#FF0000').brighten(-0.5).get('rgb')],
                    [0.5, '#FF0000'],
                    [1, Highcharts.Color('#FF0000').brighten(-0.5).get('rgb')]
                ]
            }                
        }, {
            name: 'Returned',
            data: [<?php echo implode(',', $value["Returned"]["data"]);?>],
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#FF00FF').brighten(-0.5).get('rgb')],
                    [0.5, '#FF00FF'],
                    [1, Highcharts.Color('#FF00FF').brighten(-0.5).get('rgb')]
                ]
            }
        }, {
            name: 'New Request',
            data: [<?php echo implode(',', $value["New Request"]["data"]);?>],
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#DDDDDD').brighten(-0.5).get('rgb')],
                    [0.5, '#DDDDDD'],
                    [1, Highcharts.Color('#DDDDDD').brighten(-0.5).get('rgb')]
                ]
            }
        }]
    });

    $(function(){
      $('.input-daterange').datepicker({
        format: 'yyyy-mm-dd'
      });
    });
</script>
<script type="text/javascript" src="{{ asset('plugins/chartjs/chart.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/chartjs/chartjs-plugin-datalabels.min.js') }}"></script>
@endsection