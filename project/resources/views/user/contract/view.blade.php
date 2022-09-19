@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Contract')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{route('user.contract.index')}}" class="btn btn-primary"><i class="fas fa-backward me-1"></i> {{__(' Back')}}</a>
        </div>
      </div>
      <div class="col-sm-12 text-right" style="text-align: right">
        <a href="{{route('user.contract-pdf', $data->id)}}">
          <i class="fas fa-file-pdf" aria-hidden="true"></i> {{__('PDF')}}
        </a> &nbsp;
      </div>
    </div>

  </div>
</div>

<div class="container-xl mt-3 mb-3">
    <div class="card card-lg">
      <div class="card-body">
        <div class="row">
            <div class="text-center">
                <h1>
                    {{__($data->title)}}
                </h1>
            </div>
            <div class="mt-5 mb-3">
                @foreach ($information as $title => $text)
                    <h2 class="ms-1">{{ $title }}</h2>
                    <p>{!!nl2br($text)!!}</p>
                @endforeach
            </div>
            @if ($data->status == 1)

                <div class="wrapper-image-preview  col-md-6">
                    <p class="text-muted text-center"> {{__('Contractor signed')}} </p>
                    <div class="box full-width">
                        <div class="back-preview-image" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                    </div>
                </div>
                <div class="wrapper-image-preview col-md-6">
                    <p class="text-muted text-center">{{ __('Client signed')}}</p>
                    <div class="box full-width">
                        <div class="back-preview-image" style="background-image: url({{ $data->customer_image_path ? asset('assets/images/'.$data->customer_image_path) : '' }});"></div>
                    </div>
                </div>
            @else
            <div class="wrapper-image-preview col-md-6">
                    <p class="text-muted text-center">{{$data->contracter_image_path ? __('Contractor signed') : __('Contractor not signed')}}</p>
                    <div class="box full-width">
                        <div class="back-preview-image" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                    </div>
            </div>
            <div class="wrapper-image-preview col-md-6">
                <p class="text-muted text-center">{{$data->customer_image_path ? __('Client signed') : __('Client not signed')}}</p>
                <div class="box full-width">
                    <div class="back-preview-image" style="background-image: url({{ $data->customer_image_path ? asset('assets/images/'.$data->customer_image_path) : '' }});"></div>
                </div>
            </div>
            @endif

        </div>
        <p class="text-muted text-center mt-5">{{__('Thank you very much for doing new contract. We look forward to working with
            you again!')}} <br> <small class="mt-5">{{__('All right reserved ')}} <a href="{{url('/')}}">{{$gs->title}}</a></small></p>

      </div>
    </div>
</div>
@endsection

@push('js')



    <script type="text/javascript">
        'use strict';
    </script>

@endpush

