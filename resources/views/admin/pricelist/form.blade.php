@extends('admin.index')
@section('content')
<div class="kt-portlet">
  <div class="kt-portlet__head">
    <div class="kt-portlet__head-label">
      <h3 class="kt-portlet__head-title text-primary">
        {{ strtoupper($title) }} - <?=(isset($detail)) ? 'EDIT' : 'NEW';?>
      </h3>
    </div>
  </div>
  <div class="col-md-9 col-xs-12">
  <!--begin::Form-->
  <?php 
    if (isset($detail) && !is_null($detail)) {
      $url = route('pricelist.update', [$detail->id_service_pricelist]);
      $method = 'PUT';
      $price = $detail->price;
      $id_currency = $detail->id_currency;
      $date_start_price = $detail->date_start_price;
      $date_end_price = $detail->date_end_price;
      $id_service = $detail->id_service;
    }else{
      $url = route('pricelist.store');
      $method = 'POST';
      $price = 0;
      $id_currency = 0;
      $date_start_price = Date('Y-m-d');
      $date_end_price = Date('Y-m-d');
      $id_service = \Request()->id_service;
    }
  ?>
  <form class="kt-form" action=" {{ $url }}" method="POST" enctype="multipart/form-data">
    @method($method)
    {{ csrf_field() }}
    <input type="hidden" name="id_service" value="{{ $id_service }}">

    <div class="kt-portlet__body">
      <div class="form-group form-group-last">
        @include('admin.messages')
      </div>
      <div class="form-group">
        <label>Currency</label>
        <select name="id_currency" class="form-control"> 
        @foreach($currencies as $cu)
          <option value="{{ $cu->id_currency}}" <?=($cu->id_currency == $id_currency) ? 'selected' : '';?>> {{ $cu->currency_name }}</option>
        @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Price</label>
        <input type="text" name="price" class="form-control" value="<?=(!empty(old('price'))) ? old('price') : $price ;?>" placeholder="Enter price">
      </div>
      <div class="form-group">
        <label>Start Date</label>
        <input type="text" name="date_start_price" class="form-control datepicker" value="<?=(!empty(old('date_start_price'))) ? old('date_start_price') : $date_start_price ;?>" placeholder="YYYY-mm-dd">
      </div>
      <div class="form-group">
        <label>End Date</label>
        <input type="text" name="date_end_price" class="form-control datepicker" value="<?=(!empty(old('date_end_price'))) ? old('date_end_price') : $date_end_price ;?>" placeholder="YYYY-mm-dd">
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary" name="submit">Save</button>
        <a href="{{ route('member.pricelist.index', ['id_service' => $id_service]) }}" class="btn btn-secondary">Cancel</a>
      </div>
    </div>
  </form>
  <!--end::Form-->
  </div>
  <script type="text/javascript">
    $(function(){
      $(".datepicker").datepicker({ 
        format: 'yyyy-mm-dd'
      });  
    })
    
  </script>
</div>
@endsection