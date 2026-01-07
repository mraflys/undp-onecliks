@extends('admin.index')
@section('content')
    <?php
    $agencies = App\AgencyUnit::whereRaw('is_service_unit = 1 AND id_agency_unit_parent IS NULL')->orderBy('agency_unit_name')->get();
    ?>
    <hr>
    <div class="row" style="padding-left: 11px">
        <div class="col-sm-6">
            <div id="loadingStatus"></div>
            <div class="form-group">
                <label>Agency</label>
                <select class="form-control select2" id="id_agency_unit">
                    <option value="">All</option>
                    @foreach ($agencies as $c)
                        <option value="{{ $c->id_agency_unit }}">{{ $c->agency_unit_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="exampleSelect1">Service Unit</label>
                <select class="form-control select2" id="id_service_unit" name="id_agency_unit_service"></select>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Date</label>
                <table>
                    <tr>
                        <td>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                placeholder="YYYY-mm-dd">
                        </td>
                        <td> To </td>
                        <td>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                placeholder="YYYY-mm-dd">
                        </td>
                    </tr>
                </table>
            </div>
            <div class="form-group">
                <label>Rating</label>
                <select class="form-control" id="rating">
                    <option value="">All</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <button class="btn btn-primary" id="btnSearch" type="button">Search</button>
                <p>&nbsp;</p>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table width="100%" class="table table-striped " id="mytable">
            <thead>
                <tr>
                    <th>#</th>
                    <th valign="top" field="agency_service_name" title="Order by Agency">Agency</th>
                    <th>Country</th>
                    <th>Service Unit</th>
                    <th valign="top" field="transaction_code" title="Order by Ticket">Ticket No</th>
                    <th valign="top" field="person_name_buyer" title="Order by Requester">Requester</th>
                    <th valign="top" field="service_name" title="Order by Service Name">Service Name</th>
                    <th valign="top" field="date_authorized" title="Order by Start Date">Start At</th>
                    <th valign="top" field="project_name" title="Order by Project Name">Completion At</th>
                    <th valign="top" field="status_name" title="Order by Status">Status</th>
                    <th valign="top" field="Action" title="Action">Rating</th>
                    <th valign="top" field="delay" title="Order by Delay">Delay</th>
                    <th valign="top" field="delay" title="Order by Delay">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
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
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "<?= route('myrequests.history_request_search') ?>",
                    data: function(d) {
                        d.rating = $("#rating").val();
                        d.id_service_unit = $("#id_service_unit").val();
                        d.start_date = $("#start_date").val();
                        d.end_date = $("#end_date").val();
                    }
                },
                "columns": [{
                        data: 'agency_name_service',
                        name: 'parent_agency_name'
                    },
                    {
                        data: 'agency_name_service',
                        name: 'parent_agency_name'
                    },
                    {
                        data: 'country_name',
                        name: 'ms_country.country_name'
                    },
                    {
                        data: 'parent_agency_name',
                        name: 'agency_name_service'
                    },
                    {
                        data: 'transaction_code',
                        name: 'transaction_code'
                    },
                    {
                        data: 'person_name_buyer',
                        name: 'person_name_buyer'
                    },
                    {
                        data: 'service_name',
                        name: 'service_name'
                    },
                    {
                        data: 'date_authorized',
                        name: 'date_authorized'
                    },
                    {
                        data: 'date_finished',
                        name: 'date_finished'
                    },
                    {
                        data: 'status_name',
                        name: 'st.status_name'
                    },
                    {
                        data: 'service_rating',
                        name: 'service_rating'
                    },
                    {
                        data: 'delay_duration',
                        name: 'delay_duration',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ],
                rowCallback: function(row, data, index) {
                    var api = this.api();
                    $('td:eq(0)', row).html(index + (api.page() * api.page.len()) + 1);
                },
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

        $(function() {
            showTable();
            $(".select2").select2();
            $("#id_agency_unit").change(function() {
                $.ajax({
                    url: "<?= route('api-list-agency-units-search-by') ?>" + "?all=1&id_parent=" +
                        $("#id_agency_unit").val(),
                    dataType: 'json',
                    beforeSend: function() {
                        $("#loadingStatus").html("Loading ....");
                    },
                    success: function(data) {
                        $("#loadingStatus").html("");
                        $("#id_service_unit").html("<option value=''>All</option>");
                        $.each(data.data, function(k, value) {
                            $("#id_service_unit").append("<option value='" + value
                                .id_agency_unit + "'>" + value.agency_unit_name +
                                "</option>");
                        });
                    }
                })
            });
            $("#btnSearch").click(function() {
                showTable()
            });
        });
    </script>
    @include('member.filter._date_range_js')
@endsection
