@extends('admin.index')
@section('content')
<div class="col-sm-12">
	<center>
		<h2>Your Request has been submitted </h2>
		<hr>
		<h4>Your Transaction Code is: </h4>
		<h3 class="text-bold text-success">{{ $detail->transaction_code }}</h3>
		<br>
		<a href='{{ route('myrequests.ongoing') }}' class="btn btn-primary">Back to List</a>
	</center>
</div>
@endsection