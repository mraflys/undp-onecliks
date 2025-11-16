@extends('admin.index')
@section('content')

<form class="form-horizontal m-b-10" id="formReport" role="form" method="POST" action="{{ route('myreport.detail_post') }}">
    {{ csrf_field() }}

    <div id="loadingStatus"></div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Country Office</label>
        <div class="col-sm-9">
            <select name="agency_unit" class="form-control select2" id="agency_unit">
                <option>-- Select Office --</option>
                @foreach($agency as $a)
                    <option value="{{ $a->id_agency_unit }}">{{ $a->agency_unit_code }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Service Unit</label>
        <div class="col-sm-9">
            <select name="service_unit" class="form-control select2" id="service_unit">
                <option></option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Service</label>
        <div class="col-sm-9">
            <select name="service[]" class="form-control select2" id="service" multiple="multiple">
                <option></option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Authorized Date from</label>
        <div class="col-sm-9">
            <div class="input-group input-daterange">
                <input type="text" class="form-control" name="date1" id="date1">
                <div class="input-group-addon"> &nbsp;to </div>
                <input type="text" class="form-control" name="date2" id="date2">
            </div>
        </div>
    </div>
    <!-- <div class="form-group">
        <label class="col-sm-3 control-label">Finished Date from</label>
        <div class="col-sm-9">
            <div class="input-group input-daterange">
                <input type="text" class="form-control">
                <div class="input-group-addon"> &nbsp;to </div>
                <input type="text" class="form-control">
            </div>
        </div>
    </div> -->
    <div class="form-group">
        <label class="col-sm-3 control-label">Search for </label>
        <div class="col-sm-9">
            <input type="text" class="form-control" name="search">
        </div>
    </div>
    <input type="text" hidden name="all_report_submit" id="all_report_submit">
    <div class="form-group">
        <div class="col-sm-3"></div>
        <div class="col-sm-9">
            <button type="submit" name="report_submit" id="report_submit" class="btn btn-primary">Download</button>
            <button type="submit" name="all_report_submit_form" id="all_report_submit_form" value="1" class="btn btn-primary">Download All Report</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    function serviceList(idParent) {
        $.ajax({
          url: "<?=route('api-list-service-list-search-by');?>" + "?parent_only=1&id_parent=" + idParent,
          dataType: 'json',
          beforeSend: function(){
            $("#loadingStatus").html("Loading ....");
          },
          success: function(data){
            $("#loadingStatus").html("");
            $("#service").html("<option>--- Select Service ---</option>");
            $.each(data.data, function(k, value){
              $("#service").append("<option value='"+value.id_service+"'>"+value.service_name+"</option>");
            })
          }
        })
    }

    function agencyList(idParent) {
        $.ajax({
          url: "<?=route('api-list-agency-units-search-by');?>" + "?all=1&id_parent=" +idParent,
          dataType: 'json',
          beforeSend: function(){
            $("#loadingStatus").html("Loading ....");
          },
          success: function(data){
            $("#loadingStatus").html("");
            $("#service_unit").html("");
            $.each(data.data, function(k, value){
              $("#service_unit").append("<option value='"+value.id_agency_unit+"'>"+ value.agency_unit_code+"</option>");
            });
            serviceList($("#service_unit").val());
          }
        })
    }
    $(document).ready(function() {
        $("#report_submit").click(function(event) {
            event.preventDefault(); // Prevent the form from submitting

            // Check if any of the required fields are empty
            var agencyUnitValue = $("#agency_unit").val().trim();
            var serviceUnitValue = $("#service_unit").val().trim();
            // var serviceValue = $("#service").val().map(function(item) {
            //     return item.trim();
            // });
            if (agencyUnitValue === '' || serviceUnitValue === '') {
            
                alert("Input required: Please fill in Country Office, Service Unit.");
            } else {
                $("#all_report_submit").val("");
            
                $("#formReport").submit();
            }
        });
        $("#all_report_submit_form").click(function(event) {
            event.preventDefault(); // Prevent the form from submitting

            // Check if any of the required fields are empty
            var date1 = $("#date1").val().trim();
            var date2 = $("#date2").val().trim();

            if (date1 === '' && date2 === '') {
                alert("Input required: Please fill in Authorized Date from and to.");
            } else {
                $("#all_report_submit").val("1");
            
                $("#formReport").submit();
            }
        });
        $("#agency_unit").change(function(){
            agencyList($(this).val());
        });

        $("#service_unit").change(function(){
            serviceList($(this).val());
        });

        $('.input-daterange').datepicker();

        $('.select2').select2({
            placeholder: 'Select',
            allowClear: true
        });
    });
</script>

@endsection