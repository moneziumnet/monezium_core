@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Telegram API') }}</h5>
        <ol class="breadcrumb py-0 m-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin-staff-telegram') }}">{{ __('Staff Settings') }}</a></li>
        </ol>
    </div>
</div>

  <div class="card mb-4 mt-3">

    <div class="p-3">
        <div class="card mt-3 p-3">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>@lang('Please click this to join our Telegram bot : ')</label>
                    <a href='https://t.me/moneziumbot' class="" alt="" target="_blank">{{__('Click here me')}}</a>
                </div>
            </div>
                <form action="{{route('admin.telegram.pin.generate')}}" id="form" method="post" enctype="multipart/form-data">

                @csrf
                    <div class="row ">
                        <div class="col-md-6">
                            <div class="form-label ">@lang('Telegram Pin Code : ')</div>
                            <input type="text" name="code" id="code" class="form-control shadow-none mb-2" value="{{$telegram->pincode ?? ''}}" readonly>
                        </div>

                        <div class="col-md-4">
                            <div class="form-label">&nbsp;</div>
                            <button type="submit" class="btn btn-primary w-100 create">
                                @lang('Generate')
                            </button>
                        </div>
                    </div>
                </form>
        </div>
    </div>
  </div>

@endsection
