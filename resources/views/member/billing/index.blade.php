@extends('admin.index')
@section('content')
    @include('admin.messages')

    <p>
        <a href="{{ route('mybillings.add') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add New </a>
    </p>
    <hr>
    @include('member.filter._date_range')

    <table class="table table-striped " id="mytable">
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice No</th>
                <th>Agency</th>
                <th>IDR</th>
                <th>USD</th>
                <th>Invoice Date</th>
                <th>Payment Date</th>
                <th>Option</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
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
                language: {
                    processing: "<?= \App\GeneralHelper::dt_loading_component() ?>"
                },
                "serverSide": true,
                "ajax": {
                    url: "<?= route('mybillings.billing_list') ?>",
                    type: 'GET',
                    data: function(d) {
                        d.start_date = $("#start_date").val();
                        d.end_date = $("#end_date").val();
                    }
                },
                "columns": [{
                        data: 'invoice_no',
                        name: 'tr_billing.invoice_no'
                    },
                    {
                        data: 'invoice_no',
                        name: 'tr_billing.invoice_no'
                    },
                    {
                        data: 'agency_name',
                        name: 'tr_billing.agency_name'
                    },
                    {
                        data: 'amount_billing_local',
                        name: 'amount_billing_local'
                    },
                    {
                        data: 'amount_billing',
                        name: 'amount_billing'
                    },
                    {
                        data: 'invoice_date',
                        name: 'tr_billing.date_created'
                    },
                    {
                        data: 'payment_date',
                        name: 'tr_billing_detail.date_payment'
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
                },
            });
        }

        function deleteRow(id) {
            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes'
            }).then(function(result) {
                if (result.value) {
                    // let url =  "<?= URL::to('billing') ?>" + '/' + id + '/' + 'delete';
                    $.ajax({
                        url: "<?= route('mybillings.delete') ?>",
                        type: 'POST', // user.destroy
                        data: {
                            "_token": "{{ csrf_token() }}",
                            "_method": 'POST',
                            "id": id,
                        },
                        success: function(result) {
                            swal.fire('Deleted!', 'Your file has been deleted.', 'success');
                            showTable();
                        },
                        error: function() {
                            swal.fire('ERROR!', 'DATA can not be removed.', 'error');
                        }
                    });
                }
            });
        }

        $(function() {
            showTable();
            $("#btnSearch").click(function() {
                showTable();
            });
        });
    </script>
@endsection
