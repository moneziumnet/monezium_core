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
                            <input type="radio" name="payment" value="gateway" class="form-selectgroup-input" >
                            <span class="form-selectgroup-label">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-credit-card" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <rect x="3" y="5" width="18" height="14" rx="3"></rect>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                    <line x1="7" y1="15" x2="7.01" y2="15"></line>
                                    <line x1="11" y1="15" x2="13" y2="15"></line>
                                </svg>
                                @lang('Pay with gateways')</span>
                        </label>
                        <label class="form-selectgroup-item">
                        <input type="radio" name="payment" value="wallet" class="form-selectgroup-input" checked="">
                        <span class="form-selectgroup-label">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-wallet" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"></path>
                                <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"></path>
                            </svg>
                            @lang('Pay with customer wallet')
                            </span>
                        </label>

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


