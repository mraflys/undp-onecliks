@extends('admin.index')
@section('content')
@include('admin.messages')

<?php
  if (isset($detail)){
    $url = route('mybillings.glje_update', [$detail->id_glje]);
    $id_glje = $detail->id_glje;
  }else{
    $url = route('mybillings.glje_create');
    $id_glje = "";
  }

  function generate_table_rows($rows, $checked = "", $no = 1) {
    $trows = "";
    foreach ($rows as $row){
        $trows .= "<tr>
          <td>
          <input type=\"checkbox\" class=\"ids agency_".$row->id_agency_unit."\" 
          id=\"id_".$row->id_transaction."\" name=\"id_transactions[]\" value=\"".$row->id_transaction."\" ".$checked."/></td>
          <td style=\"text-align: center;\">".$no."</td>
          <td>".$row->transaction_code."</td>
          <td>".$row->service_name."</td>
          <td>".$row->agency_unit_code."</td>
          <td align='right'>".number_format($row->service_price, 2)."</td>
        </tr>";
        $no++;
    }
    return $trows;
  }
?>
<div class="row">
  <div class="col-sm-12">
    <h4 class="alert alert-primary">{{ $title }}</h4>
    <div class='col-sm-9'>
      <?php 
      $count = 0;
      foreach ($headers as $k => $v){ ?>
        <span style='display:inline-block'>
            <input type="checkbox" class='agency_list' id='agency_<?=$k;?>' value="<?=$k;?>" <?=$id_glje == '' ? 'checked':'';?>/>
            <label for="agency_<?=$k;?>"><?=$v;?> &nbsp;</label>
        </span>
      <?php 
        // $count++;
        // if ($count > 10) { echo "<br>"; $count = 0; }
      } ?>
      <br>
      <div id="loadingDiv"></div>
    </div>

    <form action="{{ $url }}" method="POST">
    {{ csrf_field() }}
      <div class="col-sm-9" style="overflow-y: scroll; max-height: 700px">
        <table id='tbl' class="table table-striped table-bordered">
          <thead>
              <tr>
                <th><input id="select_all" type="checkbox" <?=$id_glje == '' ? 'checked':'';?> /></th>
                <th>No</th>
                <th>Ticket</th>
                <th>Service Name</th>
                <th>Agency</th>
                <th>Price</th>
              </tr>
          </thead>
          <tbody>
          <?php
          $no = 1;
          if (isset($current_glje) && !empty($current_glje)){
            echo generate_table_rows($current_glje, "checked", 1);
          }
          if (empty($glje)){
            echo "<tr><td colspan='6'>No GLJE Transaction yet.</td></tr>";
          }
          else {
            echo generate_table_rows($glje, "", (isset($current_glje) ? count($current_glje) : 1) ); 
          } 
          ?>
          </tbody>
      </table>
      </div>
      <div class="col-sm-12">
        <p class="text-left">
          <hr>
          <a class="btn btn-default" href="{{ route('mybillings.glje_index') }}">Back</a>
          <button class="btn btn-primary">Save</button>
        </p>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
    $("#select_all").click(function () {
      $("#loadingDiv").html("<p>loading...</p>");
      if ($(this).prop("checked") == true) {
          $(".ids").prop("checked", true);
      } else {
          $(".ids").prop("checked", false);
      }
      $("#loadingDiv").html("");
    });
    $(".ids").click(function () {
        var all = true;
        $(".ids").each(function () {
            all = all & $(this).prop("checked");
        });
        $("#select_all").prop("checked", all);
    });

    $('.agency_list').on('click', function (e) {
        var id = $(this).attr('id');
        if ($(this).prop("checked") == true) {
            $('.' + id).prop("checked", true);
        } else {
            $('.' + id).prop("checked", false);
        }
    });
    </script>

@endsection