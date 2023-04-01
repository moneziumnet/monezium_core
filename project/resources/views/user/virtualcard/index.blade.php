@extends('layouts.user')

@push('css')

@endpush

@section('title', __('Cards'))

@section('contents')
<div class="page-body">
  <div class="container-xl">

    <div class="row align-items-center mb-2 mt-3">
    <div class="col">

        <div class="row justify-content-center">
            <h1>@lang('Card list')</h1>
        </div>
    </div>
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a id="create_form" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Create Card')}}
          </a>
        </div>
    </div>
    </div>
    <div class="row justify-content " style="max-height: 800px;overflow-y: scroll;">
        @if (count($virtualcards) != 0)
            @foreach ($virtualcards as $key => $item)
            <div class="col-lg-4 mb-2">
                <div class="card">
                  <!-- Card body -->
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="text-primary d-flex me-auto">
                        <h2 class="me-3">
                          {{$item->currency->code}}
                        </h2>
                        <h2>
                          {{number_format(user_wallet_balance($item->user_id, $item->currency_id, 2), 2, '.', '')}}{{$item->currency->symbol}}
                        </h2>
                      </div>
                      <div class="nav-item dropdown mb-1">
                        <a class="mr-0 nav-link" data-bs-toggle="dropdown">
                          <i class="fas fa-chevron-circle-down "></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                          <a href="{{route('user.card.transaction', ['id'=>$item->id])}}" class="dropdown-item"><i class="fas fa-sync me-2"></i>{{__(' Transactions')}}</a>
                            @if($item->status==1)
                              <a href="javascript:;" class="dropdown-item btn-details" data="{{$item->id}}"><i class="fas fa-money-bill-wave-alt me-2"></i>{{__('Card Details')}}</a>
                              <a href="javascript:;" class="dropdown-item btn-withdraw" data="{{$item->id}}"><i class="fas fa-arrow-circle-down me-2"></i>{{__('Withdraw Money')}}</a>
                            @endif
                        </div>
                      </div>
                    </div>
                    <hr class="my-0"/>
                    <h4 class="mt-3">Virtual Card</h4>
                    <h2 class="text-primary my-3">XXXX - {{substr($item->card_pan, 12, 4)}}</h2>
                    <h4>Expiration Date <span class="ms-3">{{$item->expiration}}</span></h4>
                    <h4>{{$item->first_name}} {{$item->last_name}}</h4>
                  </div>
                </div>
              </div>
            @endforeach
        @else
            <p class="text-center">@lang('NO Card FOUND')</p>
        @endif
    </div>
    <hr>
  </div>
</div>

<div class="modal modal-blur fade" id="modal-form" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{('Create Virtual Card')}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="{{route('user.card.store')}}" method="POST" id="withdraw_form" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label required">{{__('First Name')}}</label>
                    <input name="first_name" id="first_name" class="form-control" placeholder="{{__('First Name')}}" type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+"  required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required">{{__('Last Name')}}</label>
                    <input name="last_name" id="last_name" class="form-control" placeholder="{{__('Last Name')}}" type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+"  required>
                </div>

                <div class="form-group mb-3 mt-3">
                    <label class="form-label required">{{__('Select Currency')}}</label>
                    <select name="currency_id" id="currency_id" class="form-control" required>
                      <option value="">Select</option>
                      @foreach($currencylist as $currency)
                      <option value="{{$currency->id}}">{{$currency->code}}</option>
                      @endforeach
                    </select>

                </div>

                <div class="modal-footer">
                    <button  id="submit-btn" class="btn btn-primary">{{ __('Create') }}</button>
                </div>
            </form>
        </div>

      </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-withdraw" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
      <div class="modal-body p-0">
          <div class="card border-0 mb-0">
          <div class="card-header">
              <h3 class="mb-0 font-weight-bolder">{{__('Withdraw to current account')}}</h3>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="card-body">
              <form method="post" action="{{route('user.card.withdraw')}}">
                @csrf
                <input type="hidden" name="withdraw_id" id="withdraw_id">
                <div class="form-group mx-2">
                    <label class="form-label">{{__('Amount')}}</label>
                    <input type="number" step="any" name="amount" class="form-control" required>
                </div>
                <div class="text-right mx-2">
                    <button type="submit" class="btn btn-primary w-100 mt-4 mb-2">{{__('Confirm')}}</button>
                </div>
              </form>
          </div>
          </div>
      </div>
      </div>
  </div>
</div>

<div class="modal modal-blur fade" id="modal-details" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
      <div class="modal-body p-0">
          <div class="card border-0 mb-0">
            <div class="card-header">
                <h3 class="mb-0 font-weight-bolder">{{__('Card Details')}}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="card-body">
              <ul class="list-group mt-2 details-list">
                  <li class="list-group-item">@lang('Name')<span id="detail_name"></span></li>
                  <li class="list-group-item">@lang('Card No')<span id="detail_card_no"></span></li>
                  <li class="list-group-item">@lang('Expiration Date')<span id="detail_exp_date"></span></li>
                  <li class="list-group-item">@lang('CVV')<span id="detail_cvv"></span></li>
                  <li class="list-group-item">@lang('Wallet No')<span id="detail_wallet_no"></span></li>
              </ul>
              <button id="submit-btn" class="btn btn-primary col-12 mt-3" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
          </div>
      </div>
      </div>
  </div>
</div>
@endsection

@push('js')
<script>
  'use strict';
  $('#create_form').on('click', function(){
    $('#modal-form').modal('show');
  });
  $('.btn-withdraw').on('click', function() {
    $('#modal-withdraw').modal('show');
    $('#withdraw_id').val($(this).attr('data'));
  });
  $('.btn-details').on('click', function() {

    $.ajax({
      method:"GET",
      url:"{{route('user.card.detail')}}",
      data:{
        card_id: $(this).attr('data')
      },
      dataType:'JSON',
      success:function(res) {
        $('#detail_name').html(res.first_name + ' ' + res.last_name);
        $('#detail_card_no').html(res.card_pan.substr(0,4) + '-' + res.card_pan.substr(4,4) + '-' + res.card_pan.substr(8,4) + '-' + res.card_pan.substr(12,4));
        $('#detail_exp_date').html(res.expiration);
        $('#detail_cvv').html(res.cvv);
        $('#detail_wallet_no').html(res.wallet_no);
        $('#modal-details').modal('show');
      }
    })
  })
</script>
@endpush
