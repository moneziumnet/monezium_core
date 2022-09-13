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
                                              {{ $data->bank->title}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Beneficiary Name') }}">
                                            <div>
                                              {{$data->account_name}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Details') }}">
                                            <div class="btn-list">
                                                <a href="{{route('user.beneficiaries.show',$data->id)}}" class="btn btn-primary">
                                                  {{__('Details')}}
                                                </a>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Edit') }}">
                                            <div class="btn-list">
                                                <a href="{{route('user.beneficiaries.edit',$data->id)}}" class="btn btn-primary">
                                                  {{__('Edit')}}
                                                </a>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Action') }}">
                                            <div class="btn-list">
                                                <a href="{{route('user.other.send',$data->id)}}" class="btn btn-primary">
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
                                    <th>{{ __('Account Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
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
                                            <td data-label="{{ __('Account Name') }}">{{ $receiver != NULL ? $receiver->name : 'User Deleted' }}</td>
                                          @endif

                                          @if (!$data->receiver_id)
                                            @php
                                              $beneficiary = App\Models\Beneficiary::whereId($data->beneficiary_id)->first();
                                            @endphp
                                            <td data-label="{{ __('Account Name') }}">{{ $beneficiary != NULL ? $beneficiary->account_name : 'deleted' }}</td>
                                          @endif
                                          <td data-label="{{ __('Type') }}">{{ $data->type }} {{ __('Bank') }}</td>
                                          <td data-label="{{ __('Amount') }}">{{$data->amount}}</td>
                                          <td data-label="{{ __('Status') }}">
                                            @if ($data->status == 1)
                                              <span class="badge bg-success">{{ __('Completed')}}</span>
                                            @elseif($data->status == 2)
                                              <span class="badge bg-danger">{{ __('Rejected')}}</span>
                                            @else
                                              <span class="badge bg-warning">{{ __('Pending')}}</span>
                                            @endif
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


@endsection

@push('js')

@endpush

