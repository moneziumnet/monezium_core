@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Staff') }} <a class="btn btn-primary btn-rounded btn-sm ml-3" href="{{route('admin.staff.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>

        <li class="breadcrumb-item"><a href="{{ route('admin.staff.index') }}">{{ __('Staff Management') }}</a></li>
    </ol>
    </div>
</div>

<div class="row justify-content-center mt-3">
  <div class="col-md-10">
    <!-- Form Basic -->
    <div class="card mb-2">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between border-bottom">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Add New Staff') }}</h6>
      </div>

      
      <form class="geniusform row align-items-center p-3" action="{{route('admin.staff.store')}}" method="POST" enctype="multipart/form-data">
      
        
        {{ csrf_field() }}
        <div class="card-body">
                @include('includes.admin.form-both')
                <div class="row">
                    <div class="col-sm-6">
                        <label for="name" class="form-label">@lang('Your First Name')</label>
                        <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" id="name" name="firstname" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="name" class="form-label">@lang('Your Last Name')</label>
                        <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" id="name" name="lastname" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6 mt-3">
                        <label for="email" class="form-label">@lang('Your Email')</label>
                        <input type="email" id="email" name="email" class="form-control form--control" required>
                    </div>
                </div>
                <div class="row">
                    
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="password" class="form-label">@lang('Your Password')</label>
                            <input type="password" id="password" name="password" class="form-control form--control" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="confirm-password" class="form-label">@lang('Confirm Password')</label>
                            <input type="password" id="confirm-password" name="password_confirmation" class="form-control form--control" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 d-flex flex-wrap justify-content-center align-items-center">
                <button type="submit" class="btn btn-primary w-50">
                    @lang('Register Now')
                </button>
            </div>
        </form>
    </div>
  </div>

</div>
@endsection


@section('scripts')


@endsection
