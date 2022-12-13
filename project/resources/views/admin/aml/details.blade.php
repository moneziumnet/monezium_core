@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('KYC Details') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.user.kycinfo',$user->id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
            <li class="breadcrumb-item"><a href="{{route('admin.user.kycinfo',$user->id)}}">{{ __('Profile') }}</a></li>
        </ol>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-12">
        @include('includes.admin.form-success')
        <div class="row">
            <div class="col-lg-12">
                <div class="special-box">
                    <div class="heading-area">
                        <h4 class="title">
                            {{__('KYC Information')}}
                        </h4>
                    </div>
                    <div class="table-responsive-sm">
                        <table class="table">
                            <tbody>
                                @if ($kycInformations != NULL)
                                    @foreach ($kycInformations as $key=>$value)
                                        @if (isset($value[1]) && $value[1] == 'file')
                                        @if (gettype($value[0]) != 'array')
                                            <tr>
                                                <th width="45%">{{$key}}</th>
                                                <td width="10%">:</td>
                                                <td width="45%"><a href="{{asset('assets/images/'.$value[0])}}" download><img src="{{asset('assets/images/'.$value[0])}}" class="img-thumbnail"></a></td>
                                            </tr>
                                        @endif
                                        @else
                                            <tr>
                                                <th width="45%">{{$key}}</th>
                                                <td width="10%">:</td>
                                                <td width="45%">{{ $value[0] ?? "" }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @else
                                    <p class="text-center mt-5">@lang('KYC NOT SUBMITTTED')</p>
                                @endif
                                @if (isset($user->kyc_photo))
                                @foreach (explode(',', $user->kyc_photo) as $key=>$value)

                                <tr>
                                    <th width="45%">{{__('Selfie')}} {{$key+1}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%"><a href="{{asset('assets/images/'.$value)}}" download><img src="{{asset('assets/images/'.$value)}}" class="img-thumbnail"></a></td></td>
                                </tr>
                                @endforeach


                                @endif


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
