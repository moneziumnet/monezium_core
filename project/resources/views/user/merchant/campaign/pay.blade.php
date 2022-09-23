<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{__($gs->title)}} - @lang('Campaign : '.$data->name) </title>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}

    <link rel="shortcut icon" href="{{asset('assets/images/'.$gs->favicon)}}">
    <link href="{{ asset('assets/user/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{asset('assets/user/css/tabler.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/tabler-flags.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/tabler-payments.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/tabler-vendors.min.css')}}" rel="stylesheet"/>
	<link rel="stylesheet" href="{{asset('assets/front/css/toastr.min.css')}}">
    <link href="{{asset('assets/user/css/demo.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/custom.css')}}" rel="stylesheet"/>
    @stack('css')
  </head>

  <body>
    <div class="wrapper mb-3">
          <div class="page-wrapper">
            <div class="container-xl">
              <!-- Page title -->
              <div class="page-header text-white d-print-none">
                <div class="row align-items-center">
                  <div class="col">
                    <h2 class="page-title text-dark">
                        @lang('Campaign Pay')
                    </h2>
                  </div>
                </div>
              </div>
            </div>
            <div class="page-body">
                <div class="container-xl">
                    <div class="card card-lg">
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-4">
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
                          <div class="col-md-5 ">
                            <form action="{{route('user.merchant.campaign.pay')}}" method="post" class="text-end" enctype="multipart/form-data">
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
                                    <label class="form-label required">{{__('Bank Account')}}</label>
                                    <select name="bank_account" id="bank_account" class="form-control">
                                        @if(count($bankaccounts) != 0)
                                        <option value="">{{__('Select')}}</option>
                                          @foreach($bankaccounts as $account)
                                              <option value="{{$account->id}}" data-data="{{$account}}" data-bank="{{$account->subbank}}" data-user="{{$account->user->name}}">{{$account->subbank->name}}</option>

                                          @endforeach
                                        @else
                                        <option value="">{{__('There is no bank account for this currency.')}}</option>

                                        @endif
                                      </select>
                                </div>

                                <div id="bank_account_part" style="display: none;">
                                    <div class="form-group ms-5 mt-2 text-start" >
                                        <label class="form-label">{{__('Receiver Name')}}</label>
                                        <input name="receiver_name" id="receiver_name" class="form-control shadow-none col-md-4"  type="text" readonly>
                                    </div >
                                    <div class="form-group ms-5 mt-2 text-start" >
                                        <label class="form-label">{{__('Bank Name')}}</label>
                                        <input name="bank_name" id="bank_name" class="form-control shadow-none col-md-4"  type="text" readonly>
                                    </div >
                                    <div class="form-group ms-5 mt-2 text-start" >
                                        <label class="form-label">{{__('Bank Address')}}</label>
                                        <input name="bank_address" id="bank_address" class="form-control shadow-none col-md-4"  type="text" readonly>
                                    </div >
                                    <div class="form-group ms-5 mt-2 text-start" >
                                        <label class="form-label">{{__('Bank IBAN')}}</label>
                                        <input name="bank_iban" id="bank_iban" class="form-control shadow-none col-md-4"  type="text" readonly>
                                    </div >
                                    <div class="form-group ms-5 mt-2 text-start" >
                                        <label class="form-label">{{__('Bank SWIFT')}}</label>
                                        <input name="bank_swift" id="bank_swift" class="form-control shadow-none col-md-4"  type="text" readonly>
                                    </div >
                                </div>

                                <div class="form-group ms-5 mt-5 text-start" >
                                    <label class="form-label required">{{__('Amount')}}</label>
                                    <input name="amount" id="amount" class="form-control shadow-none col-md-4"  type="number" min="1" max="{{$data->goal}}" required>
                                </div >
                                <div class="form-group ms-5 mt-5 text-start" >
                                    <label class="form-label required">{{__('description')}}</label>
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

                        <p class="text-muted text-center mt-5">@lang('Thank you very much for doing business with us. We look forward to working with
                            you again!')<br> <small class="mt-5">@lang('All right reserved ') <a href="{{url('/')}}">{{$gs->title}}</a></small></p>
                      </div>
                    </div>
                </div>
            </div>
          </div>
      </div>
      <!-- Tabler Core -->
      <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>
    <script src="{{asset('assets/user/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('assets/user/js/tabler.min.js')}}"></script>
    <script src="{{asset('assets/user/js/demo.min.js')}}"></script>
    <script src="{{asset('assets/front/js/custom.js')}}"></script>
    <script src="{{asset('assets/user/js/notify.min.js')}}"></script>
    <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>

      {{-- @include('notify.alert') --}}
      <script>
        'use strict';

        @if(Session::has('message'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
            toastr.success("{{ session('message') }}");
        @endif

        @if(Session::has('error'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
            toastr.error("{{ session('error') }}");
        @endif

        @if(Session::has('info'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
            toastr.info("{{ session('info') }}");
        @endif

        @if(Session::has('warning'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
            toastr.warning("{{ session('warning') }}");
        @endif
        $('.select_method').on('click', function() {
            if ($(this).attr('id') == 'bank_pay') {
                $("#bank_account").prop('required',true);
                document.getElementById("bank_part").style.display = "block";
            }
            else {
                $("#bank_account").prop('required',false);
                document.getElementById('bank_account_part').style.display = "none";
                document.getElementById("bank_part").style.display = "none";
            }
            if ($(this).attr('id') == 'crypto') {
                    window.location.href = "{{route('user.merchant.campaign.crypto.link', $data->id)}}"
                }
            })
        $('#bank_account').on('change', function() {
            console.log('test');
            var selected = $('#bank_account option:selected').data('data');
            var bank = $('#bank_account option:selected').data('bank');
            var user = $('#bank_account option:selected').data('user');
            if(selected){
            $('#receiver_name').val(user);
            $('#bank_name').val(bank.name);
            $('#bank_address').val(bank.address);
            $('#bank_iban').val(selected.iban);
            $('#bank_swift').val(selected.swift);
            $('#bank_swift').val(selected.swift);
                document.getElementById('bank_account_part').style.display = "block";
            } else{
                document.getElementById('bank_account_part').style.display = "none";
            }
        })
      </script>
      @stack('js')
    </body>
</html>
