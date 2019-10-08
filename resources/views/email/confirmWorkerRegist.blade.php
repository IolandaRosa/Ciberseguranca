@extends('master')

@section('content')


<h4> Confirm registration </h4>

<p> Your email was registrated as a worker on the platform.</p>
</br>
<p> Please go to the following link to continue your registration:  </p>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td>
                        <a href="{{$actionUrl}}" class="btn btn-primary"> {{ $actionText }} </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</br>
</br>
<p> Thank you. </p> 

@endsection

@section('pagescript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.17.1/moment.min.js"></script>
<script src="js/app.js"></script>
@stop  
