@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">

  <div class="page-header d-print-none">

  </div>
</div>
<div class="page-body">
  <div class="container-xl">

    <div class="row align-items-center mb-2">
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
                @if (isset($wallet_type[$item->wallet_type]))
                    <div class="col-sm-6 col-md-4 mb-3">
                        <div class="card h-100 card--info-item">
                        <div class="text-end icon">
                            <i class="fas ">
                                {{$item->currency->symbol}}
                            </i>
                        </div>
                        <div class="card-body">
                            <div class="h3 m-0 text-uppercase"> {{$wallet_type[$item->wallet_type]}}</div>
                            <div class="h4 m-0 text-uppercase"> {{ $item->wallet_no }}</div>
                            <div class="text-muted">{{ amount($item->balance,$item->currency->type,2) }}  {{$item->currency->code}}</div>
                        </div>
                        </div>
                    </div>
                @endif
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
                    <button  id="submit-btn" class="btn btn-primary">{{ __('Verify') }}</button>
                </div>
            </form>
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
  })
</script>
@endpush
