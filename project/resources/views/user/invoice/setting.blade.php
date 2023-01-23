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
            {{__('Invoice Setting')}}
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
                        @foreach ($invoice_type as $key=>$type)

                            <div class="col-md-6 mb-3">
                                <div class="form-label">{{__( $type.' Prefix ')}}</div>
                                @php
                                    $prefix = 'prefix_'.$type;
                                @endphp
                                <input type="text" pattern="[^()/><\][;!|]+" name="{{$prefix}}" class="form-control shadow-none" value="{{$invoice_setting->number_generator->$prefix ?? ''}}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-label">{{__( $type.' Number length ')}}</div>
                                @php
                                    $length = 'length_'.$type;
                                @endphp
                                <input type="number" name="{{$length}}" class="form-control shadow-none" value="{{$invoice_setting->number_generator->$length ?? ''}}"  min="4" max="10" required>
                            </div>
                        @endforeach

                        <div class="col-md-6 mb-3">
                            <div class="form-label">{{__('Invoice Template')}}</div>
                            @php
                                $type = $invoice_setting->template ?? '';
                            @endphp
                            <select class="form-select shadow-none" name="template" required>
                                <option value="" selected>{{__('Select')}}</option>
                                  <option value="Basic" {{$type == "Basic" ? 'Selected' : ''}} >{{__('Basic')}}</option>
                                  <option value="Classic" {{$type == "Classic" ? 'Selected' : ''}} >{{__('Classic')}}</option>
                                  <option value="Pro" {{$type == "Pro" ? 'Selected' : ''}} >{{__('Pro')}}</option>
                            </select>
                        </div>
                        <input type="hidden" name="user_id" value="{{auth()->id()}}" >

                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Confirm')}}</button>
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
