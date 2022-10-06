@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.ex_payment_tab')
        <div class="row align-items-center mt-3">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('External Payments')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('user.beneficiaries.create') }}" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
              {{__('Add Beneficiaries')}}
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
                    @if (count($beneficiaries) == 0)
                        <h3 class="text-center py-5">{{__('No Beneficiary Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Bank') }}</th>
                                    <th>{{ __('Beneficiary Name') }}</th>
                                    <th>{{ __('Details') }}</th>
                                    <th>{{ __('Edit') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($beneficiaries as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Bank') }}">
                                            <div>
                                              {{ $data->bank_name}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Beneficiary Name') }}">
                                            <div>
                                              {{$data->name}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Details') }}">
                                            <div class="btn-list">
                                                <button data-id="{{$data->id}}" class="btn btn-sm btn-primary beneficiary-details">
                                                  {{__('Details')}}
                                                </button>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Edit') }}">
                                            <div class="btn-list">
                                                <a href="{{route('user.beneficiaries.edit',$data->id)}}" class="btn btn-sm btn-primary">
                                                  {{__('Edit')}}
                                                </a>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Action') }}">
                                            <div class="btn-list">
                                                <a href="{{route('user.other.send',$data->id)}}" class="btn btn-sm btn-primary">
                                                  {{__('Send')}}
                                                </a>
                                            </div>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $beneficiaries->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Transfer Logs')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    @if (count($logs) == 0)
                        <h3 class="text-center py-5">{{__('No Transfer Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Transaction Number') }}</th>
                                    <th>{{ __('Beneficiary Name') }}</th>
                                    <th>{{ __('Sender Bank Name') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Details') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($logs as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Date') }}">{{ $data->created_at->toFormattedDateString() }}</td>
                                          <td data-label="{{ __('Transaction Number') }}">{{ $data->transaction_no }}</td>
                                          @if ($data->receiver_id)
                                            @php
                                              $receiver = App\Models\User::whereId($data->receiver_id)->first();
                                            @endphp

                                            <td data-label="{{ __('Account No') }}">{{ $receiver != NULL ? $receiver->account_number : 'User Deleted' }}</td>
                                            <td data-label="{{ __('Beneficiary Name') }}">{{ $receiver != NULL ? $receiver->name : 'User Deleted' }}</td>
                                          @endif

                                          @if (!$data->receiver_id)
                                            @php
                                              $beneficiary = App\Models\Beneficiary::whereId($data->beneficiary_id)->first();
                                            @endphp
                                            <td data-label="{{ __('Beneficiary Name') }}">{{ $beneficiary != NULL ? $beneficiary->name : 'deleted' }}</td>
                                          @endif
                                          @php
                                            $subbank = App\Models\SubInsBank::whereId($data->subbank)->first();
                                          @endphp
                                          <td data-label="{{ __('Sender Bank Name') }}">{{ $subbank ? $subbank->name : '' }}</td>
                                          <td data-label="{{ __('Amount') }}">{{$data->currency->symbol}}{{$data->amount}} {{$data->currency->code}}</td>
                                          <td data-label="{{ __('Status') }}">
                                            @if ($data->status == 1)
                                              <span class="badge bg-success">{{ __('Completed')}}</span>
                                            @elseif($data->status == 2)
                                              <span class="badge bg-danger">{{ __('Rejected')}}</span>
                                            @else
                                              <span class="badge bg-warning">{{ __('Pending')}}</span>
                                            @endif
                                          </td>
                                          <td>
                                            <button class="btn btn-primary btn-sm details" data-id="{{ $data->id }}">
                                              {{ __('Details') }}
                                            </button>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $logs->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-details" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="modal-status bg-primary"></div>
          <div class="modal-body text-center py-4">
              <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
              <h3>@lang('Transfer Log Details')</h3>
              <div class="transfer-log-details">

              </div>
          </div>
          <div class="modal-footer">
              <div class="w-100">
                  <div class="d-flex">
                          <a href="#" class="btn w-50 me-2" data-bs-dismiss="modal">
                              @lang('Close')
                          </a>
                          <a href="" id="copy_transfer" class="btn w-50" >
                            @lang('Copy Transaction')
                          </a>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<div class="modal modal-blur fade" id="modal-details-2" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="modal-status bg-primary"></div>
          <div class="modal-body text-center py-4">
              <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
              <h3>@lang('Beneficiary Details')</h3>
              <div class="beneficiary-details-info">

              </div>
          </div>
          <div class="modal-footer">
              <div class="w-100">
                  <div class="row">
                      <div class="col">
                          <a href="#" class="btn w-100" data-bs-dismiss="modal">
                              @lang('Close')
                          </a>
                      </div>
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

$('.details').on('click', function() {
    var url = "{{url('user/beneficiaries/details/')}}"+'/'+$(this).data('id')
    var copy_url = "{{url('user/other-bank/copy')}}" + '/' + $(this).data('id')
    $.get(url,function (res) {
        $('.transfer-log-details').html(res)
        $('#copy_transfer').attr('href', copy_url)
        $('#modal-details').modal('show')
    })
})

$('.beneficiary-details').on('click', function() {
    var url = "{{url('user/beneficiaries/show/')}}"+'/'+$(this).data('id')
    $.get(url,function (res) {
        $('.beneficiary-details-info').html(res);
        $('#modal-details-2').modal('show');
    })
})
</script>
@endpush

