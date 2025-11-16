@extends('admin.index')
@section('content')
<div class="row">
	<div class="col-sm-2" align="right">
		<img alt="Logo" src="<?=URL::to('/');?>/{{ \App\GeneralHelper::app_configs()->logo }}" class="kt-header__brand-logo-default"/>
	</div>
	<div class="col-sm-6">
		<h1>Contact Us </h1>
		<p style="font-size: 17px">We look forward to helping you with your problem or question.
			<br/>Please <b><a href="mailto:ict.id@undp.org?subject=One-Click Helpdesk">click here</a> </b>to submit your problem.
			<br/>We will respond to your request as soon as possible. Thank you</p>
		<br />
		<a href="{{ route('login') }}" class="btn btn-primary">Back to Sign In</a>
	</div>
</div>
@endsection