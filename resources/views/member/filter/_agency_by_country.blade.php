<?php
  $btn_search_id = isset($btn_search_id) && !empty($btn_search_id) ? $btn_search_id : 'btnSearch';
  $countries = \App\Country::list_with_cache();
  $hidden = isset($hidden) ? $hidden : [];
  $agency_api = isset($agency_api) ? $agency_api : null;
?>

<div class="col-sm-6">
  <div id="loadingStatus"></div>
  <form class="kt-form" action="">
    <div class="kt-portlet__body">
      <!-- <div class="form-group">
        <label>Keyword</label>
        <input type="text" name="keyword" id="keyword" class="form-control">
      </div> --> 
      <div class="form-group">
        <label>Status</label>
        <select class="form-control" id="status">
          <option value=""></option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
      <div class="form-group">
        <label>Country</label>
        <select class="form-control select2" id="id_country">
          <option></option>
          @foreach($countries as $c)
            <option value="{{ $c->id_country }}">{{ $c->country_name }}</option>
          @endforeach
        </select>
      </div>
      @if (!in_array('agency_unit', $hidden))
        <div class="form-group">
          <label for="exampleSelect1">Agency Unit</label>
          <select class="form-control select2" id="id_agency_unit" name="id_agency_unit_service"></select>
        </div>
      @endif
      <div class="form-group">
        <button class="btn btn-primary" id="<?=$btn_search_id;?>" type="button">Search</button>
      </div> 
    </div>
  </form>
</div>

<script type="text/javascript">
  function agencyUnitList(){
    var isAll = "<?=($agency_api == null) ? '' : '&all=true';?>";
    $.ajax({
      url: "<?=route('api-list-agency-units-search-by');?>" + "?id_country=" + $("#id_country").val() + isAll,
      dataType: 'json',
      beforeSend: function(){
        $("#loadingStatus").html("Loading ....");
      },
      success: function(data){
        $("#loadingStatus").html("");
        $("#id_agency_unit").html("<option value=''>--- Select Agency ---</option>");
        $.each(data.data, function(k, value){
          $("#id_agency_unit").append("<option value='"+value.id_agency_unit+"'>"+value.agency_unit_name+"</option>");
        })
      }
    })
  }

  $(function(){
    $(".select2").select2();
    $("#id_country").change(function() { agencyUnitList() });
  })
</script>