@extends('layouts.merchant')

{{-- @section('title')
   @lang('QR Code')
@endsection

@section('breadcrumb')
 <section class="section">
    <div class="section-header">
        <h1>@lang('QR Code')</h1>
    </div>
</section>
@endsection --}}

@section('content')
<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between">
	<h5 class=" mb-0 text-gray-800 pl-3">{{ __('QR CODE') }}</h5>
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('merchant.dashboard')}}">{{ __('Dashboard') }}</a></li>
		<li class="breadcrumb-item"><a href="{{route('merchant.qr')}}">{{ __('QR CODE') }}</a></li>
	</ol>
	</div>
</div>

<div class="row mt-3">
    <div class="col-lg-12">
        <div class="qr--code">
            <div class="card">
                <div class="card-body text-center">
                    <div >
                        <img src="{{generateQR(merchant()->email)}}" class="w-100" alt="">
                    </div>
                    <h6 class="mt-4">{{merchant()->email}}</h6>
                    <div class="mt-3">
                        <a href="{{route('merchant.download.qr',merchant()->email)}}" class="btn btn-primary btn-lg btn-download">@lang('Download')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
