@extends('admin.index')
@section('content')
    <form class="form-horizontal m-b-10" role="form">
        <div class="form-group">
            <label class="col-sm-3 control-label" style="padding-top: 0px;">View Coa Setting</label>
            <div class="form-group">
                <label for="exampleSelect1">view By</label>
                <select class="form-control w-50" id="exampleSelect1" name="viewBy">
                    <option value="date_finished">Date Complete</option>
                    <option value="date_authorized">Date Authorized</option>
                </select>
            </div>
            <div class="col-sm-7">
                <div class="input-group input-daterange">
                    <input type="text" id="date1" onchange="fundatechange1()" class="form-control"
                        value="<?= Date('Y-m-d', strtotime('first day of this month')) ?>"> &nbsp;&nbsp;
                    <input type="text" id="date2" onchange="fundatechange2()" class="form-control"
                        value="<?= Date('Y-m-d', strtotime('last day of this month')) ?>">
                    &nbsp; <button type="button" class="btn btn-primary" id="btnSearch">Show Report</button>
                </div>
            </div>
        </div>
    </form>
    <img src="{{ $src }}" alt="" id="imgProcessMap" style="display: none">
    <div class="col-sm-12">
        <p>
        <h3>Detail COA Report</h3>
        </p>

        <div id="print"></div>
        <table id="mytable" class="table table-striped table-bordered" colspan="0" width="100%">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Service Name</th>
                    <th>Description</th>
                    <th>Unit Name</th>
                    <th>Requester Unit</th>
                    <th>PCBU</th>
                    <th>Project</th>
                    <th>Activities</th>
                    <th>Contract Number</th>
                    <th>Expenditure Type</th>
                    <th>Funding Source</th>
                    <th>ACC</th>
                    <th>OPU</th>
                    <th>FUND</th>
                    <th>Department</th>
                    <th>Agent</th>
                    <th>Donor</th>
                    <th>(%)</th>
                    <th>Value</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>

    <script type="text/javascript">
        var datechange1 = $("#date1").val();
        var datechange2 = $("#date2").val();
        var viewBychange = $("#exampleSelect1").val();

        function fundatechange1() {
            datechange1 = $("#date1").val();
            return datechange1;
        }

        function fundatechange2() {
            datechange2 = $("#date2").val();
            return datechange2;
        }

        function funviewBychange2() {
            viewBychange12 = $("#exampleSelect1").val();
            return viewBychange12;
        }

        function showTable() {
            var date1 = $("#date1").val();
            var date2 = $("#date2").val();
            var viewBy = $("#exampleSelect1").val();

            $("#mytable").DataTable().destroy();
            var oTable = $('#mytable').DataTable({
                scrollX: true,
                dom: 'Blfrtip',
                buttons: generalDTButtons('landscape'),
                lengthMenu: generalDTLengths,
                "processing": true,

                ajax: {
                    "url": "<?= route('myreport.coa_list') ?>",
                    "type": "GET",
                    "data": function(dt) {
                        dt.date1 = date1;
                        dt.date2 = date2;
                        dt.viewBy = viewBy;
                    }
                },
                "columns": [{
                        data: 'transaction_code',
                        name: 'transaction_code'
                    },
                    {
                        data: 'service_name',
                        name: 'service_name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'unit_name',
                        name: 'agency_unit_name'
                    },
                    {
                        data: 'requester_unit',
                        name: 'agency_name_buyer'
                    },
                    {
                        data: 'pcbu',
                        name: 'pcbu'
                    },
                    {
                        data: 'project',
                        name: 'project'
                    },
                    {
                        data: 'activities',
                        name: 'activities'
                    },
                    {
                        data: 'contract_number',
                        name: 'contract_number'
                    },
                    {
                        data: 'exp_type',
                        name: 'exp_type'
                    },
                    {
                        data: 'funding_source',
                        name: 'funding_source'
                    },
                    {
                        data: 'acc',
                        name: 'acc'
                    },
                    {
                        data: 'opu',
                        name: 'opu'
                    },
                    {
                        data: 'fund',
                        name: 'fund'
                    },
                    {
                        data: 'dept',
                        name: 'dept'
                    },
                    {
                        data: 'imp_agent',
                        name: 'imp_agent'
                    },
                    {
                        data: 'donor',
                        name: 'donor'
                    },
                    {
                        data: 'percentage',
                        name: 'percentage'
                    },
                    {
                        data: 'service_price',
                        name: 'service_price'
                    },
                    {
                        data: 'status_name',
                        name: 'status_name'
                    },
                    // { data: 'sla', name: 'sla', orderable: false, searchable: false },
                    // { data: 'actual', name: 'actual', orderable: false, searchable: false },
                    // { data: 'delay', name: 'delay', orderable: false, searchable: false },
                ],
            });
        }

        $(document).ready(function() {
            showTable();
            $('.input-daterange').datepicker({
                format: 'yyyy-mm-dd'
            });
            $("#btnSearch").click(function() {
                showTable();
            })
        });
    </script>
@endsection
