@include('member.filter._date_range', [
    'btn_search_id' => 'btnSearchOngoing',
    'start_date_id' => 'start_date_og',
    'end_date_id' => 'end_date_og',
])
<table class="table table-striped" id="mytableOngoing">
    <thead>
        <tr>
            <th class="sort" field="transaction_code" title="Order by Ticket No" target="_ongoing">Ticket No</th>
            <th class="sort" field="service_name" title="Order by Service Name" target="_ongoing">Service Name</th>
            <th class="sort" field="date_start_actual" title="Order by Start Date" target="_ongoing">Start Date</th>
            <th class="sort" field="date_end_estimated" title="Order by Start Date" target="_ongoing">Est End Date
            </th>
            <th class="sort" field="requester" title="Order by Requester" target="_ongoing">Requester</th>
            <th class="sort" field="status_name" title="Order by Status" target="_ongoing">Status</th>
            <th class="sort" field="cur_workflow" title="Order by Current Activity" target="_ongoing">Current Activity
            </th>
            <th class="sort" field="person_name_primary" title="Order by Current Primary PIC" target="_ongoing">
                Current PIC</th>
            <th class="sort" field="delay" title="Order by Delay" target="_ongoing">Delay</th>
            <th>Option</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script type="text/javascript">
    function showTableOngoing() {
        $("#mytableOngoing").DataTable().destroy();
        var oTable = $('#mytableOngoing').DataTable({
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
            language: {
                processing: "<?= \App\GeneralHelper::dt_loading_component() ?>"
            },
            "serverSide": true,
            "ajax": {
                "url": "<?= route('myservices.list_ongoing_new') ?>",
                "type": "GET",
                data: function(d) {
                    d.start_date = $("#start_date_og").val();
                    d.end_date = $("#end_date_og").val();
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
                    data: 'date_start_actual',
                    name: 'date_start_actual'
                },
                {
                    data: 'date_end_estimated',
                    name: 'date_end_estimated'
                },
                {
                    data: 'requester',
                    name: 'requester'
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
                    data: 'user_name_primary',
                    name: 'user_name_primary'
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
        $('#mytableOngoing_filter input').unbind();
        $('#mytableOngoing_filter input').bind('keyup', function(e) {
            if (e.keyCode == 13) { // Enter key
                oTable.search(this.value).draw();
            }
        });

        // Add search button next to search input
        if ($('#mytableOngoing_filter .btn-search-dt').length == 0) {
            $('#mytableOngoing_filter').append(
                '&nbsp;<button type="button" class="btn btn-sm btn-primary btn-search-dt"><i class="fa fa-search"></i></button>'
            );
            $('#mytableOngoing_filter .btn-search-dt').on('click', function() {
                oTable.search($('#mytableOngoing_filter input').val()).draw();
            });
        }
    }

    $(function() {
        showTableOngoing();
        $("#btnSearchOngoing").click(function() {
            showTableOngoing();
        });
    });
</script>
