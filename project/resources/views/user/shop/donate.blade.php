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
          {{__('Campaign')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">

            <a class="btn btn-primary d-sm-inline-block" href="{{route('user.shop.index')}}">
              <i class="fas fa-backward me-1"></i> {{__('Back')}}
            </a>
      </div>
    </div>
  </div>
</div>


<div class="page-body">
    <div class="container-xl">
        <div class="card card-lg">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="card">
                    <img class="back-preview-image"
                        src="{{asset('assets/images')}}/{{$data->logo}}"
                    alt="Campaign Logo">
                    <!-- Card body -->
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5 class="h4 mb-2 font-weight-bolder">{{__('Campaign Title: ')}}{{$data->title}}</h5>
                                <h5 class="mb-1">{{__('Category: ')}} {{$data->category->name}}</h5>
                                <h5 class="mb-1">{{__('Organizer: ')}} {{$data->user->name}}</h5>
                                <h5 class="mb-1">{{__('Goal: ')}} {{$data->currency->symbol}}{{$data->goal}}</h5>
                                @php
                                    $total = DB::table('campaign_donations')->where('campaign_id', $data->id)->where('status', 1)->sum('amount');
                                @endphp
                                <h5 class="mb-1">{{__('FundsRaised: ')}} {{$data->currency->symbol}}{{$total}}</h5>
                                <h5 class="mb-1">{{__('Deadline: ')}} {{$data->deadline}}</h5>
                                <h5 class="mb-3">{{__('Created Date:')}} {{$data->created_at}}</h5>
                                <h6 class="mb-3">{{__('Description:')}} {{$data->description}}</h6>
                            </div>
                        </div>
                    </div>
                </div>
              </div>
              <div class="col-md-3">
              </div>
              <div class="col-md-5 text-end ">
                <form action="{{route('user.merchant.campaign.pay')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-selectgroup row">
                        <label class="form-selectgroup-item">
                            <input type="radio" name="payment" value="bank_pay" id="bank_pay" class="form-selectgroup-input select_method" >
                            <span class="form-selectgroup-label">
                                <i class="fas fa-credit-card me-2"></i>
                                @lang('Pay with Bank')</span>
                        </label>
                        <label class="form-selectgroup-item">
                            <input type="radio" name="payment" value="gateway" id="gateway" class="form-selectgroup-input select_method" >
                            <span class="form-selectgroup-label">
                                <i class="fas fa-dollar-sign me-2"></i>
                                @lang('Pay with gateways')</span>
                        </label>
                        <label class="form-selectgroup-item">
                            <input type="radio" name="payment" value="crypto" id="crypto" class="form-selectgroup-input select_method" >
                            <span class="form-selectgroup-label">
                                <i class="fas fa-coins me-2"></i>
                                @lang('Pay with Crypto')</span>
                        </label>
                        <label class="form-selectgroup-item">
                        <input type="radio" name="payment" value="wallet" id="wallet" class="form-selectgroup-input select_method" checked>
                        <span class="form-selectgroup-label">
                            <i class="fas fa-wallet me-2"></i>
                            @lang('Pay with customer wallet')
                            </span>
                        </label>

                    </div>
                    <div class="form-group ms-5 mt-5 text-start" id="bank_part" style="display: none">
                        <label class="form-label">{{__('Bank Account')}}</label>
                        <select name="bank_account" id="bank_account" class="form-control">
                            @if(count($bankaccounts) != 0)
                            <option value="">Select</option>
                              @foreach($bankaccounts as $account)
                                  <option value="{{$account->id}}">{{$account->subbank->name}}</option>

                              @endforeach
                            @else
                            <option value="">There is no bank account for this currency.</option>

                            @endif
                          </select>
                    </div>

                    <div class="form-group ms-5 mt-5 text-start" >
                        <label class="form-label">{{__('Amount')}}</label>
                        <input name="amount" id="amount" class="form-control shadow-none col-md-4"  type="number" min="1" max="{{$data->goal}}" required>
                    </div >
                    <div class="form-group ms-5 mt-5 text-start" >
                        <label class="form-label">{{__('description')}}</label>
                        <input name="description" id="description" class="form-control shadow-none col-md-4"  type="text"  required>
                    </div >
                    <input type="hidden" name="campaign_id" value="{{$data->id}}">
                    <input type="hidden" name="user_id" value="{{auth()->id()}}">

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-block">{{__('Pay')}} <i class="ms-2 fas fa-long-arrow-alt-right"></i></button>
                    </div>
                </form>
              </div>
            </div>

          </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script type="text/javascript">
"use strict";
$('.select_method').on('click', function() {
    if ($(this).attr('id') == 'bank_pay') {
        $("#bank_account").prop('required',true);
        document.getElementById("bank_part").style.display = "block";
    }
    else {
        $("#bank_account").prop('required',false);
        document.getElementById("bank_part").style.display = "none";
    }
})
</script>
@endpush
