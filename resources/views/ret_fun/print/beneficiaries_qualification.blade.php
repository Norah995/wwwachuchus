@extends('print_global.print')
@section('content')
<div>
    @include('affiliates.print.full_personal_info', ['affiliate'=>$affiliate])
    @include('ret_fun.print.applicant', ['applicant'=>$applicant])
    @include('ret_fun.print.beneficiaries_list', ['beneficiaries'=>$beneficiaries])
</div>
@endsection