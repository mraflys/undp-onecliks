<?php
  $btn_search_id = isset($btn_search_id) && !empty($btn_search_id) ? $btn_search_id : 'btnSearch';
  $start_date_id = isset($start_date_id) && !empty($start_date_id) ? $start_date_id : 'start_date';
  $end_date_id = isset($end_date_id) && !empty($end_date_id) ? $end_date_id : 'end_date';
?>

<div class="col-sm-6">
  <form class="kt-form" action="">
    <div class="kt-portlet__body">
      <!-- <div class="form-group">
        <label>Keyword</label>
        <input type="text" name="keyword" id="keyword" class="form-control">
      </div> --> 
      <div class="form-group">
        <label>Date</label>
        <table class="table">
          <tr>
            <td>
              From: <input type="date" name="start_date" id="<?=$start_date_id;?>" class="form-control" placeholder="YYYY-mm-dd">
            </td>
            <td>
              To: <input type="date" name="end_date" id="<?=$end_date_id;?>" class="form-control"  placeholder="YYYY-mm-dd">
            </td>
          </tr>
        </table>
      </div>
      <div class="form-group">
        <button class="btn btn-primary" id="<?=$btn_search_id;?>" type="button">Search</button>
      </div> 
    </div>
  </form>
</div>
@include("member.filter._date_range_js")