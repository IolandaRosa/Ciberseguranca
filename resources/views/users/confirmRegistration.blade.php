@extends('master')

@section('title', 'Confirm registration')

@section('content')

	<div>
		<confirm-registration :userId="{{$user}}"> </confirm-registration>
	</div>

@endsection
@section('pagescript')
<script src="../js/app.js"></script>
@stop 	
