@extends('admin.index')
@section('content')
    <link href="{{ asset('plugins/chartjs/chart.min.css') }}" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="{{ asset('plugins/chartjs/highcharts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('plugins/chartjs/jquery.highchartTable.js') }}"></script>

    <?php
    // dd($value);
    ?>
    <div id="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="panel panel-color panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Management Report</h3>
                                </div>
                                <div class="panel-body">
                                    <ul>
                                        <li><a href="{{ route('myreport.detail') }}" class="waves-effect"
                                                id="detail">Detail</a></li>
                                        <li><a href="{{ route('myreport.timeliness') }}" class="waves-effect"
                                                id="timeliness-report">Timeliness Report : Completion of Ticket and
                                                Workflow</a></li>
                                        <li><a href="{{ route('myreport.timeliness_detail') }}" class="waves-effect"
                                                id="timeliness-detail">Timeliness Detail</a></li>
                                        <li><a href="{{ route('myreport.coa') }}" class="waves-effect" id="coa">Detail
                                                COA</a></li>
                                        <li><a href="{{ route('myreport.user_registration') }}" class="waves-effect"
                                                id="registration">User Registration</a></li>
                                        <li><a href="{{ route('myreport.workload_analysis') }}" class="waves-effect"
                                                id="workload">Staff Workload analysis</a></li>
                                        <li><a href="{{ route('myreport.performance') }}" class="waves-effect"
                                                id="performance">Performance report based on rating from client</a></li>
                                        <li><a href="{{ route('myreport.critical_service') }}" class="waves-effect"
                                                id="progress">Progress of Critical Services</a></li>
                                        <li><a href="{{ route('myreport.service_cost') }}" class="waves-effect"
                                                id="cost">Service Unit Cost Recovery and Transaction Value</a></li>
                                        <li><a href="{{ route('myreport.service_workload') }}" class="waves-effect"
                                                id="service">Service Unit Workload</a></li>
                                        <li><a href="{{ route('myrequests.tracking') }}" class="waves-effect"
                                                id="tracking">Request Tracking</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="panel panel-color panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Financial Report</h3>
                                </div>
                                <div class="panel-body">
                                    <ul>
                                        <li>
                                            <a href="{{ route('myreport.search_engine') }}" class="waves-effect"
                                                id="search">Search Engine</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('myreport.invoice_issue') }}" class="waves-effect"
                                                id="invoice">List of invoice issued and status of payments</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('myreport.dsa_advance') }}" class="waves-effect"
                                                id="dsa">DSA advance report</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('myreport.complete_ticket') }}" class="waves-effect"
                                                id="complete">List of Completed Tickets</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('myreport.my_project') }}" class="waves-effect"
                                                id="complete">My Payroll Expenditure</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-box">
                                <div class="content">
                                    <div class="chart-container">
                                        <div id="myHighChart"></div>
                                    </div>
                                    <?php if(isset($pagination) && $pagination['total_pages'] > 1): ?>
                                    <div class="text-center" style="margin-top: 20px;">
                                        <div class="btn-group" role="group">
                                            <?php if($pagination['current_page'] > 1): ?>
                                            <a href="?page=1" class="btn btn-default btn-sm">
                                                <i class="fa fa-angle-double-left"></i> First
                                            </a>
                                            <a href="?page=<?php echo $pagination['current_page'] - 1; ?>" class="btn btn-default btn-sm">
                                                <i class="fa fa-angle-left"></i> Prev
                                            </a>
                                            <?php endif; ?>

                                            <span class="btn btn-primary btn-sm disabled">
                                                Page <?php echo $pagination['current_page']; ?> of <?php echo $pagination['total_pages']; ?>
                                            </span>

                                            <?php if($pagination['current_page'] < $pagination['total_pages']): ?>
                                            <a href="?page=<?php echo $pagination['current_page'] + 1; ?>" class="btn btn-default btn-sm">
                                                Next <i class="fa fa-angle-right"></i>
                                            </a>
                                            <a href="?page=<?php echo $pagination['total_pages']; ?>" class="btn btn-default btn-sm">
                                                Last <i class="fa fa-angle-double-right"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-muted" style="margin-top: 10px;">
                                            Showing <?php echo ($pagination['current_page'] - 1) * $pagination['per_page'] + 1; ?>
                                            to <?php echo min($pagination['current_page'] * $pagination['per_page'], $pagination['total_items']); ?>
                                            of <?php echo $pagination['total_items']; ?> agencies
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                text: 'Period : <?= isset($period) ? $period : '' ?>'
            },
            credits: {
                enabled: false
            },
            xAxis: {
                categories: [<?php echo isset($categories) ? $categories : ''; ?>]
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
                align: 'right',
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
                        formatter: function() {
                            if (this.point.y == 0) {
                                return '';
                            } else {
                                let total = (this.point.y / this.point.total) * 100;
                                return total.toFixed(2) + '%'
                            }
                        }
                    }
                }
            },
            series: [{
                name: 'Completed',
                data: [<?php echo isset($value['Completed']['data']) && count($value['Completed']['data']) > 0 ? implode(',', $value['Completed']['data']) : ''; ?>],
                color: {
                    linearGradient: {
                        x1: 0,
                        x2: 1,
                        y1: 0,
                        y2: 0
                    },
                    stops: [
                        [0, Highcharts.Color('#00FF00').brighten(-0.5).get('rgb')],
                        [0.5, '#00FF00'],
                        [1, Highcharts.Color('#00FF00').brighten(-0.5).get('rgb')]
                    ]
                }
            }, {
                name: 'In Progress',
                data: [<?php echo isset($value['On-going']['data']) && count($value['On-going']['data']) > 0 ? implode(',', $value['On-going']['data']) : ''; ?>],
                color: {
                    linearGradient: {
                        x1: 0,
                        x2: 1,
                        y1: 0,
                        y2: 0
                    },
                    stops: [
                        [0, Highcharts.Color('#FFFF00').brighten(-0.5).get('rgb')],
                        [0.5, '#FFFF00'],
                        [1, Highcharts.Color('#FFFF00').brighten(-0.5).get('rgb')]
                    ]
                }
            }, {
                name: 'Cancelled',
                data: [<?php echo isset($value['Cancelled']['data']) && count($value['Cancelled']['data']) > 0 ? implode(',', $value['Cancelled']['data']) : ''; ?>],
                color: {
                    linearGradient: {
                        x1: 0,
                        x2: 1,
                        y1: 0,
                        y2: 0
                    },
                    stops: [
                        [0, Highcharts.Color('#FF9900').brighten(-0.5).get('rgb')],
                        [0.5, '#FF9900'],
                        [1, Highcharts.Color('#FF9900').brighten(-0.5).get('rgb')]
                    ]
                }
            }, {
                name: 'Rejected',
                data: [<?php echo isset($value['Rejected']['data']) && count($value['Rejected']['data']) > 0 ? implode(',', $value['Rejected']['data']) : ''; ?>],
                color: {
                    linearGradient: {
                        x1: 0,
                        x2: 1,
                        y1: 0,
                        y2: 0
                    },
                    stops: [
                        [0, Highcharts.Color('#FF0000').brighten(-0.5).get('rgb')],
                        [0.5, '#FF0000'],
                        [1, Highcharts.Color('#FF0000').brighten(-0.5).get('rgb')]
                    ]
                }
            }, {
                name: 'Returned',
                data: [<?php echo isset($value['Returned']['data']) && count($value['Returned']['data']) > 0 ? implode(',', $value['Returned']['data']) : ''; ?>],
                color: {
                    linearGradient: {
                        x1: 0,
                        x2: 1,
                        y1: 0,
                        y2: 0
                    },
                    stops: [
                        [0, Highcharts.Color('#FF00FF').brighten(-0.5).get('rgb')],
                        [0.5, '#FF00FF'],
                        [1, Highcharts.Color('#FF00FF').brighten(-0.5).get('rgb')]
                    ]
                }
            }, {
                name: 'New Request',
                data: [<?php echo isset($value['New Request']['data']) && count($value['New Request']['data']) > 0 ? implode(',', $value['New Request']['data']) : ''; ?>],
                color: {
                    linearGradient: {
                        x1: 0,
                        x2: 1,
                        y1: 0,
                        y2: 0
                    },
                    stops: [
                        [0, Highcharts.Color('#DDDDDD').brighten(-0.5).get('rgb')],
                        [0.5, '#DDDDDD'],
                        [1, Highcharts.Color('#DDDDDD').brighten(-0.5).get('rgb')]
                    ]
                }
            }]
        });
    </script>
@endsection
