@extends('admin.index')
@section('content')
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title text-primary">
                    {{ strtoupper($title) }} - <?= isset($detail) ? 'EDIT' : 'NEW' ?>
                </h3>
            </div>
        </div>
        <div class="col-md-9 col-xs-12">
            <form class="kt-form" action=" {{ route('mybillings.create') }}" method="POST">
                {{ csrf_field() }}

                <div class="kt-portlet__body">
                    <div class="form-group form-group-last">
                        @include('admin.messages')
                    </div>
                    <div class="form-group">
                        <label>Agency to Bill</label>
                        <select class="form-control select2" id="id_agency_unit" name="id_agency_unit">
                            <option value="">---Select Agency---</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id_agency_unit }}">{{ $agency->agency_unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleSelect1">Description</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="exampleSelect1">UNORE USD to IDR</label>
                        <input type="number" name="unore" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="exampleSelect1">Service to Bill</label>
                        <div class="col-lg-12" style="max-height: 500px; overflow-y: scroll;">
                            <table class="table table-striped table-bordered" id="transaction_ready_to_bill">
                                <thead>
                                    <th><input type="checkbox" id="select_all" checked="checked" /></th>
                                    <th>TID</th>
                                    <th>Service Name</th>
                                    <th style="max-width: 250px">Description</th>
                                    <th>Payment</th>
                                    <th>Amount</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="kt-portlet__foot">
                    <div class="kt-form__actions">
                        <button type="submit" class="btn btn-primary" name="submit">Save</button>
                        <a href="{{ route('mybillings.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
            <!--end::Form-->
        </div>

    </div>
    <script type="text/javascript">
        $(function() {
            $('.select2').select2();
            $('#id_agency_unit').change(function() {
                $.ajax({
                    url: "<?= URL::to('member-area/billing/data/transaction_ready_to_bill') ?>" +
                        "/" + $("#id_agency_unit").val(),
                    dataType: 'json',
                    beforeSend: function() {
                        $("#transaction_ready_to_bill tbody").html(
                            "<tr><td colspan='7'>Loading.... Please wait a moment</td></tr>"
                        );
                    },
                    success: function(data) {
                        $("#transaction_ready_to_bill tbody").empty("");
                        var trRow = "";
                        $.each(data.data, function(k, value) {
                            console.log(value.id_transaction);
                            trRow += "<tr>";
                            trRow +=
                                "<td><input type='checkbox' class='ids' name='ids[" +
                                value.id_transaction + "]' value='" + value
                                .id_transaction + "' checked></td>";
                            trRow += "<td>" + value.transaction_code + "</td>";
                            trRow += "<td>" + value.service_name + "</td>";
                            trRow += "<td style='max-width: 250px'>" + value
                                .description + "</td>";
                            trRow += "<td>" + value.payment_method + "</td>";
                            trRow += "<td><input type='hidden' name='prices[" + value
                                .id_transaction + "]' value='" + value.service_price +
                                "'>" + value.service_price + "</td>";
                        });
                        $("#transaction_ready_to_bill tbody").html(trRow);
                    }
                })
            });
            $("#select_all").change(function() {
                var status = $(this).is(":checked") ? true : false;
                $(".ids").prop("checked", status);
            });
        })
    </script>
@endsection
