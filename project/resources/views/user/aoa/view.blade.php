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
          {{__('AoA')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{route('user.contract.aoa', $data->contract_id)}}" class="btn btn-primary"><i class="fas fa-backward me-1"></i> {{__(' Back')}}</a>
        </div>
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
            <div class="text-center mt-5">
                <p>
                    {{__($description)}}
                </p>
            </div>
            @if ($data->status == 1)
            <div class = "row">

                <div class="wrapper-image-preview col-6">
                    <p class="text-muted text-center"> {{__('Contracter')}} </p>
                    <div class="box full-width">
                        <div class="back-preview-image" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                    </div>
                </div>

                <div class="wrapper-image-preview col-6">
                    <p class="text-muted text-center"> {{__('Customer')}} </p>
                    <div class="box full-width">
                        <div class="back-preview-image" style="background-image: url({{ $data->customer_image_path ? asset('assets/images/'.$data->customer_image_path) : '' }});"></div>
                    </div>
                </div>
            </div>
            @else
                <p class="text-muted text-center mt-1">{{__('Someone did not signed')}}</p>
                <div class = "row">

                    <div class="wrapper-image-preview col-6">
                        <p class="text-muted text-center"> {{__('Contracter')}} </p>
                        <div class="box full-width">
                            <div class="back-preview-image" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                        </div>
                    </div>

                    <div class="wrapper-image-preview col-6">
                        <p class="text-muted text-center"> {{__('Customer')}} </p>
                        <div class="box full-width">
                            <div class="back-preview-image" style="background-image: url({{ $data->customer_image_path ? asset('assets/images/'.$data->customer_image_path) : '' }});"></div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
        <p class="text-muted text-center mt-5">{{__('Thank you very much for doing new AoA. We look forward to working with
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

