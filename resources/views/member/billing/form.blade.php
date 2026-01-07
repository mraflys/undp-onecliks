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
                        <div class="col-lg-12">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="alert alert-info py-2 mb-2">
                                        <input type="checkbox" id="select_all_pages" name="select_all" value="1"
                                            class="mr-2" />
                                        <strong>Select All Services</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" id="search_transaction" class="form-control"
                                        placeholder="Search by TID, Service Name, or Description..." />
                                </div>
                            </div>
                            <div style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
                                <table class="table table-striped table-bordered table-sm" id="transaction_ready_to_bill">
                                    <thead class="thead-light">
                                        <th width="60" class="text-center"><input type="checkbox" id="select_all" />
                                        </th>
                                        <th width="120">TID</th>
                                        <th>Service Name</th>
                                        <th>Description</th>
                                        <th width="100">Payment</th>
                                        <th width="100">Amount</th>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="mt-2 d-flex justify-content-between align-items-center" id="pagination_controls">
                                <div id="pagination_info" class="text-muted small"></div>
                                <div id="pagination_buttons"></div>
                            </div>
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

            // Store checked items across pages
            var checkedItems = {};
            var currentPage = 1;
            var currentAgency = null;
            var searchQuery = '';
            var allData = [];

            function filterData(data) {
                if (!searchQuery) return data;

                var query = searchQuery.toLowerCase();
                return data.filter(function(item) {
                    return item.transaction_code.toLowerCase().indexOf(query) !== -1 ||
                        item.service_name.toLowerCase().indexOf(query) !== -1 ||
                        item.description.toLowerCase().indexOf(query) !== -1;
                });
            }

            function loadTransactions(page) {
                if (!currentAgency) return;

                $.ajax({
                    url: "<?= URL::to('member-area/billing/data/transaction_ready_to_bill') ?>/" +
                        currentAgency,
                    data: {
                        page: page,
                        search: searchQuery
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $("#transaction_ready_to_bill tbody").html(
                            "<tr><td colspan='6' class='text-center'><i class='fa fa-spinner fa-spin'></i> Loading...</td></tr>"
                        );
                    },
                    success: function(response) {
                        currentPage = response.current_page;
                        allData = response.data;
                        renderTable(response.data);
                        renderPagination(response);
                    }
                });
            }

            function renderTable(data) {
                $("#transaction_ready_to_bill tbody").empty();

                if (data.length === 0) {
                    $("#transaction_ready_to_bill tbody").html(
                        "<tr><td colspan='6' class='text-center text-muted'>No data found</td></tr>"
                    );
                    updateSelectAllState();
                    return;
                }

                var trRow = "";
                $.each(data, function(k, value) {
                    // Check if item is explicitly checked (has a price value stored)
                    var isChecked = checkedItems.hasOwnProperty(value.id_transaction) && checkedItems[value
                        .id_transaction] !== false;
                    trRow += "<tr>";
                    trRow += "<td class='text-center'><input type='checkbox' class='ids' data-id='" + value
                        .id_transaction + "' value='" + value.id_transaction + "'" + (isChecked ?
                            " checked" : "") + "></td>";
                    trRow += "<td><small>" + value.transaction_code + "</small></td>";
                    trRow += "<td><small>" + value.service_name + "</small></td>";
                    trRow += "<td><small>" + value.description + "</small></td>";
                    trRow += "<td><small>" + value.payment_method + "</small></td>";
                    trRow += "<td class='text-right'><input type='hidden' class='price-input' data-id='" +
                        value.id_transaction + "' value='" + value.service_price + "'><small>" + value
                        .service_price + "</small></td>";
                    trRow += "</tr>";
                });

                $("#transaction_ready_to_bill tbody").html(trRow);
                updateSelectAllState();
            }

            function renderPagination(response) {
                var info = "";
                var buttons = "";

                if (response.last_page > 1) {
                    // Info text
                    var start = (response.current_page - 1) * response.per_page + 1;
                    var end = Math.min(response.current_page * response.per_page, response.total);
                    info = 'Showing ' + start + '-' + end + ' of ' + response.total + ' services';

                    // Pagination buttons - Tampilkan maksimal 10 page numbers di tengah
                    buttons += '<ul class="pagination pagination-sm mb-0">';

                    // Previous button
                    if (response.current_page > 1) {
                        buttons += '<li class="page-item"><a class="page-link" href="#" data-page="' + (response
                            .current_page - 1) + '">‹</a></li>';
                    } else {
                        buttons += '<li class="page-item disabled"><span class="page-link">‹</span></li>';
                    }

                    var maxPagesToShow = 10; // Maksimal 10 nomor halaman yang ditampilkan

                    if (response.last_page <= maxPagesToShow + 2) {
                        // Jika total pages <= 12, tampilkan semua
                        for (var i = 1; i <= response.last_page; i++) {
                            var active = i === response.current_page ? 'active' : '';
                            buttons += '<li class="page-item ' + active +
                                '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
                        }
                    } else {
                        // Tampilkan: 1 ... [range 10 pages] ... last

                        // Hitung range 10 halaman di sekitar current page
                        var halfRange = Math.floor(maxPagesToShow / 2);
                        var rangeStart = Math.max(2, response.current_page - halfRange);
                        var rangeEnd = Math.min(response.last_page - 1, rangeStart + maxPagesToShow - 1);

                        // Adjust jika rangeEnd mentok ke last_page
                        if (rangeEnd === response.last_page - 1) {
                            rangeStart = Math.max(2, rangeEnd - maxPagesToShow + 1);
                        }

                        // Tampilkan halaman pertama
                        if (response.current_page === 1) {
                            buttons +=
                                '<li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>';
                        } else {
                            buttons +=
                                '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
                        }

                        // Ellipsis setelah halaman 1
                        if (rangeStart > 2) {
                            buttons += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }

                        // Tampilkan range 10 halaman
                        for (var i = rangeStart; i <= rangeEnd; i++) {
                            var active = i === response.current_page ? 'active' : '';
                            buttons += '<li class="page-item ' + active +
                                '"><a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
                        }

                        // Ellipsis sebelum halaman terakhir
                        if (rangeEnd < response.last_page - 1) {
                            buttons += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }

                        // Tampilkan halaman terakhir
                        if (response.current_page === response.last_page) {
                            buttons += '<li class="page-item active"><a class="page-link" href="#" data-page="' +
                                response.last_page + '">' + response.last_page + '</a></li>';
                        } else {
                            buttons += '<li class="page-item"><a class="page-link" href="#" data-page="' + response
                                .last_page + '">' + response.last_page + '</a></li>';
                        }
                    }

                    // Next button
                    if (response.current_page < response.last_page) {
                        buttons += '<li class="page-item"><a class="page-link" href="#" data-page="' + (response
                            .current_page + 1) + '">›</a></li>';
                    } else {
                        buttons += '<li class="page-item disabled"><span class="page-link">›</span></li>';
                    }

                    buttons += '</ul>';
                }

                $('#pagination_info').html(info);
                $('#pagination_buttons').html(buttons);
            }

            function updateSelectAllState() {
                var allChecked = true;
                $('.ids').each(function() {
                    if (!$(this).is(':checked')) {
                        allChecked = false;
                        return false;
                    }
                });
                $('#select_all').prop('checked', allChecked);
            }

            // Handle agency change
            $('#id_agency_unit').change(function() {
                currentAgency = $(this).val();
                checkedItems = {};
                currentPage = 1;
                searchQuery = '';
                $('#search_transaction').val('');
                $('#select_all_pages').prop('checked', false);
                loadTransactions(1);
            });

            // Handle search input
            var searchTimer;
            $('#search_transaction').on('keyup', function() {
                searchQuery = $(this).val();

                // Debounce search - tunggu 500ms setelah user berhenti mengetik
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    currentPage = 1; // Reset ke page 1 saat search
                    loadTransactions(1);
                }, 500);
            });

            // Handle pagination clicks
            $(document).on('click', '#pagination_buttons a', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                if (page) {
                    loadTransactions(page);
                }
            });

            // Handle select all on current page
            $("#select_all").change(function() {
                var status = $(this).is(":checked");
                $(".ids").each(function() {
                    $(this).prop("checked", status);
                    var id = $(this).data('id');
                    if (status) {
                        var price = $('.price-input[data-id="' + id + '"]').val();
                        checkedItems[id] = price;
                    } else {
                        checkedItems[id] = false;
                    }
                });
            });

            // Handle individual checkbox changes
            $(document).on('change', '.ids', function() {
                var id = $(this).data('id');
                if ($(this).is(':checked')) {
                    var price = $('.price-input[data-id="' + id + '"]').val();
                    checkedItems[id] = price;
                } else {
                    checkedItems[id] = false;
                }
                updateSelectAllState();
            });

            // Before form submit, add all checked items as hidden inputs
            $('form').submit(function(e) {
                // Remove old hidden inputs
                $('.dynamic-input').remove();

                // If select all pages is checked, just add the flag
                if ($('#select_all_pages').is(':checked')) {
                    // select_all input already exists in HTML
                    return true;
                }

                // Otherwise add individual checked items
                for (var id in checkedItems) {
                    if (checkedItems[id] !== false) {
                        $(this).append('<input type="hidden" class="dynamic-input" name="ids[' + id +
                            ']" value="' + id + '">');
                        $(this).append('<input type="hidden" class="dynamic-input" name="prices[' + id +
                            ']" value="' + checkedItems[id] + '">');
                    }
                }
            });
        })
    </script>
@endsection
