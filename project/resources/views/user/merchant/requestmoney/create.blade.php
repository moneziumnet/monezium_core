@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Request Now')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">

            <a href="{{ route('user.merchant.money.request.index') }}" class="btn btn-primary d-sm-inline-block">
                <i class="fas fa-backward me-1"></i> {{__('Request List')}}
            </a>
          </div>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-5">
                    @includeIf('includes.flash')
                    <form id="request-form" action="{{ route('user.merchant.money.request.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Account Email')}}</label>
                            <div class="input-group">
                                <input name="email" id="email" class="form-control camera_value" autocomplete="off" placeholder="{{__('user@mail.com')}}" type="email" value="{{ old('email') }}" min="1" required>
                                <button type="button"  data-bs-toggle="tooltip" data-bs-original-title="@lang('Scan QR code')" class="input-group-text scan"><i class="fas fa-qrcode"></i></button>
                            </div>
                        </div>


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Account Name')}}</label>
                            <input name="account_name" id="account_name" class="form-control" autocomplete="off" placeholder="{{__('Jhon Doe')}}" type="text" pattern="[^()/><\][\\\-;!|]+" value="{{ old('account_name') }}" min="1" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Account PhoneNumber')}}</label>
                            <input name="account_phone" id="account_phone" class="form-control" autocomplete="off" placeholder="{{__('+1234567890')}}" type="number" value="{{ old('account_phone') }}" min="1" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Select Shop')}}</label>
                            <select name="shop_id" id="shop_id" class="form-control" required>
                              <option value="">Select</option>
                              @foreach($shop_list as $shop)
                              <option value="{{$shop->id}}">{{$shop->name}}</option>
                              @endforeach
                            </select>
                        </div>


                        <div class="form-group mb-3 mt-3">
                          <label class="form-label required">{{__('Select Currency')}}</label>
                          <select name="wallet_id" id="wallet_id" class="form-control" required>
                            <option value="">Select</option>
                            @php
                            $modules = explode(" , ", auth()->user()->modules);
                            if(in_array('Crypto',$modules)){
                              $currencies = DB::table('currencies')->get();
                            }
                            else{
                                $currencies = DB::table('currencies')->where('type', 1)->get();
                            }
                            @endphp
                            @foreach($currencies as $currency)
                            <option value="{{$currency->id}}">{{$currency->code}}</option>
                            @endforeach
                          </select>

                      </div>


                        <div class="form-group mb-3">
                            <label class="form-label required">{{__('Request Amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                        </div>

                        <div class="form-group mb-3 ">
                            <label class="form-label">{{__('Description')}}</label>
                            <textarea name="details" class="form-control nic-edit" cols="30" rows="5" placeholder="{{__('Receive account details')}}"></textarea>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100" disabled>{{__('Submit')}}</button>
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
    $("#email").on('change',function(){

      $.post("{{ route('user.username.email') }}",{email: $("#email").val(),_token:'{{csrf_token()}}'}, function(data){
        $("#account_name").val(data['name']);
        $("#account_phone").val(data['phone']);
        $(".submit-btn").prop( "disabled", false );
      });
    })
  </script>
@endpush
