@extends('admin.index')
@section('content')
@include('admin.messages')

<p>
  <a href="{{ route('mybillings.glje_add') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add New </a>
</p>
<hr>
<div class="col-sm-12">
  <table class="table table-striped" id="mytable"> 
  	<thead>
  		<tr>
        <th># Total Transactions</th>
        <th>Created At</th>
        <th>Last Downloaded At</th>
        <th>GLJE No</th>
        <th>Option</th>
      </tr>
  	</thead>
  	<tbody>
      @foreach($list as $l)
        <tr>
          <td>{{ $l->trans }}</td>
          <td>{{ $l->date_created }}</td>
          <td>{{ $l->date_downloaded }}</td>
          <td>{{ $l->glje_no }}</td>
          <td>
            <a href="{{ route('mybillings.glje_show', [$l->id_glje]) }}" class="bt btn-default" title="view"> <i class="fa fa-eye"></i> </a> &nbsp;
            @if ($l->glje_no == "")
              <a href="{{ route('mybillings.glje_edit', [$l->id_glje]) }}" class="bt btn-default" title="edit"> <i class="fa fa-edit"></i> </a> &nbsp;
              <a href="{{ route('mybillings.glje_delete', [$l->id_glje]) }}" class="bt btn-default" title="delete" onclick="return confirm('Are you sure?')"> <i class="fa fa-trash"></i></a> &nbsp;
            @endif
            <a href="{{ route('mybillings.glje_download', [$l->id_glje]) }}" class="bt btn-default" title="download"> <i class="fa fa-download"></i> </a> &nbsp;
          </td>
        </tr>
      @endforeach 
    </tbody>
  </table>
</div>
<script type="text/javascript">
  $(function(){
    $("#mytable").DataTable();
  })
</script>
@endsection