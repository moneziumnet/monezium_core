@extends('layouts.user')

@push('css')

@endpush

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
            @foreach ($virtualcards as $item)
            <div class="col-lg-4 mb-2">
                <div class="card">
                  <!-- Card body -->
                  <div class="card-body">
                    <div class="row justify-content-between align-items-center">
                      <div class="col">
                        <span class="text-primary ">{{$currency->symbol.number_format($item->amount, 2, '.', '')}}</span> @if($item->status==0) <span class="badge badge-pill badge-danger">Terminated</span> @elseif($item->status==1) <span class="badge badge-pill badge-success">Active</span> @elseif($item->status==2) <span class="badge badge-pill badge-danger">Blocked</span>@endif
                      </div>
                      <div class="col-auto nav-item dropdown">
                        <a class="mr-0 nav-link" data-bs-toggle="dropdown">
                          <i class="fas fa-chevron-circle-down "></i>
                        </a>
                        <div class="dropdown-menu">
                          <a href="{{route('user.card.transaction', ['id'=>$item->id])}}" class="dropdown-item"><i class="fas fa-sync"></i>{{__(' Transactions')}}</a>
                          <a data-bs-toggle="modal" data-bs-target="#modal-more{{$item->id}}"  class="dropdown-item"><i class="fas fa-credit-card"></i>{{__(' Card Details')}}</a>
                            @if($item->status==1)
                              <a data-bs-toggle="modal" data-bs-target="#modal-formfund{{$item->id}}" href="" class="dropdown-item"><i class="fas fa-money-bill-wave-alt"></i>{{__('Fund Card')}}</a>
                              <a data-toggle="modal" data-target="#modal-formwithdraw{{$item->id}}" href="" class="dropdown-item"><i class="fas fa-arrow-circle-down"></i>{{__('Withdraw Money')}}</a>
                              {{-- <a href="{{route('terminate.virtual', ['id'=>$item->id])}}" class="dropdown-item"><i class="fas fa-ban"></i>{{__('Terminate')}}</a>
                              <a href="{{route('block.virtual', ['id'=>$item->id])}}" class="dropdown-item"><i class="fas fa-sad-tear text-danger"></i>{{__('Freeze')}}</a> --}}
                            @elseif($item->status==2)
                              {{-- <a href="{{route('unblock.virtual', ['id'=>$item->id])}}" class="dropdown-item"><i class="fas fa-smile text-success"></i>{{__('Unfreeze')}}</a> --}}
                            @endif
                        </div>
                      </div>
                    </div>
                    <div class="my-2">
                      <span class="h6 surtitle text-gray  mb-2">
                      {{$item->first_name}} {{$item->last_name}}- {{$item->card_type}}
                      </span>
                      <div class="card-serial-number h1 text-primary ">
                        <div>{{$item->card_pan}}</div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col">
                        <span class="h6 surtitle text-gray ">Expiry date</span>
                        <span class="d-block h3 text-primary ">{{$item->expiration}}</span>
                      </div>
                      <div class="col">
                        <span class="h6 surtitle text-gray ">CVV</span>
                        <span class="d-block h3 text-primary ">{{$item->cvv}}</span>
                      </div>
                    </div>
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
                    <input name="first_name" id="first_name" class="form-control" placeholder="{{__('First Name')}}" type="text"  required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required">{{__('Last Name')}}</label>
                    <input name="last_name" id="last_name" class="form-control" placeholder="{{__('Last Name')}}" type="text"  required>
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

@foreach($virtualcards as $k=>$val)
<div class="modal fade" id="modal-formwithdraw{{$val->id}}" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered" role="document">
      <div class="modal-content">
      <div class="modal-body p-0">
          <div class="card bg-white border-0 mb-0">
          <div class="card-header">
              <h3 class="mb-0 font-weight-bolder">{{__('Withdraw funds from Virtual Card')}}</h3>
              <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="card-body">
              {{-- <form method="post" action="{{route('user.card.withdraw')}}">
              @csrf
              <input type="hidden" name="id" value="{{$val->card_hash}}">
              <div class="form-group row">
                  <label class="col-form-label col-lg-12">{{__('Amount')}}</label>
                  <div class="col-lg-12">
                      <div class="input-group">
                          <div class="input-group-prepend">
                              <span class="input-group-text">{{$currency->symbol}}</span>
                          </div>
                          <input type="number" step="any" name="amount" id="amounttransfer4{{$val->id}}" class="form-control" max="{{$val->amount}}" required>
                      </div>
                      <p class="form-text text-xs">Charge is {{$set->virtual_charge}}% +  {{$currency->symbol.$xf->rate*$set->virtual_chargep}}.</p>
                  </div>
              </div>
              <div class="text-right">
                  <button type="submit" class="btn btn-neutral btn-block my-4">{{__('Withdraw Funds')}} <span id="resulttransfer4{{$val->id}}"></span></button>
              </div>
              </form> --}}
          </div>
          </div>
      </div>
      </div>
  </div>
</div>
{{-- <div class="modal fade" id="modal-formfund{{$val->id}}" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered" role="document">
      <div class="modal-content">
      <div class="modal-body p-0">
          <div class="card bg-white border-0 mb-0">
          <div class="card-header">
              <h3 class="mb-0 font-weight-bolder">{{__('Add Funds to Virtual Card')}}</h3>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="card-body">
              <form method="post" action="{{route('fund.virtual')}}">
              @csrf
              <input type="hidden" name="id" value="{{$val->card_hash}}">
              <div class="form-group row">
                  <label class="col-form-label col-lg-12">{{__('Amount')}}</label>
                  <div class="col-lg-12">
                      <div class="input-group">
                          <div class="input-group-prepend">
                              <span class="input-group-text">{{$currency->symbol}}</span>
                          </div>
                          <input type="number" step="any" name="amount" id="amounttransfer5{{$val->id}}" class="form-control" max="{{$set->vc_max-$val->amount}}" required>
                          <input type="hidden" value="{{$set->virtual_charge}}" id="vtransfer3{{$val->id}}">
                          <input type="hidden" value="{{$set->virtual_chargep}}" id="vtransferx{{$val->id}}">
                      </div>
                      <p class="form-text text-xs">Charge is {{$set->virtual_charge}}% +  {{$currency->symbol.$xf->rate*$set->virtual_chargep}}.</p>
                  </div>
              </div>
              <div class="text-right">
                  <button type="submit" class="btn btn-neutral btn-block my-4">{{__('Pay')}} <span id="resulttransfer5{{$val->id}}"></span></button>
              </div>
              </form>
          </div>
          </div>
      </div>
      </div>
  </div>
</div> --}}
<div class="modal fade" id="modal-more{{$val->id}}" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="mb-0 font-weight-bolder">{{__('Card Details')}}</h3>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>State: {{$val->state}}</p>
          <p>City: {{$val->city}}</p>
          <p>Zip Code: {{$val->zip_code}}</p>
          <p>Address: {{$val->address}}</p>
        </div>
      </div>
  </div>
</div>
@endforeach
@endsection

@push('js')
<script>
  'use strict';
  $('#create_form').on('click', function(){
    $('#modal-form').modal('show');
  })
</script>
@endpush
