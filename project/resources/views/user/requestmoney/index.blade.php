@extends('layouts.user')

@push('css')

@endpush

@section('title', __('Request Money'))

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Request Money')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
          <div class="btn-list">
            <a href="{{ route('user.money.request.create') }}" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
              {{__('Add Request Money')}}
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
                    @if (count($requests) == 0)
                        <h3 class="text-center py-5">{{__('No Request Money Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Sender') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Receiver') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($requests as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Date') }}">
                                            <div>
                                              {{ $data->created_at->toFormattedDateString() }}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Sender') }}">
                                            <div>
                                              {{ auth()->user()->company_name ?? auth()->user()->name }}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Amount') }}">
                                            <div>
                                              {{ $data->amount.$data->currency->symbol }}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Receiver') }}">
                                            <div>
                                              {{ $data->receiver_name }}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Status') }}">
                                            @if($data->status == 1)
                                              @php
                                                  $bclass = "success";
                                                  $bstatus = "completed";
                                              @endphp
                                            @elseif($data->status == 2)
                                              @php
                                                  $bclass = "danger";
                                                  $bstatus = "cancelled";
                                              @endphp
                                            @else
                                              @php
                                                  $bclass = "warning";
                                                  $bstatus = "pending";
                                              @endphp
                                            @endif
                                            <div>
                                              <span class="badge bg-{{ $bclass }}">{{ $bstatus}}</span>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Details') }}">
                                            <div class="btn-list">
                                                <button data-id="{{$data->id}}" class="btn btn-primary btn-sm details">
                                                  {{__('Details')}}
                                                </button>
                                                @if($data->status == 0)
                                                <a href="javascript:;" class="btn btn-danger btn-sm details" data-bs-toggle="modal" data-bs-target="#confirm-delete" data-href="{{route('user.request.money.delete', $data->id)}}">
                                                  {{__('Delete')}}
                                                </a>
                                                @endif
                                            </div>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $requests->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      <div class="modal-status bg-danger"></div>
      <div class="modal-body text-center py-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
        <h3>{{__('Are you sure')}}?</h3>
        <div class="text-muted">{{__("You are about to delete this Request.")}}</div>
      </div>
      <div class="modal-footer">
        <div class="w-100">
          <div class="row">
            <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                {{__('Cancel')}}
              </a></div>
            <div class="col">
              <a href="javascript:;" class="btn btn-danger w-100 btn-ok">
                {{__('Delete')}}
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Receive Request Money')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                  @include('includes.user.form-both')
                    @if (count($receives) == 0)
                        <h3 class="text-center py-5">{{__('No Request Money Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Request From') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Details') }}</th>
                                    <th class="w-1"></th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($receives as $key=>$data)
                                    @php
                                        $from = App\Models\User::where('id',$data->user_id)->first();
                                    @endphp
                                      <tr>
                                          <td data-label="{{ __('Date') }}">
                                            <div>
                                              {{ $data->created_at->toFormattedDateString() }}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Request From') }}">
                                            <div>
                                              {{ $from != NULL ? ($from->company_name ?? $from->name) : 'User Deleted' }}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Amount') }}">
                                            <div>
                                              {{ $data->amount.$data->currency->symbol }}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Status') }}">
                                            @if($data->status == 1)
                                              @php
                                                  $bclass = "success";
                                                  $bstatus = "completed";
                                              @endphp
                                            @elseif($data->status == 2)
                                              @php
                                                  $bclass = "danger";
                                                  $bstatus = "cancelled";
                                              @endphp
                                            @else
                                              @php
                                                  $bclass = "warning";
                                                  $bstatus = "pending";
                                              @endphp
                                            @endif
                                            <div>
                                              <span class="badge bg-{{ $bclass }}">{{ $bstatus}}</span>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Details') }}">
                                            <div class="btn-list">
                                                <button data-id="{{$data->id}}" class="btn btn-sm btn-primary details">
                                                  {{__('Details')}}
                                                </button>
                                            </div>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $receives->links() }}
                    @endif
                </div>
            </div>


        </div>
    </div>
</div>

<div class="modal modal-blur confirm-modal fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="requestMoney" action="" method="post">
        @csrf
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-success"></div>

        <div class="modal-body text-center py-4">
          <p class="text-center">{{ __("You are about to change the status.") }}</p>
          <p class="text-center">{{ __("Do you want to proceed?") }}</p>
          @if($user->paymentCheck('Receive Request Money'))
          <div class="form-group mt-3 text-start" id="otp_body">
              <label class="form-label required">{{__('OTP Code')}}</label>
              <input name="otp_code" id="otp_code" class="form-control" placeholder="{{__('OTP Code')}}" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" value="{{ old('opt_code') }}" required>
          </div>
          @endif
        </div>
        <div class="modal-footer">
          <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</a>
          <button type="submit" id="sendprocess" class="btn shadow-none btn--success" data-bs-dismiss="modal">@lang('Proceed')</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal modal-blur confirm-modal fade" id="modal-cancel" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="cancelRequestMoney" action="" method="post">
        @csrf
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-success"></div>

        <div class="modal-body text-center py-4">
          <p class="text-center">{{ __("You are want to cancel this request money.") }}</p>
          <p class="text-center">{{ __("Do you want to proceed?") }}</p>
        </div>

        <div class="modal-footer">
          <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</a>
          <button type="submit" id="closeprocess" class="btn shadow-none btn--success" data-bs-dismiss="modal">@lang('Proceed')</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="modal-details" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="request-money-details">
        </div>
      </div>
  </div>
</div>

@endsection

@push('js')
  <script>
    'use strict';
    $('.details').on('click', function() {
        var url = "{{url('user/money-request/details/')}}"+'/'+$(this).data('id')
        $.get(url,function (res) {
          $('.request-money-details').html(res)
          $('#modal-details').modal('show')
          $('#sendBtn').on('click', function() {
            $("#requestMoney").prop("action",$(this).data('href'))
            @if($user->paymentCheck('Receive Request Money'))
            var url = "{{url('user/sendotp')}}";
            $.get(url,function (res) {
                console.log(res)
                if(res=='success') {
                    $('#modal-success').modal('show');
                }
                else {
                  toastr.options = { "closeButton" : true, "progressBar" : true }
                  toastr.error('The OTP code can not be sent to you.');
                }
            });
            @else
            $('#modal-success').modal('show');
            @endif
          })

          $("#sendprocess").on('click',function(){
            $("#sendBtn").text("Processing ...");
          })
          $("#cancelBtn").on('click',function(){
            $("#cancelRequestMoney").prop("action",$(this).data('href'))
          })
          $("#closeprocess").on('click',function(){
            $("#cancelBtn").text("Processing ...");
          })
        })
    })
    $('#confirm-delete').on('show.bs.modal', function(e) {
      $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
    });
  </script>
@endpush
