@extends('layouts.user')

@push('css')
    
@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('QR CODE')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        <div class="row row-cards">
            <div class="col-12">
              <div class="qr--code">
                <div class="card">
                    <div class="card-body text-center">
                        <div >
                            <img src="{{generateQR($user->email)}}" class="" alt="">
                        </div>
                        <h6 class="mt-4">{{$user->email}}</h6>
                        <div class="mt-3">
                            <a href="{{route('user.merchant.download.qr',$user->email)}}" class="btn btn-primary btn-lg btn-download">@lang('Download')</a>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

@endsection