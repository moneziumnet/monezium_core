@extends('layouts.user')

@push('css')

@endpush

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
            {{__('Incoming (Bank)')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">

            <a href="{{ route('user.depositbank.create') }}" class="btn btn-primary d-sm-inline-block">
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
                                    <th>{{ __('Incoming Date') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Account') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Details') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($deposits as $deposit)
                                    <tr>
                                        <td data-label="{{ __('Incoming Date') }}">
                                        <div>
                                          {{date('d-M-Y',strtotime($deposit->created_at))}}
                                        </div>
                                      </td>
                                        <td data-label="{{ __('Method') }}">
                                          <div>
                                            {{$deposit->method}}
                                          </div>
                                        </td>
                                        <td data-label="{{ __('Account') }}">
                                          <div>
                                            {{ auth()->user()->email }}
                                          </div>
                                        </td>

                                        <td data-label="{{ __('Amount') }}">
                                          <div id="li_amount">
                                            {{ showprice($deposit->amount,$deposit->currency) }}
                                          </div>
                                        </td>

                                        <td data-label="{{ __('Status') }}">
                                          <div>
                                            {{ ucfirst($deposit->status) }}
                                          </div>
                                        </td>
                                        <td data-label="@lang('Details')" class="text-end">
                                            @php
                                                $subbank = DB::table('sub_ins_banks')->where('id', $deposit->sub_bank_id)->first();
                                                $data = DB::table('bank_accounts')->whereUserId(auth()->id())->where('subbank_id', $subbank->id)->where('currency_id', $deposit->currency_id)->first();
                                            @endphp
                                            <button class="btn btn-primary btn-sm details" data-data="{{json_encode($data)}}" data-subbank="{{json_encode($subbank)}}" data-description="{{$deposit->details}}">@lang('Details')</button>
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
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Name')<span id="bank_name"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Address')<span id="bank_address"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Iban')<span id="bank_iban"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Bank Swift')<span id="bank_swift"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Amount')<span id="amount"></span></li>
            <li class="list-group-item d-flex justify-content-between">@lang('Description')<span id="bank_details"></span></li>
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
          $('#bank_name').text($(this).data('subbank').name);
          $('#bank_address').text($(this).data('subbank').address);
          $('#bank_iban').text($(this).data('data').iban);
          $('#bank_swift').text($(this).data('data').swift);
          $('#bank_details').text($(this).data('description'));
          $('#amount').text($('#li_amount').text());
          $('#modal-success').modal('show');

      })
    </script>
@endpush

