@extends('layouts.admin')

@section('content')


<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Website Contents') }}</h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">{{ __('General Settings') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.gs.contents') }}">{{ __('Website Contents') }}</a></li>
    </ol>
    </div>
</div>

  <div class="card mb-4 mt-3">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-primary">{{ __('Website Contents Form') }}</h6>
    </div>

    <div class="card-body">
      <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
      <form class="geniusform" action="{{ route('admin.gs.update') }}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="inp-title">{{  __('Frontend Page')  }}</label>
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" name="frontend_status" value="1" data-turnon="{{ route('admin.gs.status',['frontend_status',0]) }}"  data-turnoff="{{ route('admin.gs.status',['frontend_status',1]) }}" class="custom-control-input theme-change" {{$gs->frontend_status == 0 ? 'checked' : ''}} id="frontend">
                  <label class="custom-control-label" for="frontend">{{__('Diable/Enable')}}</label>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label for="inp-disqus">{{  __('Disqus Website Short Name')  }}</label>
              <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-disqus" name="disqus"  placeholder="{{ __('Disqus Website Short Name') }}" value="{{ $gs->disqus }}" required>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <div class="cp-container" id="cp3-container">
                <div class="input-group" title="Using input value">
                    <input  type="color" name="colors"  class="form-control"  value="{{ $gs->colors }}" id="exampleInputPassword1">
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="row">
          <div class="col-md-4">
              <div class="form-group">
                <label for="inp-prefix">{{  __('User Account No Prefix')  }}</label>
                <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-prefix" name="account_no_prefix"  placeholder="{{ __('User Account No Prefix') }}" value="{{ $gs->account_no_prefix }}" required>
              </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label for="inp-prefix">{{  __('User Wallet No Prefix')  }}</label>
              <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-prefix" name="wallet_no_prefix"  placeholder="{{ __('User Wallet No Prefix') }}" value="{{ $gs->wallet_no_prefix }}" required>
            </div>
        </div>

          <div class="col-md-4">
            <div class="form-group">
              <label for="inp-title">{{  __('Website Title')  }}</label>
              <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control" id="inp-title" name="title"  placeholder="{{ __('Enter Website Title') }}" value="{{ $gs->title }}" required>
            </div>
          </div>

        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                  <label for="other_bank_limit">{{  __('Add documnets from account')  }}</label>
                  <input type="number" step="any" class="form-control" id="other_bank_limit" name="other_bank_limit"  placeholder="{{ __('Add documnets from account') }}" value="{{ $gs->other_bank_limit }}">
                </div>
            </div>

        </div>

        <div class="form-group">
          <label for="inp-name">{{ __('Currency Format') }}</label>
          <select class="form-control mb-3" name="currency_format">
            <option value="" selected>{{__('Select Category')}}</option>
            <option value="0" {{ $gs->currency_format== 0 ? 'selected':''}}>{{__('Before Price')}}</option>
            <option value="1" {{ $gs->currency_format== 1 ? 'selected':''}}>{{__('After Price')}}</option>
          </select>
        </div>

        <div class="form-group">
          <label for="copyright-text">{{  __('Copyright Text')  }}</label>
          <textarea name="copyright" class="form-control summernote" id="copyright-text" rows="5">{{ $gs->copyright }}</textarea>
        </div>



          <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>

      </form>
    </div>
  </div>

@endsection
