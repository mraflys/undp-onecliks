@include('member.filter._date_range', [
    'btn_search_id' => 'btnSearchTracking',
    'start_date_id' => 'start_date_t',
    'end_date_id' => 'end_date_t',
])
<table class="table table-striped" id="mytableTracking">
    <thead>
        <tr>
            <th class="sort" field="transaction_code" title="Order by Ticket No" target="_tracking">Ticket No</th>
            <th class="sort" field="service_name" title="Order by Service Name" target="_tracking">Service Name</th>
            <th class="sort" field="date_transaction" title="Order by Start Date" target="_tracking">Start Date</th>
            <th class="sort" field="date_finished" title="Order by Start Date" target="_tracking">Finished Date</th>
            <th class="sort" field="requester" title="Order by Requester" target="_tracking">Requester</th>
            <th class="sort" field="service_price" title="Order by Requester" target="_tracking">Price</th>
            <th class="sort" field="status_name" title="Order by Status" target="_tracking">Status</th>
            <th class="sort" field="delay" title="Order by Delay" target="_tracking">Delay</th>
            <th class="sort" field="service_rating" title="Order by Current Activity" target="_tracking">Rating</th>
            <th>Option</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script type="text/javascript">
    function showTableTracking() {
        $("#mytableTracking").DataTable().destroy();
        var oTable = $('#mytableTracking').DataTable({
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
                "url": "<?= route('myservices.list_tracking') ?>",
                "type": "GET",
                data: function(d) {
                    d.start_date = $("#start_date_t").val();
                    d.end_date = $("#end_date_t").val();
                }
            },
            "columns": [{
                    data: 'transaction_code',
                    name: 'tr_service.transaction_code'
                },
                {
                    data: 'service_name',
                    name: 'tr_service.service_name'
                },
                {
                    data: 'date_transaction',
                    name: 'tr_service.date_transaction'
                },
                {
                    data: 'date_finished',
                    name: 'tr_service.date_finished'
                },
                {
                    data: 'person_name_buyer',
                    name: 'tr_service.person_name_buyer'
                },
                {
                    data: 'service_price',
                    name: 'tr_service.service_price'
                },
                {
                    data: 'status_name',
                    name: 'ms_status.status_name'
                },
                {
                    data: 'delay_duration',
                    name: 'delay_duration',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'service_rating',
                    name: 'tr_service.service_rating'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
        });

        // Disable auto search on keyup, only search on button click or Enter key
        $('#mytableTracking_filter input').unbind();
        $('#mytableTracking_filter input').bind('keyup', function(e) {
            if (e.keyCode == 13) { // Enter key
                oTable.search(this.value).draw();
            }
        });

        // Add search button next to search input
        if ($('#mytableTracking_filter .btn-search-dt').length == 0) {
            $('#mytableTracking_filter').append(
                '&nbsp;<button type="button" class="btn btn-sm btn-primary btn-search-dt"><i class="fa fa-search"></i></button>'
            );
            $('#mytableTracking_filter .btn-search-dt').on('click', function() {
                oTable.search($('#mytableTracking_filter input').val()).draw();
            });
        }
    }

    $(function() {
        showTableTracking();
        $("#btnSearchTracking").click(function() {
            showTableTracking();
        });
    });
</script>
