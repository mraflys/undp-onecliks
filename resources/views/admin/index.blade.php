<html lang="en">

<!-- begin::Head -->

<head>
    <?php
    $base_url_theme = URL::to('theme/demo4/src/');
    ?>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title> {{ isset($title) ? $title : 'Dashboard' }}</title>
    <meta name="description" content="Latest updates and statistic charts">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <!--begin::Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">

    <!--end::Fonts -->

    <!--begin::Page Vendors Styles(used by this page) -->
    <link href="<?= $base_url_theme ?>/assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet"
        type="text/css" />

    <!--end::Page Vendors Styles -->

    <!--begin::Global Theme Styles(used by all pages) -->
    <link href="<?= $base_url_theme ?>/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= $base_url_theme ?>/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= asset('plugins/DataTables/datatables.min.css') ?>" rel="stylesheet" type="text/css" />
    <link href="<?= asset('plugins/DataTables/Buttons-1.6.1/css/buttons.dataTables.min.css') ?>" rel="stylesheet"
        type="text/css" />

    <script src="<?= asset('js/jquery-3.3.1.js') ?>" type="text/javascript"></script>
    <link href="<?= URL::to('plugins/fontawesome') ?>/css/fontawesome.css" rel="stylesheet">
    <link href="<?= URL::to('plugins/fontawesome') ?>/css/brands.css" rel="stylesheet">
    <link href="<?= URL::to('plugins/fontawesome') ?>/css/solid.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/v/dt/dt-1.12.1/rg-1.2.0/datatables.min.css" />
    <!--end::Global Theme Styles -->

    <!--begin::Layout Skins(used by all pages) -->

    <!--end::Layout Skins -->
    <link rel="shortcut icon" href="<?= URL::to('/') ?>/assets/images/undp-logo.png" />
    <style type="text/css">
        table {
            font-size: 13px !important;
        }

        .dataTables_wrapper .dt-buttons {
            margin-left: 10px;
            padding-bottom: 5px;
        }

        div.dataTables_wrapper div.dataTables_processing {
            top: 3em;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 30px;
            height: 30px;
            -webkit-animation: spin 2s linear infinite;
            /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <script type="text/javascript">
        var generalDTButtons = function(orientation = 'portrait') {
            return [{
                extend: 'excelHtml5',
                className: 'btn btn-success',
                text: "<i class='fa fa-download'> Excel</i>",
                init: function(api, node, config) {
                    $(node).removeClass('dt-button')
                }
            }, {
                extend: 'pdfHtml5',
                className: 'btn btn-danger',
                text: "<i class='fa fa-download'> PDF</i>",
                orientation: orientation,
                init: function(api, node, config) {
                    $(node).removeClass('dt-button')
                }
            }];
        }

        var generalDTButtons = function(orientation = 'landscape') {
            return [{
                    className: 'btn btn-success',
                    text: "<i class='fa fa-download'> Excel</i>",
                    init: function(api, node, config) {
                        $(node).removeClass('dt-button')
                    },
                    action: function(e, dt, button, config) {
                        var date1 = fundatechange1();
                        var date2 = fundatechange2();
                        var viewByval = funviewBychange2();
                        var base =
                            '{{ route('myreport.coa.excel', ['date1' => 'dateone', 'date2' => 'datetwo', 'viewBy' => 'views']) }}';
                        let urlolder = base.replace("views", viewByval);
                        let urlold = urlolder.replace("dateone", date1);
                        let url = urlold.replace("datetwo", date2);
                        window.location = url;
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-danger',
                    text: "<i class='fa fa-download'> PDF</i>",
                    orientation: orientation,
                    pageSize: 'LEGAL',
                    init: function(api, node, config) {
                        $(node).removeClass('dt-button')
                    }
                }
            ];
        }

        var generalExcelDTButtons = [{
            extend: 'excelHtml5',
            className: 'btn btn-success',
            text: "<i class='fa fa-download'> Excel</i>",
            init: function(api, node, config) {
                $(node).removeClass('dt-button')
            }
        }];
        var generalDTLengths = [
            [10, 25, 50, -1],
            ['10', '25', '50', 'All']
        ];
        var generalDTOptions = 'lBfrtip';
    </script>
</head>

<!-- end::Head -->

<!-- begin::Body -->

<body
    style="background-image: url(<?= $base_url_theme ?>/assets/media/demos/demo4/header.jpg); background-position: center top; background-size: 100% 350px;"
    class="kt-page--fluid kt-quick-panel--right kt-demo-panel--right kt-offcanvas-panel--right kt-header--fixed kt-header--minimize-menu kt-header-mobile--fixed kt-subheader--enabled kt-subheader--transparent">

    <!-- begin::Page loader -->

    <!-- end::Page Loader -->

    <!-- begin:: Page -->

    <!-- begin:: Header Mobile -->
    @include('admin.header')
    <!-- end:: Header -->
    <div class="kt-body kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor kt-grid--stretch" id="kt_body">
        <div class="kt-content kt-content--fit-top  kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor"
            id="kt_content">

            <!-- begin:: Subheader -->
            <div class="kt-subheader  kt-grid__item" id="kt_subheader" style="padding-bottom: 0">
                <div class="kt-container ">
                    <div class="kt-subheader__main">
                        <h3 class="kt-subheader__title"> {{ isset($title) ? $title : '' }}</h3>
                        <div class="kt-subheader__breadcrumbs">
                            @if (isset($breadcrumps[0]))
                                <span class="kt-subheader__breadcrumbs-separator"></span>
                                <a href="" class="kt-subheader__breadcrumbs-link"> {{ $breadcrumps[0] }}</a>
                            @endif
                            @if (isset($breadcrumps[1]))
                                <span class="kt-subheader__breadcrumbs-separator"></span>
                                <a href="" class="kt-subheader__breadcrumbs-link"> {{ $breadcrumps[1] }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- end:: Subheader -->

            <!-- begin:: Content -->
            <div class="kt-container  kt-grid__item kt-grid__item--fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <!--begin::Portlet-->
                        <div class="kt-portlet" style="margin: 0">
                            <!-- <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                          <h3 class="kt-portlet__head-title">
                            {{ isset($title) ? $title : '' }}
                          </h3>
                        </div>
                      </div> -->
                            <div class="kt-portlet__body">
                                <div class="kt-section">
                                    <span class="kt-section__info">
                                        <!-- Using the most basic table markup, here’s how tables look in Metronic: -->
                                    </span>
                                    <div class="kt-section__content">
                                        @yield('content')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end:: Content -->
        </div>
    </div>

    <!-- begin:: Footer -->
    @include('admin.footer')
    <!-- end:: Footer -->
    </div>
    </div>
    </div>

    <!-- end:: Page -->

    <!-- begin::Scrolltop -->
    <div id="kt_scrolltop" class="kt-scrolltop">
        <i class="fa fa-arrow-up"></i>
    </div>
    <!-- begin::Global Config(global config for global JS sciprts) -->
    <script>
        var KTAppOptions = {
            "colors": {
                "state": {
                    "brand": "#366cf3",
                    "light": "#ffffff",
                    "dark": "#282a3c",
                    "primary": "#5867dd",
                    "success": "#34bfa3",
                    "info": "#36a3f7",
                    "warning": "#ffb822",
                    "danger": "#fd3995"
                },
                "base": {
                    "label": ["#c5cbe3", "#a1a8c3", "#3d4465", "#3e4466"],
                    "shape": ["#f0f3ff", "#d9dffa", "#afb4d4", "#646c9a"]
                }
            }
        };
    </script>
    <!-- end::Global Config -->

    <!--begin::Global Theme Bundle(used by all pages) -->
    <script src="<?= $base_url_theme ?>/assets/plugins/global/plugins.bundle.js" type="text/javascript"></script>
    <script src="<?= $base_url_theme ?>/assets/js/scripts.bundle.js" type="text/javascript"></script>
    <script src="<?= asset('plugins/DataTables/datatables.min.js') ?>" type="text/javascript"></script>
    <script src="<?= asset('plugins/DataTables/Buttons-1.6.1/js/dataTables.buttons.min.js') ?>" type="text/javascript">
    </script>
    <script src="<?= asset('plugins/DataTables/pdfmake.min.js') ?>" type="text/javascript"></script>
    <script src="<?= asset('plugins/DataTables/vfs_fonts.js') ?>" type="text/javascript"></script>
    <!-- <script src="<?= asset('plugins/DataTables/Buttons-1.6.1/js/buttons.flash.min.js') ?>" type="text/javascript"></script>
    <script src="<?= asset('plugins/DataTables/Buttons-1.6.1/js/buttons.html5.min.js') ?>" type="text/javascript"></script>
 -->
    <script src="<?= asset('plugins/DataTables/Buttons-1.6.1/js/jszip.js') ?>" type="text/javascript"></script>
    <!--end::Global Theme Bundle -->
    <!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script> -->

    <!--begin::Page Vendors(used by this page) -->
    <script src="<?= $base_url_theme ?>/assets/plugins/custom/fullcalendar/fullcalendar.bundle.js" type="text/javascript">
    </script>

    <!--end::Page Vendors -->

    <!--begin::Page Scripts(used by this page) -->
    <script src="<?= $base_url_theme ?>/assets/js/pages/dashboard.js" type="text/javascript"></script>
    <script src="<?= $base_url_theme ?>/assets/js/pages/components/extended/sweetalert2.js" type="text/javascript"></script>
    <script src="<?= $base_url_theme ?>/assets/js/pages/crud/forms/widgets/bootstrap-datepicker.js" type="text/javascript">
    </script>

    <script type="text/javascript">
        $(".datepicker").datepicker({
            format: 'yyyy-mm-dd'
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Auto logout after 15 minutes of inactivity
        (function() {
            var timeout = 15 * 60 * 1000; // 15 minutes in milliseconds
            var idleTimer = null;
            var logoutUrl = "{{ route('logout') }}";
            var loginUrl = "{{ route('login') }}";

            function resetTimer() {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(function() {
                    // Logout user
                    $.ajax({
                        url: logoutUrl,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            window.location.href = loginUrl + '?timeout=1';
                        },
                        error: function() {
                            window.location.href = loginUrl + '?timeout=1';
                        }
                    });
                }, timeout);
            }

            // Track user activity
            $(document).on('mousemove keypress mousedown touchstart scroll click', function() {
                resetTimer();
            });

            // Start the timer
            resetTimer();
        })();
    </script>
    <!--end::Page Scripts -->
</body>

<!-- end::Body -->

</html>
