@extends('admin.index')
@section('content')
    <ul class="nav nav-tabs  nav-tabs-line" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#kt_tabs_1_1" role="tab">My Service Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#kt_tabs_1_2" role="tab">Service Unit On Going Ticket</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#kt_tabs_1_3" role="tab">My Request Dashboard</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="kt_tabs_1_1" role="tabpanel">
            <div class="row">
                <div class="col-sm-5">
                    <h4>My Service Dashboard</h4>
                    <hr>
                    <div id="chartdiv" style="height: 350px;width: 90%"></div>
                </div>
                <div class="col-sm-7">
                    <h4>OnGoing Service</h4>
                    <hr>
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
                    <center>
                        <h4>On Going Ticket as of {{ Date('d F Y') }}</h4>
                    </center>
                    <div id="chartdiv2" style="height: 350px;width: 95%"></div>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="kt_tabs_1_3" role="tabpanel">
            <div class="row">
                <div class="col-sm-5">
                    <h4>My Request Dashboard</h4>
                    <hr>
                    <div id="chartdiv3" style="height: 350px;width: 90%"></div>
                </div>
                <div class="col-sm-7">
                    <h4>My Request Ticket</h4>
                    <hr>
                    <div class="table-responsive">
                        <table width="100%" class="table table-striped" id="myRequestTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th valign="top">Agency</th>
                                    <th>Country</th>
                                    <th>Service Unit</th>
                                    <th valign="top">Ticket No</th>
                                    <th valign="top">Start Date</th>
                                    <th valign="top">Requester</th>
                                    <th valign="top">Project</th>
                                    <th valign="top">Service Name</th>
                                    <th valign="top">Status</th>
                                    <th valign="top">Current Activity</th>
                                    <th valign="top">Delay</th>
                                    <th valign="top">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    $base_url_theme = URL::to('theme/demo4/src/');
    ?>
    <script src="<?= asset('/') ?>plugins/amcharts/core.js"></script>
    <script src="<?= asset('/') ?>plugins/amcharts/charts.js"></script>
    <script src="<?= asset('/') ?>plugins/amcharts/animated.js"></script>

    <!-- Chart code -->
    <script type="text/javascript">
        function showTable() {
            $("#mytable").DataTable().destroy();
            var oTable = $('#mytable').DataTable({
                dom: 'lBfrtip',
                buttons: [{
                    extend: 'excel',
                    text: "<i class='fa fa-download'> Excel</i>"
                }],
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'All']
                ],
                processing: true,
                language: {
                    processing: "<?= \App\GeneralHelper::dt_loading_component() ?>"
                },
                serverSide: true,
                ajax: "<?= route('myrequests.ongoing_search_home') ?>",
                "columns": [{
                        data: 'date_authorized',
                        name: 'date_authorized'
                    },
                    {
                        data: 'transaction_code',
                        name: 'transaction_code'
                    },
                    {
                        data: 'service_name',
                        name: 'service_name'
                    },
                    {
                        data: 'agency_name_buyer',
                        name: 'agency_name_buyer'
                    },
                    {
                        data: 'delay',
                        name: 'delay',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                // rowCallback: function( row, data, index ) {
                //   var api = this.api();
                //   $('td:eq(0)', row).html( index + (api.page() * api.page.len()) + 1);
                // },
            });

            // Disable auto search on keyup, only search on button click or Enter key
            $('#mytable_filter input').unbind();
            $('#mytable_filter input').bind('keyup', function(e) {
                if (e.keyCode == 13) { // Enter key
                    oTable.search(this.value).draw();
                }
            });

            // Add search button next to search input
            if ($('#mytable_filter .btn-search-dt').length == 0) {
                $('#mytable_filter').append(
                    '&nbsp;<button type="button" class="btn btn-sm btn-primary btn-search-dt"><i class="fa fa-search"></i></button>'
                );
                $('#mytable_filter .btn-search-dt').on('click', function() {
                    oTable.search($('#mytable_filter input').val()).draw();
                });
            }
        }

        function getPieChartData() {
            var delaySummary = 0;
            var ontimeSummary = 0;

            $.ajax({
                url: "{{ URL::to('api/summary/ontime_and_delay') }}" + '/' + <?= session('user_agency_unit_id') ?>,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $("#chartdiv").html("<center><p>Loading data, please wait ...</p></center>");
                },
                success: function(data) {
                    var result = data.data;
                    ontimeSummary = result.ontime
                    delaySummary = result.delay;
                    generatePieChart(ontimeSummary, delaySummary);
                }
            });
        }

        function getBarChartData() {
            var delaySummary = 0;
            var ontimeSummary = 0;

            $.ajax({
                url: "{{ URL::to('api/summary/ongoing/0') }}",
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $("#chartdiv2").html("<center><p>Loading data, please wait ...</p></center>");
                },
                success: function(data) {
                    var result = data.data;
                    generateBarChart(result);
                }
            });
        }

        $(document).ready(function() {
            showTable();
            getPieChartData();
            getBarChartData();

            // Initialize My Request tab (only load when tab is clicked)
            $('a[href="#kt_tabs_1_3"]').on('shown.bs.tab', function(e) {
                // Only load once
                if (!$(this).data('loaded')) {
                    showMyRequestTable();
                    getMyRequestPieChartData();
                    $(this).data('loaded', true);
                }
            });

            // If tab is already active on page load, load immediately
            if ($('#kt_tabs_1_3').hasClass('active')) {
                showMyRequestTable();
                getMyRequestPieChartData();
            }
        });

        function getMyRequestPieChartData() {
            $.ajax({
                url: "<?= route('myrequests.myrequest_summary_by_agency') ?>",
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    $("#chartdiv3").html("<center><p>Loading data, please wait ...</p></center>");
                },
                success: function(data) {
                    if (data.data && data.data.length > 0) {
                        var result = data.data;
                        generateMyRequestPieChart(result);
                    } else {
                        $("#chartdiv3").html("<center><p>No data available</p></center>");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading chart data:', error);
                    console.error('Response:', xhr.responseText);
                    $("#chartdiv3").html(
                        "<center><p style='color:red;'>Error loading chart data. Please refresh the page.</p></center>"
                    );
                }
            });
        }

        function showMyRequestTable() {
            $("#myRequestTable").DataTable().destroy();
            var oTable = $('#myRequestTable').DataTable({
                dom: 'lBfrtip',
                buttons: [{
                    extend: 'excel',
                    text: "<i class='fa fa-download'> Excel</i>"
                }],
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'All']
                ],
                processing: true,
                language: {
                    processing: "<?= \App\GeneralHelper::dt_loading_component() ?>"
                },
                serverSide: true,
                ajax: {
                    url: "<?= route('myrequests.old_ongoing_request_search') ?>",
                    data: function(d) {
                        d.is_mine = 1; // My request only hardcoded
                        d.status = 2;
                    }
                },
                columns: [{
                        data: 'agency_name_service',
                        name: 'agency_name_service'
                    },
                    {
                        data: 'agency_name_service',
                        name: 'agency_name_service'
                    },
                    {
                        data: 'country_name',
                        name: 'country_name'
                    },
                    {
                        data: 'parent_agency_name',
                        name: 'agency_unit_name'
                    },
                    {
                        data: 'transaction_code',
                        name: 'transaction_code'
                    },
                    {
                        data: 'date_authorized',
                        name: 'date_authorized'
                    },
                    {
                        data: 'person_name_buyer',
                        name: 'person_name_buyer'
                    },
                    {
                        data: 'id_project',
                        name: 'id_project'
                    },
                    {
                        data: 'service_name',
                        name: 'service_name'
                    },
                    {
                        data: 'status_name',
                        name: 'status_name'
                    },
                    {
                        data: 'workflow_name',
                        name: 'workflow_name'
                    },
                    {
                        data: 'delay',
                        name: 'delay',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [4, "ASC"]
                ],
                rowCallback: function(row, data, index) {
                    var api = this.api();
                    $('td:eq(0)', row).html(index + (api.page() * api.page.len()) + 1);
                },
            });

            // Disable auto search on keyup, only search on button click or Enter key
            $('#myRequestTable_filter input').unbind();
            $('#myRequestTable_filter input').bind('keyup', function(e) {
                if (e.keyCode == 13) { // Enter key
                    oTable.search(this.value).draw();
                }
            });

            // Add search button next to search input
            if ($('#myRequestTable_filter .btn-search-dt').length == 0) {
                $('#myRequestTable_filter').append(
                    '&nbsp;<button type="button" class="btn btn-sm btn-primary btn-search-dt"><i class="fa fa-search"></i></button>'
                );
                $('#myRequestTable_filter .btn-search-dt').on('click', function() {
                    oTable.search($('#myRequestTable_filter input').val()).draw();
                });
            }
        }

        function generateMyRequestPieChart(data) {
            am4core.ready(function() {
                am4core.useTheme(am4themes_animated);
                var chart = am4core.create("chartdiv3", am4charts.PieChart3D);
                chart.hiddenState.properties.opacity = 0; // this creates initial fade-in

                chart.legend = new am4charts.Legend();
                chart.data = data;

                var series = chart.series.push(new am4charts.PieSeries3D());
                series.dataFields.value = "count";
                series.dataFields.category = "agency_name_service";

            }); // end am4core.ready()
        }

        function generateBarChart(data) {
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

        function generatePieChart(ontimeSummary = 0, delaySummary = 0) {
            am4core.ready(function() {
                am4core.useTheme(am4themes_animated);
                var chart = am4core.create("chartdiv", am4charts.PieChart3D);
                chart.hiddenState.properties.opacity = 0; // this creates initial fade-in

                chart.legend = new am4charts.Legend();
                chart.data = [{
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
