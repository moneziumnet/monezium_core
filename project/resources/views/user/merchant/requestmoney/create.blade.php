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
                            <input name="email" id="email" class="form-control" autocomplete="off" placeholder="{{__('user@mail.com')}}" type="text" value="{{ old('email') }}" min="1" required>
                        </div>


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Account Name')}}</label>
                            <input name="account_name" id="account_name" class="form-control" autocomplete="off" placeholder="{{__('Jhon Doe')}}" type="text" value="{{ old('account_name') }}" min="1" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Account PhoneNumber')}}</label>
                            <input name="account_phone" id="account_phone" class="form-control" autocomplete="off" placeholder="{{__('+1234567890')}}" type="text" value="{{ old('account_phone') }}" min="1" required readonly>
                        </div>

                        <div class="form-group mb-3 mt-3">
                          <label class="form-label required">{{__('Select Currency')}}</label>
                          <select name="wallet_id" id="wallet_id" class="form-control" required>
                            <option value="">Select</option>
                            @foreach(DB::table('currencies')->get() as $currency)
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
    $("#account_name").on('click',function(){
      let email = $("#email").val();

      let url = `${mainurl}/user/username-by-email/${email}`;

      $.get(url, function(data){
        $("#account_name").val(data['name']);
        $("#account_phone").val(data['phone']);
        $(".submit-btn").prop( "disabled", false );
      });
    })
  </script>
@endpush
