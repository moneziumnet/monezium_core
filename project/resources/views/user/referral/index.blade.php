@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
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
    <div class="container-xl">
    <div class="row justify-content " style="max-height: 368px;">
    @foreach ($wallets as $item)
    <div class="col-sm-6 col-md-4 mb-3">
        <div class="card h-100 card--info-item">
            <div class="text-end icon">
            <i class="fas ">
                {{$item->currency->symbol}}
            </i>
            </div>
            <div class="card-body">
            <div class="h3 m-0 text-uppercase"> {{__('Deposit')}}</div>
            <div class="h4 m-0 text-uppercase"> {{ $item->wallet_no }}</div>
            <div class="text-muted">{{ amount($item->balance,$item->currency->type,2) }}  {{$item->currency->code}}</div>
            </div>
        </div>
    </div>
    @endforeach
    </div>
    </div>
</div>

<div class="container-xl">
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
    <div class="container-xl">
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

<div class="container-xl">
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
    <div class="container-xl">
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


@endsection

@push('js')
<script>
    'use strict';

    function myFunction() {
      var copyText = document.getElementById("cronjobURL");
      copyText.select();
      copyText.setSelectionRange(0, 99999);
      document.execCommand("copy");
      alert('copied');
    }
  </script>
@endpush

