@extends('layouts.user')

@push('css')

@endpush

@section('title', __('Supervisor'))

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
        <div class="col">
            </div>
            <h2 class="page-title">
            {{__('Supervisor Account')}}
            </h2>
        </div>
        </div>
    </div>

<div class="page-body">
    <div class="container-fluid">
    <div class="row justify-content " style="max-height: 368px;">
    @foreach ($wallets as $item)
    <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
            <div class="text-end icon rounded-circle">
            <i class="fas ">
                {{$item->currency->symbol}}
            </i>
            </div>
            <div class="card-body">
            <div class="h3 m-0 text-uppercase"> {{__('Supervisor')}}</div>
            <div class="h4 m-0 text-uppercase"> {{ $item->wallet_no }}</div>
            <div class="text-muted">{{ amount($item->balance,$item->currency->type,2) }}  {{$item->currency->code}}</div>
            </div>
        </div>
    </div>
    @endforeach
    </div>
    </div>
</div>

<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Invite User')}}
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
                    <div class="card-body">
                        <div class="form-group">
                          <p>{{ __('My Referral Link') }}</p>
                          <div class="input-group input--group">
                            <input type="text" name="key" value="{{ url('/').'?reff='.$user->affilate_code}}" class="form-control" id="cronjobURL" readonly>
                            <button class="btn btn-sm copytext input-group-text" id="copyBoard" onclick="myFunction()"> <i class="fa fa-copy"></i> </button>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row row-cards mt-2">
            <div class="col-12">
                <div class="card p-3">
                    <h3>{{ __('Send Invitation') }}</h3>
                    @includeIf('includes.flash')
                    <form id="request-form" action="{{ route('user.referral.invite-user') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Enter Email Address')}}</label>
                            <input name="invite_email" id="invite_email" class="form-control" autocomplete="off" placeholder="{{__('example@gmail.com')}}" type="email" value="{{ old('invite_email') }}" required>
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100">{{__('Send Invitation')}}</button>
                        </div>
                    </form>
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
            {{__('My Referred')}}
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
                    @if (count($referreds) == 0)
                        <h3 class="text-center py-5">{{__('No Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-sm card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Serial No') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Joined At') }}</th>
                                    <th >{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($referreds as $key=>$data)
                                        <tr>
                                            <td data-label="{{ __('Serial No') }}">
                                                <div>
                                                    {{ $loop->iteration }}
                                                </div>
                                            </td>
                                            <td data-label="{{ __('Name') }}">
                                                <div>
                                                    {{ ucfirst($data->name) }}
                                                </div>
                                            </td>
                                            <td data-label="{{ __('Joined At') }}">
                                                <div>
                                                    {{ $data->created_at->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td data-label="{{ __('Action') }}" >
                                                <div>
                                                    <a class="btn btn-primary btn-sm details" href="{{route('user-pricingplan',$data->id)}}">@lang('Details')</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $referreds->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if ((check_user_type(4)))

<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Manager list')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

              <a href="{{ route('user.manager.create') }}" class="btn btn-primary d-sm-inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                {{__('Create Manager')}}
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
                    @if (count($managers) == 0)
                        <h3 class="text-center py-5">{{__('No Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-sm card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Serial No') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Joined At') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($managers as $key=>$data)
                                        <tr>
                                            <td data-label="{{ __('Serial No') }}">
                                                <div>
                                                    {{ $loop->iteration }}
                                                </div>
                                            </td>
                                            <td data-label="{{ __('Name') }}">
                                                <div>
                                                    {{ ucfirst($data->manager->company_name ?? $data->manager->name) }}
                                                </div>
                                            </td>
                                            <td data-label="{{ __('Email') }}">
                                                <div>
                                                    {{ ucfirst($data->manager->email) }}
                                                </div>
                                            </td>
                                            <td data-label="{{ __('Joined At') }}">
                                                <div>
                                                    {{ $data->created_at->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td data-label="{{ __('Action') }}" >
                                                <div>
                                                    <a class="btn btn-danger btn-sm details" href="javascript:;" id="delete" data-bs-toggle="modal" data-bs-target="#modal-delete" data-href="{{route('user.manager.delete',$data->id)}}">@lang('Delete')</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-blur confirm-modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <form id="deletemanager" action="" method="get">
          @csrf
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="modal-status bg-success"></div>

          <div class="modal-body text-center py-4">
            <p class="text-center">{{ __("You want to delete this manager.") }}</p>
            <p class="text-center">{{ __("Do you want to proceed?") }}</p>
          </div>

          <div class="modal-footer">
            <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</a>
            <button type="submit" class="btn shadow-none btn--success" id="cancelprocess" data-bs-dismiss="modal">@lang('Ok')</button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endif


@endsection

@push('js')
<script>
    'use strict';

    function myFunction() {
      var copyText = document.getElementById("cronjobURL");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");
      toastr.options =
      {
        "closeButton" : true,
        "progressBar" : true
      }
      toastr.success("Copied.");
    }
    $("#delete").on('click',function(){
      $("#deletemanager").prop("action",$(this).data('href'))
    })
  </script>
@endpush

