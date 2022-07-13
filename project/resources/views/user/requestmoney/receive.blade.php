@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Receive Request Money')}}
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
                  @include('includes.user.form-both')
                    @if (count($requests) == 0)
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
                                  @foreach($requests as $key=>$data)
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
                                              {{ $from != NULL ? $from->name : 'User Deleted' }}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Amount') }}">
                                            <div>
                                              {{ showprice($data->amount,$data->currency) }}
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
                                              <span class="badge badge-{{ $bclass }}">{{ $bstatus}}</span>
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Details') }}">
                                            <div class="btn-list">
                                                <a href="{{route('user.money.request.details',$data->id)}}" class="btn btn-primary">
                                                  {{__('Details')}}
                                                </a>

                                                @if ($data->status == 0)
                                                  <a href="javascript:;" id="sendBtn" data-href="{{ route('user.request.money.verify',$data->id) }}" class="btn" data-bs-toggle="modal" data-bs-target="#modal-success">
                                                    {{__('Send')}}
                                                  </a>
                                                  <a href="javascript:;" id="cancelBtn" data-href="{{ route('user.request.money.cancel',$data->id) }}" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-cancel">
                                                    {{__('Cancel')}}
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

@endsection

@push('js')
  <script>
    'use strict';

    $("#sendBtn").on('click',function(){
      $("#requestMoney").prop("action",$(this).data('href'))
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
  </script>
@endpush

