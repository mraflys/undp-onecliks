@extends('admin.index')
@section('content')
<script type="text/javascript" src="{{ asset('plugins/chartjs/highcharts.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/chartjs/jquery.highchartTable.js') }}"></script>

<form class="form-horizontal m-b-10" role="form" method="POST" action="{{ route('myreport.service_workload') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label class="col-sm-3 control-label">Period Start Date from</label>
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
        <div id="service_workload"></div>
    </div>
</div>
<?php 
// dd($value) ;
?>
<script type="text/javascript">
  @if (isset($categories))
   $('#service_workload').highcharts({
        chart: {
            type: 'column',
            spacingBottom: 50
        },
        title: {
            text: 'Service Unit Workload *'
        },
        subtitle: {
          text: "Period : <?=$period;?>"
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
            borderWidth: 3,
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
                        textShadow: '0 0 1px white'
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
            data: <?php echo $value["Completed"]["data"]?>,
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
            data: <?php echo $value["On-going"]["data"]?>,
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
            data: <?php echo $value["Cancelled"]["data"]?>,
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
            data: <?php echo $value["Rejected"]["data"]?>,
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
            data: <?php echo $value["Returned"]["data"]?>,
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
            data: <?php echo $value["New Request"]["data"]?>,
            color: {
                linearGradient: { x1: 0, x2:1, y1: 0, y2:0 },
                stops: [
                    [0, Highcharts.Color('#DDDDDD').brighten(-0.5).get('rgb')],
                    [0.5, '#DDDDDD'],
                    [1, Highcharts.Color('#DDDDDD').brighten(-0.5).get('rgb')]
                ]
            }
        }]    });
  @endif
  $(document).ready(function () {
    $('.input-daterange').datepicker();
});
</script>

@endsection