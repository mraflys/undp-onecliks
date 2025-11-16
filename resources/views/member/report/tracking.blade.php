@extends('admin.index')
@section('content')

<form class="form-horizontal m-b-10" role="form" method="POST" action="{{ route('myreport.tracking_post') }}">
    {{ csrf_field() }}

    <div id="loadingStatus"></div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Agency</label>
        <div class="col-sm-9">
            <select name="agency_unit" class="form-control select2" id="agency_unit" required>
                <option>-- Select Agency --</option>
                @foreach($agency as $a)
                    <option value="{{ $a->id_agency_unit }}">{{ $a->agency_unit_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Service Unit</label>
        <div class="col-sm-9">
            <select name="service_unit" class="form-control select2" id="service_unit" required>
                <option></option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-3"></div>
        <div class="col-sm-9">
            <button type="submit" class="btn btn-primary">Track</button>
        </div>
    </div>
</form>

<script type="text/javascript">
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
              $("#service_unit").append("<option value='"+value.id_agency_unit+"'>"+ value.agency_unit_name+"</option>");
            });
            serviceList($("#service_unit").val());
          }
        })
    }
    $(document).ready(function() {
        $("#agency_unit").change(function(){
            agencyList($(this).val());
        });

        $('.select2').select2({
            placeholder: 'Select',
            allowClear: true
        });
    });
</script>

@endsection