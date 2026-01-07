@extends('admin.index')
@section('content')
    @include('admin.messages')
    <hr>
    <?php
    $agencies = App\AgencyUnit::whereRaw('is_service_unit = 1 AND id_agency_unit_parent IS NULL')->orderBy('agency_unit_name')->get();
    ?>
    <style type="text/css">
        .rate-selector [type=radio] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* IMAGE STYLES */
        .rate-selector [type=radio]+img {
            cursor: pointer;
        }

        /* CHECKED STYLES */
        .rate-selector [type=radio]:checked+img {
            outline: 2px solid #f00;
        }
    </style>
    <div class="row" style="padding-left: 11px">
        <div class="col-sm-6">

            <!-- <div class="hiddenradio">
                              <label>
                                <input type="radio" name="test" value="small" checked>
                                <img src="http://placehold.it/40x60/0bf/fff&text=A">
                              </label>

                              <label>
                                <input type="radio" name="test" value="big">
                                <img src="http://placehold.it/40x60/b0f/fff&text=B">
                              </label>
                            </div> -->

            <div id="loadingStatus"></div>
            <div class="form-group">
                <label>Agency</label>
                <select class="form-control select2" id="id_agency_unit">
                    <option>&nbsp;</option>
                    @foreach ($agencies as $c)
                        <option value="{{ $c->id_agency_unit }}">{{ $c->agency_unit_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="exampleSelect1">Service Unit</label>
                <select class="form-control select2" id="id_service_unit" name="id_agency_unit_service"></select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <table class="table">
                    <tr>
                        <td>
                            From: <input type="date" name="start_date" id="start_date" class="form-control"
                                placeholder="YYYY-mm-dd">
                        </td>
                        <td>
                            To: <input type="date" name="end_date" id="end_date" class="form-control"
                                placeholder="YYYY-mm-dd">
                        </td>
                    </tr>
                </table>
            </div>
            <div class="form-group">
                <button class="btn btn-primary" id="btnSearch" type="button">Search</button>
                <p>&nbsp;</p>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <label>Status</label>
                <select class="form-control" id="status">
                    <option value=""></option>
                    <option value="-1">Returned</option>
                    <option value="1">New Request</option>
                    <option value="2">On Going</option>
                    <option value="3">Rejected</option>
                    <option value="5">Complete</option>
                    <option value="6">Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <label>View</label>
                <select class="form-control" id="mine">
                    <option value=""></option>
                    <option value="1">My Request Only</option>
                </select>
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
                    <th valign="top" field="date_authorized" title="Order by Start Date">Start Date</th>
                    <th valign="top" field="person_name_buyer" title="Order by Requester">Requester</th>
                    <th valign="top" field="project_name" title="Order by Project Name">Project</th>
                    <th valign="top" field="service_name" title="Order by Service Name">Service Name</th>
                    <th valign="top" field="status_name" title="Order by Status">Status</th>
                    <th valign="top" field="workflow_name" title="Order by Current Status">Current Activity</th>
                    <th valign="top" field="delay" title="Order by Delay">Delay</th>
                    <th valign="top" field="Action" title="Action">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="modal fade" id="ratingModal" tabindex="-1" role="dialog" aria-labelledby="formReturnLabel"
        aria-hidden="true">
        <form action="{{ route('myservices.add_rating') }}" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="id_transaction" id="id_transaction_rating">
            <div class="modal-dialog" role="document" style="max-width: 1000px">
                <div class="modal-content" style="width: 1000px">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formReturnLabel">Rating Form</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped table-bordered">
                            <tbody>
                                <tr>
                                    <td style="text-align: right;">Date</td>
                                    <td style="text-align: left;"><?php echo date('d M Y'); ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">Agency</td>
                                    <td style="text-align: left;">
                                        <div id="rateAgency"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">Service</td>
                                    <td style="text-align: left;">
                                        <div id="rateService"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">Delay</td>
                                    <td style="text-align: left;">
                                        <div id="rateDelay"></div> Day(s)
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">Service Rating</td>
                                    <td style="text-align: left;">
                                        <div class="rate-selector">
                                            <label>
                                                <input type="radio" name="rating" value="1" />
                                                <img src="<?= asset('assets/images/smiley/1.png') ?>"
                                                    title='Very Unsatisfied' />
                                            </label>
                                            <label>
                                                <input type="radio" name="rating" value="2" />
                                                <img src="<?= asset('assets/images/smiley/2.png') ?>"
                                                    title='Unsatisfied' />
                                            </label>
                                            <label>
                                                <input type="radio" name="rating" value="3" />
                                                <img src="<?= asset('assets/images/smiley/3.png') ?>" title='Normal' />
                                            </label>
                                            <label>
                                                <input type="radio" name="rating" value="4" />
                                                <img src="<?= asset('assets/images/smiley/4.png') ?>" title='Satisfied' />
                                            </label>
                                            <label>
                                                <input type="radio" name="rating" value="5" />
                                                <img src="<?= asset('assets/images/smiley/5.png') ?>"
                                                    title='Very Satisfied' />
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: right;">Input for improvement</td>
                                    <td style="text-align: left;">
                                        <textarea class="validate[required]" id="improvement" name="comment" cols="75" rows="5" required></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">Send Rating</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        // function showTable(){
        //     $("#mytable").DataTable().destroy();
        //     var oTable = $('#mytable').DataTable({
        //       dom: 'lBfrtip',
        //       buttons: [
        //           { extend: 'excel', text: "<i class='fa fa-download'> Excel</i>" }
        //       ],
        //       lengthMenu: [[ 10, 25, 50, -1 ],['10', '25', '50', 'All']],
        //       "processing": true,
        //       "serverSide": true,
        //       "ajax": "<?= route('myrequests.ongoing_request_search') ?>",
        //       "columns": [
        //         { data: 'service_category_name', name: 'service_category.agency_unit_name' },
        //         { data: 'service_category_name', name: 'service_category.agency_unit_name' },
        //         { data: 'country_name', name: 'ms_country.country_name' },
        //         { data: 'agency_parent_name', name: 'agency_parent.agency_unit_name' },
        //         { data: 'transaction_code', name: 'transaction_code' },
        //         { data: 'date_authorized', name: 'date_authorized' },
        //         { data: 'person_name_buyer', name: 'person_name_buyer' },
        //         { data: 'id_project', name: 'id_project' },
        //         { data: 'service_name', name: 'service_name' },
        //         { data: 'status_name', name: 'ms_status.status_name' },
        //         { data: 'id_project', name: 'id_project' },
        //         { data: 'delay_duration', name: 'delay_duration', orderable: false, searchable: false },
        //         { data: 'action', name: 'action', orderable: false, searchable: false }
        //       ],
        //       rowCallback: function( row, data, index ) {
        //         var api = this.api();
        //         $('td:eq(0)', row).html( index + (api.page() * api.page.len()) + 1);
        //       },
        //     });
        //   }

        function showTable() {
            $("#mytable").DataTable().destroy();
            var oTable = $('#mytable').DataTable({
                dom: generalDTOptions,
                buttons: generalExcelDTButtons,
                lengthMenu: generalDTLengths,
                "processing": true,
                language: {
                    processing: "<?= \App\GeneralHelper::dt_loading_component() ?>"
                },
                "serverSide": true,
                "ajax": {
                    url: "<?= route('myrequests.old_ongoing_request_search') ?>",
                    data: function(d) {
                        d.status = $("#status").val();
                        d.is_mine = $("#mine").val();
                        d.id_service_unit = $("#id_service_unit").val();
                        d.start_date = $("#start_date").val();
                        d.end_date = $("#end_date").val();
                    }
                },
                "columns": [{
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
                "order": [
                    [4, "ASC"]
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

        function addRating(id, agency, service, delay) {
            $("#id_transaction_rating").val(id);
            $("#rateAgency").html(agency);
            $("#rateService").html(service);
            $("#rateDelay").html(delay);
            $("#ratingModal").modal('show');
        }

        $(function() {
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
                        $("#id_service_unit").html("");
                        $.each(data.data, function(k, value) {
                            $("#id_service_unit").append("<option value='" + value
                                .id_agency_unit + "'>" + value.agency_unit_name +
                                "</option>");
                        });
                    }
                })
            })
            showTable();
            $("#btnSearch").click(function() {
                showTable()
            });
        });
    </script>
    @include('member.filter._date_range_js')
@endsection
