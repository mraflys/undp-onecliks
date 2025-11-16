@extends('admin.index')
@section('content')
@include('admin.messages')

<h4 class="alert alert-primary">GLJE DETAIL</h4>
<div class="col-sm-6">
  <form action="{{ route('mybillings.glje_update_no', [$detail->id_glje]) }}" method="POST">
  {{ csrf_field() }}
    <table class="table">
      <tr>
        <td>GLJE No</td>
        <td><input type="text" name="glje_no" class="form-control" value="{{ $detail->glje_no }}" /></td>
      </tr>
      <tr>
        <td>last Downloaded At</td>
        <td>{{ $detail->date_downloaded }}</td>
      </tr>
      <tr>
        <td colspan="2" align="right">
          <a class="btn btn-default" href="{{ route('mybillings.glje_index') }}">Back</a>
          <button class="btn btn-primary">Save</button>
        </td>
      </tr>
    </table>
  </form>
</div>
<div class="col-sm-12" style="overflow-x: scroll" >
  <div style="overflow-x: scroll;min-width: 2000px">
    {{ $content }}
  </div>
</div>
@endsection