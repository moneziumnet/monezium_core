@extends('layouts.user')

@push('css')

@endpush

@section('contents')

<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Create Voucher')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">

            <a href="{{ route('user.vouchers') }}" class="btn btn-primary d-sm-inline-block">
                <i class="fas fa-backward me-1"></i> {{__('Voucher List')}}
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
                <div class="card p-3 p-sm-4 p-lg-5">
                    @includeIf('includes.flash')
                    <form id="voucher-form" action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label class="form-label required">{{__('Amount')}}
                                {{-- <code class="limit">{{__('min : '.$charge->minimum.' '.$gs->currency_code)}} -- {{__('max : '.$charge->maximum.' '.$gs->currency_code)}}</code>  --}}
                            </label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" value="{{ old('amount') }}" min="1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">{{__('Select Wallet')}}</label>
                            <select class="form-select wallet shadow-none" name="wallet_id">
                                <option value="" selected>@lang('Select')</option>
                                @foreach ($wallets as $wallet)
                                    <option value="{{$wallet->id}}" data-rate="{{getRate($wallet->currency)}}" data-code="{{$wallet->currency->code}}">{{$wallet->currency->code}} -- ({{amount($wallet->balance,$wallet->currency->type,2)}})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
        <div class="row row-deck row-cards mt-3">
            <div class="col-md-12">
                <h2> @lang('Recent Vouchers')</h2>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive">
                      <table class="table table-vcenter card-table table-striped">
                        <thead>
                          <tr>
                            <th>@lang('Voucher Code')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Date')</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($recentVouchers as $item)
                            <tr>
                              <td data-label="@lang('Voucher Code')">{{$item->code}}</td>
                              <td data-label="@lang('Amount')">{{numFormat($item->amount)}} {{$item->currency->code}}</td>
                              <td data-label="@lang('Status')">
                                @if ($item->status == 0)
                                   <span class="badge bg-secondary">@lang('unused')</span>
                                @elseif ($item->status == 1)
                                    <span class="badge bg-success">@lang('used')</span>
                                @endif
                              </td>
                              <td data-label="@lang('Date')">{{dateFormat($item->created_at)}}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center" colspan="12">@lang('No data found!')</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
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


        $('.wallet').on('change',function () {

            var code = $('.wallet option:selected').data('code')


            if($('#amount').val() == ''){
              toastr.options = { "closeButton" : true, "progressBar" : true };
              toastr.error('Please provide the amount first.');
              return false
            }

            var totalAmount = parseFloat($('#amount').val());

            $('.exAmount').text($('#amount').val() +' '+ code)
            $('.total_amount').text(totalAmount.toFixed(8) + ' '+code)

            $('.info').removeClass('d-none')

        })

        $('.create').on('click',function () {
            if($('#amount').val() == ''){
              toastr.options = { "closeButton" : true, "progressBar" : true };
              toastr.error('Please provide the amount first.');
              return false
            }
            if($('.wallet option:selected').val() == ''){
              toastr.options = { "closeButton" : true, "progressBar" : true };
              toastr.error('Please select the wallet.');
              return false
            }
            $('#modal-success').modal('show')
        })
    </script>
@endpush
