@extends('layouts.user')

@push('css')

@endpush

@section('title', __('Withdraw Crypto'))

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
        @include('user.ex_payment_tab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Withdraws (Crypto)')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">

            <a href="{{ route('user.cryptowithdraw.create') }}" class="btn btn-primary d-sm-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
              {{__('Create new Withdraw')}}
            </a>
          </div>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    @if (count($withdraws) == 0)
                        <h3 class="text-center py-5">{{__('No Withdraws Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Sender Address') }}</th>
                                    <th>{{ __('Reciever Address') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Details') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($withdraws as $withdraw)
                                    <tr>
                                        <td data-label="{{ __('Date') }}">
                                        <div>
                                          {{date('d-M-Y',strtotime($withdraw->created_at))}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Sender Address') }}">
                                        <div>
                                            {{Get_Wallet_Address(auth()->id(), $withdraw->currency->id)}}
                                        </div>
                                      </td>
                                        <td data-label="{{ __('Reciever Address') }}">
                                          <div>
                                            {{ str_dis($withdraw->sender_address) }}
                                          </div>
                                        </td>

                                        <td data-label="{{ __('Amount') }}">
                                          <div id="li_amount">
                                            {{ $withdraw->currency->symbol.$withdraw->amount }}
                                          </div>
                                        </td>

                                        <td data-label="{{ __('Status') }}">
                                          <div>
                                            @php
                                            if ($withdraw->status == 1) {
                                                $status  = __('Completed');
                                            } elseif ($withdraw->status == 2) {
                                                $status  = __('Rejected');
                                            } else {
                                                $status  = __('Pending');
                                            }
                                            @endphp
                                            {{ ucfirst($status) }}
                                          </div>
                                        </td>
                                        <td data-label="@lang('Details')" class="text-end">
                                            <button class="btn btn-primary btn-sm details" data-data="{{json_encode($withdraw)}}" data-address="{{Get_Wallet_Address(auth()->id(), $withdraw->currency->id)}}" >@lang('Details')</button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $withdraws->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
        <h3>@lang('Withdraw Details')</h3>
        <ul class="list-group mt-2">
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Sender Address')<span id="crypto_address" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Receiver Address')<span id="sender_address" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Transaction ID')<a id="trnx_url" href="" target="_blank"><span id="trnx" style="margin-left: 60px"></span></a></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Amount')<span id="amount" style="margin-left: 60px"></span></li>
        </ul>
        </div>
    </div>
    </div>
</div>


@endsection

@push('js')
<script type="text/javascript">
    'use strict';
      $('.details').on('click', function() {
          $('#crypto_address').text($(this).data('address'));
          $('#sender_address').text($(this).data('data').sender_address);
          var code = $(this).data('data').currency.code;
          if (code == 'BTC') {
            $('#trnx_url').attr('href','https://www.blockchain.com/explorer/transactions/btc/' + $(this).data('data').hash);
          }
          else if(code == 'TRON' || code == 'USDT(TRON)') {
            $('#trnx_url').attr('href','https://tronscan.org/#/transaction/' + $(this).data('data').hash);
          }
          else {
            $('#trnx_url').attr('href','https://etherscan.io/tx/' + $(this).data('data').hash);
          }
          $('#trnx').text($(this).data('data').hash);
          $('#amount').text($(this).data('data').amount + $(this).data('data').currency.code);
          $('#modal-success').modal('show');

      })
    </script>
@endpush

