@extends('layouts.user')

@section('contents')

<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Merchant Setting')}}
          </h2>
        </div>
      </div>
    </div>
  </div>

<div class="container-xl mt-3 mb-3">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
            <div class="card-body">
                <form action="" id="form" method="post">
                  @csrf
                    <div class="row form-group">
                        <div class="col-md-6 mx-auto">
                            <div class="form-label">{{__( 'Address')}}</div>
                            <input type="email" name="address" class="form-control shadow-none" value="{{$setting->address ?? ''}}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-footer mx-auto">
                            <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Confirm')}}</button>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
    <script>
        'use strict';
    </script>
@endpush
