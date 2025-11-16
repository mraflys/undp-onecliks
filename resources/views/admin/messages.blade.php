@if (!is_null(session('message_error')))
	<div class="alert alert-danger alert-dismissible">
  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  	<h6>{{ session('message_error') }}</h6>
	</div>
@endif

@if (session('message_success'))
	<div class="alert alert-success alert-dismissible">
  	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  	<h6>{{ session('message_success') }}</h6>

	</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
	
@endif