@extends('admin.index')
@section('content')
<script type="text/javascript" src="{{ asset('plugins/chartjs/highcharts.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/chartjs/jquery.highchartTable.js') }}"></script>

<form class="form-horizontal m-b-10" role="form" method="POST" action="{{ route('myreport.service_cost') }}">
    {{ csrf_field() }}
    <div class="form-group">
        <label class="col-sm-3 control-label">Billing Date from</label>
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
        <div id="monthly_income"></div>
        <div id="monthly_expenditure"></div>
        <div id="monthly_agency_expenditure"></div>
    </div>
</div>
<?php 
// dd($value) ;
?>
<script type="text/javascript">
  @if (isset($income))
   $('#monthly_income').highcharts({
      chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false
      },
      title: {
        style: { "fontSize": "16px", "fontWeight": "bold"},
        text: 'Service Unit Cost Recovery'
      },
      subtitle: {
        text: "Period : <?=isset($period) ? $period : '-';?>"
      },
      credits: {
        enabled: false
      },
      tooltip: {
        pointFormat: '{series.name}: <b>{point.y} ({point.percentage:.1f}%)</b>'
      },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '<b>{point.name}</b>: {point.y} ({point.percentage:.1f}%)',
            style: {
              color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
            }
          },
          showInLegend: true
        }
      },
      series: [{
        type: 'pie',
        name: 'Service',
        data: [<?php 
          foreach($income as $i) {
          ?> {
          name: <?php echo $i["name"];?>,
          y: <?php echo $i["y"];?>,
        visible: <?php echo $i["y"] == 0 ? 'false' : 'true'?>,
          color: {
            radialGradient: {cx: 0.5, cy: 0.5, r: 0.9},
            stops: [
                [0, '<?php echo $i["color"];?>'],
                [1, Highcharts.Color('<?php echo $i["color"];?>').brighten(-0.5).get('rgb')] // darken
            ]}
          },
          <?php
          }
        ?>]
      }]
    });
  @endif

  @if (isset($expenditure) && !is_null($expenditure))
    $('#monthly_expenditure').highcharts({
      chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false
      },
      title: {
        style: { "fontSize": "16px", "fontWeight": "bold"},
        text: 'Cost Recovery From Services Provided to UNDP Related Programme / Projects'
      },
      subtitle: {
        text: "Period : <?=isset($period) ? $period : '-';?>"
      },
      credits: {
        enabled: false
      },
      tooltip: {
        pointFormat: '{series.name}: <b>{point.y} ({point.percentage:.1f}%)</b>'
      },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '<b>{point.name}</b>: {point.y} ({point.percentage:.1f}%)',
            style: {
              color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
            }
          },
          showInLegend: true
        }
      },
      series: [{
        type: 'pie',
        name: 'Service',
        data: <?php echo $expenditure; ?>
      }]
    });
  @endif

  @if (isset($agency_expenditure) && !is_null($agency_expenditure))
    $('#monthly_agency_expenditure').highcharts({
      chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false
      },
      title: {
        style: { "fontSize": "16px", "fontWeight": "bold"},
        text: 'Cost Recovery From Services Provided to UN Agencies'
      },
      subtitle: {
        text: "Period : <?=isset($period) ? $period : '-';?>"
      },
      credits: {
        enabled: false
      },
      tooltip: {
        pointFormat: '{series.name}: <b>{point.y} ({point.percentage:.1f}%)</b>'
      },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '<b>{point.name}</b>: {point.y} ({point.percentage:.1f}%)',
            style: {
              color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
            }
          },
          showInLegend: true
        }
      },
      series: [{
        type: 'pie',
        name: 'Service',
        data: <?php echo $agency_expenditure; ?>
      }]
    });
@endif
  $(document).ready(function () {
    $('.input-daterange').datepicker({ format: 'yyyy-mm-dd'});
});
</script>

@endsection