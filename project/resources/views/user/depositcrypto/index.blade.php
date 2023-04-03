@extends('layouts.user')

@push('css')

@endpush

@section('title', __('Incoming'))

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.deposittab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Incoming (Crypto)')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">

            <a href="{{ route('user.cryptodeposit.create') }}" class="btn btn-primary d-sm-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
              {{__('Create new Incoming')}}
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
                <div class="card">
                    @if (count($deposits) == 0)
                        <h3 class="text-center py-5">{{__('No Incoming Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Crypto Address') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th class="text-end">{{ __('Details') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($deposits as $deposit)
                                    <tr>
                                        <td data-label="{{ __('Date') }}">
                                        <div>
                                          {{date('d-M-Y',strtotime($deposit->created_at))}}
                                        </div>
                                      </td>
                                        <td data-label="{{ __('Crypto Address') }}">
                                          <div>
                                            {{str_dis($deposit->address)}}
                                          </div>
                                        </td>

                                        <td data-label="{{ __('Amount') }}">
                                          <div id="li_amount">
                                            {{ $deposit->currency->symbol.$deposit->amount }}
                                          </div>
                                        </td>

                                        <td data-label="@lang('Details')" class="text-end">
                                            <button class="btn btn-primary btn-sm details" data-data="{{json_encode($deposit)}}" >@lang('Details')</button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $deposits->links() }}
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
        <h3>@lang('Bank Details')</h3>
        <ul class="list-group mt-2">
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Receiver Crypto Address')<span id="address"  style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Amount')<span id="amount" style="margin-left: 60px"></span></li>
            <li class="list-group-item d-flex justify-content-center" style="word-break:break-all;"><img id="qrcode"></li>
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
          $('#address').text($(this).data('data').address);
          $('#amount').text($(this).data('data').amount+ $(this).data('data').currency.code);
          $('#qrcode').attr('src', `https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=${$(this).data('data').address}&choe=UTF-8`);
          $('#modal-success').modal('show');

      })
    </script>
@endpush

