@include('member.filter._date_range')
<table class="table table-striped " id="mytableNew">
    <thead>
        <tr>
            <th>#</th>
            <th class="sort" field="transaction_code" title="Order by Ticket No" target="_new">Ticket No</th>
            <th class="sort" field="service_name" title="Order by Service Name" target="_new">Service Name</th>
            <th class="sort" hidden field="description" title="Order by Service Description" target="_new">
                Description</th>
            <th class="sort" field="date_transaction" title="Order by Request Date" target="_new">Request Date</th>
            <th class="sort" field="person_name_buyer" title="Order by Requester" target="_new">Requester</th>
            <th class="sort" field="agency_name_buyer" title="Order by Requester" target="_new">Requester Unit</th>
            <th>Status</th>
            <th>Option</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
<script type="text/javascript">
    function showTableNew() {
        $("#mytableNew").DataTable().destroy();
        var oTable = $('#mytableNew').DataTable({
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
                url: "<?= route('myservices.list_new') ?>",
                type: "GET",
                data: function(d) {
                    d.start_date = $("#start_date").val();
                    d.end_date = $("#end_date").val();
                }
            },
            "columns": [{
                    data: 'transaction_code',
                    name: 'tr_service.transaction_code'
                },
                {
                    data: 'transaction_code',
                    name: 'tr_service.transaction_code'
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
                    data: 'date_transaction',
                    name: 'date_transaction'
                },
                {
                    data: 'person_name_buyer',
                    name: 'person_name_buyer'
                },
                {
                    data: 'agency_name_buyer',
                    name: 'agency_name_buyer'
                },
                {
                    data: 'id_status',
                    name: 'id_status'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            rowCallback: function(row, data, index) {
                var api = this.api();
                $('td:eq(0)', row).html(index + (api.page() * api.page.len()) + 1);
                $('td:eq(3)', row).hide();
            },
        });

        // Disable auto search on keyup, only search on button click or Enter key
        $('#mytableNew_filter input').unbind();
        $('#mytableNew_filter input').bind('keyup', function(e) {
            if (e.keyCode == 13) { // Enter key
                oTable.search(this.value).draw();
            }
        });

        // Add search button next to search input
        if ($('#mytableNew_filter .btn-search-dt').length == 0) {
            $('#mytableNew_filter').append(
                '&nbsp;<button type="button" class="btn btn-sm btn-primary btn-search-dt"><i class="fa fa-search"></i></button>'
            );
            $('#mytableNew_filter .btn-search-dt').on('click', function() {
                oTable.search($('#mytableNew_filter input').val()).draw();
            });
        }
    }

    $(function() {
        showTableNew();
        $("#btnSearch").click(function() {
            showTableNew();
        });
    });
</script>
