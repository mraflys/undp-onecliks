@extends('admin.index')
@section('content')
  <h3>Welcome, {{ \Auth::user()->person_name }}</h3>
@endsection