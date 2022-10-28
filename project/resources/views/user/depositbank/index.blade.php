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
                                    <th>{{ __('Deposit No') }}</th>
                                    <th>{{ __('Bank Name') }}</th>
                                    <th>{{ __('Bank SWIFT') }}</th>
                                    <th>{{ __('Bank IBAN') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end">{{ __('Details') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($deposits as $deposit)
                                    @php
                                        @$subbank = App\Models\SubInsBank::where('id', $deposit->sub_bank_id)->with('subInstitution')->first();
                                        if($subbank->hasGateway()){
                                          @$data = App\Models\BankAccount::whereUserId(auth()->id())->where('subbank_id', $subbank->id)->where('currency_id', $deposit->currency_id)->first();
                                        } else {
                                          @$data = App\Models\BankPoolAccount::where('bank_id', $subbank->id)->where('currency_id', $deposit->currency_id)->first();
                                        }
                                    @endphp
                                    <tr>
                                        <td data-label="{{ __('Incoming Date') }}">
                                          {{date('d-M-Y',strtotime($deposit->created_at))}}
                                        </td>
                                        <td data-label="{{ __('Deposit No') }}">
                                          {{$deposit->deposit_number}}
                                        </td>
                                        <td data-label="{{ __('Bank Name') }}">
                                          {{$subbank->name}}
                                        </td>
                                        <td data-label="{{ __('Bank SWIFT') }}">
                                          {{$data->swift ?? ''}}
                                        </td>
                                        <td data-label="{{ __('Bank IBAN') }}">
                                          {{$data->iban ?? ''}}
                                        </td>
                                        <td data-label="{{ __('Amount') }}" id="li_amount">
                                            {{$deposit->amount}}{{$deposit->currency->symbol}}
                                        </td>

                                        <td data-label="{{ __('Status') }}">
                                          @if($deposit->status == "complete")
                                            <span class="badge bg-success">Complete</span>
                                          @elseif ($deposit->status == "pending")
                                            <span class="badge bg-warning">Pending</span>
                                          @else
                                            <span class="badge bg-danger">Reject</span>
                                          @endif
                                        </td>
                                        <td data-label="@lang('Details')" class="text-end">
                                          <button class="btn btn-primary btn-sm details"
                                            data-data="{{json_encode($data ?? '')}}"
                                            data-hasgateway = "{{json_encode($subbank->hasGateway())}}"
                                            data-subbank="{{json_encode($subbank ?? '')}}"
                                            data-deposit="{{json_encode($deposit)}}"
                                            data-amount="{{$deposit->amount}}{{$deposit->currency->symbol}}"
                                          >@lang('Details')</button>
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
        <h3>@lang('Deposit Details')</h3>
        <p class="bank_details"></p>
        <ul class="list-group details-list mt-2">
            <li class="list-group-item">@lang('Receiver Name')<span id="user_name"></span></li>
            <li class="list-group-item">@lang('Receiver Address')<span id="user_address"></span></li>
            <li class="list-group-item">@lang('Bank Name')<span id="bank_name"></span></li>
            <li class="list-group-item">@lang('Bank Address')<span id="bank_address"></span></li>
            <li class="list-group-item">@lang('Bank IBAN')<span id="bank_iban"></span></li>
            <li class="list-group-item">@lang('Bank SWIFT')<span id="bank_swift"></span></li>
            <li class="list-group-item">@lang('Amount')<span id="amount"></span></li>
            <li class="list-group-item">@lang('Description')<span id="bank_details"></span></li>
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
          if($(this).data('hasgateway')) {
            const user = $(this).data('deposit').user;
            $('#user_name').text(user.company_name ?? user.name);
            $('#user_address').text(user.company_address ?? user.address);
          } else {
            $('#user_name').text($(this).data('subbank').sub_institution.name);
            $('#user_address').text($(this).data('subbank').sub_institution.address);
          }
          $('#bank_name').text($(this).data('subbank').name);
          $('#bank_address').text($(this).data('subbank').address);
          $('#bank_iban').text($(this).data('data').iban);
          $('#bank_swift').text($(this).data('data').swift);
          $('#bank_details').text($(this).data('deposit').details + " / " + $(this).data('deposit').deposit_number);
          $('#amount').text($(this).data('amount'));
          $('#modal-success').modal('show');
      })
    </script>
@endpush

