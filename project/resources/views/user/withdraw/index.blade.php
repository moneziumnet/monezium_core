@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.ex_payment_tab')
        <div class="d-flex flex-wrap justify-content-between mt-3">
        <div class="me-3">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Withdraws')}}
          </h2>
        </div>
        <div class="d-print-none">
          <div class="btn-list">
            <a href="{{ route('user.withdraw.create') }}" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
              {{__('Create Withdraws')}}
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
                    @if (count($withdraws) == 0)
                        <h3 class="text-center py-5">{{__('No Withdraw Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Withdraw Date') }}</th>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Details') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($withdraws as $key=>$withdraw)
                                      <tr>
                                          <td data-label="{{ __('Withdraw Date') }}">{{date('d-M-Y',strtotime($withdraw->created_at))}}</td>
                                          <td data-label="{{ __('Method') }}">{{$withdraw->method->name}}</td>
                                          <td data-label="{{ __('Amount') }}">{{ amount($withdraw->amount, $withdraw->currency->type, 2).$withdraw->currency->symbol }}</td>
                                          @if ($withdraw->status == '1')
                                          <td data-label="{{ __('Status') }}">{{ __('Accepted') }}</td>
                                          @elseif($withdraw->status == 2)
                                          <td data-label="{{ __('Status') }}">{{ __('Rejected') }}</td>
                                          @else
                                          <td data-label="{{ __('Status') }}">{{ __('Pending') }}</td>
                                          @endif

                                          <td data-label="{{ __('Details') }}">
                                            <button class="btn btn-primary btn-sm details" data-id="{{$withdraw->id}}">
                                              {{__('Details')}}
                                            </button>
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
<div class="modal modal-blur fade" id="modal-details" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="modal-status bg-primary"></div>
          <div class="modal-body text-center py-4">
              <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
              <h3>@lang('Withdraw Details')</h3>
              <div class="withdraw-details">

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
      var url = "{{url('user/withdraw/')}}"+'/'+$(this).data('id')
      $.get(url,function (res) {
          $('.withdraw-details').html(res)
          $('#modal-details').modal('show')
      })
  })
</script>
@endpush

