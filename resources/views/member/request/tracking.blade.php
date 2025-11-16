<?php 
  if ($print){
    $file = 'Tracking.xls';
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=$file");
  }
?>

@extends((!$print) ? 'admin.index' : 'admin.print_template')
@section('content')

<?php if ($print) echo '<html><head></head><body>'; ?>
@if (!$print)
  <div class="col-sm-6">
    <form name="tracking" method="post" action="{{ route('post.myrequests.tracking') }}">
      {{ csrf_field() }}
      <input type="hidden" name="with_id_agency_buyer" value="{{ isset($with_id_agency_buyer) ? $with_id_agency_buyer : 1 }}" >
      <div class="form-group">
        <label>Select Agency</label>
        <select name="agency" id="agency" class="form-control select2">
          <option>-- Select Agency --</option>
          <option value="{{ \Auth::user()->agency->parent->id_agency_unit }}">{{ \Auth::user()->agency->parent->agency_unit_name }}</option>
        </select>
      </div>
      <div class="form-group">
        <label>Select Category</label>
        <select name="category" class="form-control select2" id="category"></select>
      </div>
      <div class="form-group">
        <label>Transaction Code</label><br>
        <input type="text"  id="transaction_code" name="transaction_code" style="width: 250px; padding: 5px;" 
          value="<?=(isset($transaction_code) && !empty($transaction_code)) ? $transaction_code : '';?>">
        
        @if (isset($trackings) && !is_null($trackings) && count($trackings) > 0)
          <button id="search_transaction_code" type="button" class="btn btn-success">Search Current</button>
          <div id="searchStatus"></div>
        @endif
      </div>
      <div class="form-group">
        <label></label>
        <button class="btn btn-primary" type="submit" name="btn_submit" value="html"><i class="fa fa-search"></i> Track </button>
      </div>
    </form>
  </div>
@endif
<div class="col-sm-12"></div>
@if (isset($trackings) && !is_null($trackings))
  @if (!$print)
    <form name="tracking" method="post" action="{{ route('post.myrequests.tracking') }}">
      {{ csrf_field() }}
      <input type="hidden" name="with_id_agency_buyer" value="{{ $with_id_agency_buyer }}">
      <input type="hidden" name="category" value="{{ $category }}">
      <input type="hidden" name="transaction_code" value="{{ $transaction_code }}">
      <p><button class="btn btn-success" name="btn_submit" value="excel"><i class="fa fa-download"></i> Export</button></p>
    </form>
  @endif
  
  <?php $name = ""; ?>
  @foreach($trackings as $key => $data_value)
    <?php 
      if ($name != $data_value["name"]) {
        $name = $data_value['name'];
        echo '<h5 class="alert alert-info">'.$data_value["name"].'</h5>';
      }
    ?>
    <table class="table table-bordered">
      <thead>
        <tr style="background: #5578eb; color: #fff">
          <td>Transaction Code</td>
          <td style="width: 15%">Description</td>
          <td>Start Date</td>
          <?php for($i = 1; $i <= count($data_value['workflows']); $i++){
            echo "<td>Activity $i</td>";
          }?>
        </tr>
      </thead>
      <tr id="{{ $data_value["transaction_code"] }}">
        <td>{{ $data_value["transaction_code"] }}</td>  
        <td>{{ $data_value["description"] }}</td>  
        <td>{{ $data_value["date_authorized"] }}</td>  
      @foreach($data_value['workflows'] as $workflow)
        <?php 
          if ($workflow->delay > 0){
            $background = "#FCC";
          }else {
            $background = "#CFC";
          }
        ?>
        <td style="background: {{ $background }}">
          {{ ($workflow->date_end_actual != null) ? Date('d-M-Y', strtotime($workflow->date_end_actual)) : "" }}<br>
          {{ ($workflow->delay > 0 ) ? 'Delay '.$workflow->delay. ' day(s)' : '' }} <br>  
          {{ $workflow->workflow_name }} <br>  
        </td>  
      @endforeach
    </tr>
    </table>
  @endforeach
@endif
<script type="text/javascript">
  function serviceList(idCountry, idParent) {
    $.ajax({
      url: "<?=route('api-list-agency-units-search-by');?>" + "?id_parent=" + idParent + '&service_only=1&all=1',
      dataType: 'json',
      success: function(data){
        $("#category").html("");
        $.each(data.data, function(k, value){
          $("#category").append("<option value='"+value.id_agency_unit+ "'>"+value.agency_unit_name+"</option>");
        })
      }
    })
  }
  $(function(){
    $(".select2").select2();
    $('#agency').change(function(){
      serviceList(null, $("#agency").val());
    })
    $("#search_transaction_code").click(function(){
      var transactionCode = $("#transaction_code").val();
      if($("#" + transactionCode).length == 0) {
        $("#searchStatus").html("<p>" + transactionCode + " can't be found </p>");
      }else{
        document.getElementById(transactionCode).scrollIntoView();
      }
    })
  })
</script>

@if (!$print)
  @endsection
@else
  </body></html>
@endif