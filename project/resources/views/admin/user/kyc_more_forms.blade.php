@extends('layouts.admin')


@section('content')

<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between py-3">
        <h5 class=" mb-0 text-gray-800 pl-3">{{ __('User KYC Form') }}</h5>
        <ol class="breadcrumb py-0 m-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.user.kycinfo', $user->id) }}">{{ __('AML/KYC') }}</a></li>
        </ol>
	</div>
</div>

    <div class="card mb-4 mt-3">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">@lang('KYC Form Fields')</h6>
        </div>
        <div class="card-body">
            <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
            <form class="geniusform" action="{{route('admin.kyc.more.form.store')}}" method="POST" enctype="multipart/form-data">
            @include('includes.admin.form-both')
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}">
            <div class="form-group col-md-4">
                <label for="title">{{ __('Title') }}</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="{{ __('Enter Title') }}" value="" required>
            </div>

            <a href="javascript:;" id="lang-btn" class="add-fild-btn d-flex justify-content-center"><i class="icofont-plus"></i> {{__('Add More Field')}}</a>

            <div class="form-group">
                <div class="lang-tag-top-filds" id="lang-section">
                    <label for="instruction">{{ __("Required Information") }}</label>
                    <div class="lang-area mb-3">
                        <span class="remove lang-remove"><i class="fas fa-times"></i></span>
                        <div class="row">
                            <div class="col-md-4">
                                <!-- <input type="text" name="form_builder[1][field]" class="form-control" placeholder="{{ __('Field Name') }}" required> -->
                                <select class="form-control type" name="form_builder[1][type]" required>
                                    <option value="">{{ __('Select Input Type') }}</option>
                                    <option value="Input">@lang('Input')</option>
                                    <option value="Image">@lang('Image')</option>
                                    <option value="Textarea">@lang('Textarea')</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <input type="text" name="form_builder[1][label]" class="form-control" placeholder="{{ __('Input Label value') }}" required>
                            </div>

                            <div class="col-md-4">
                                <!-- <input type="text" name="form_builder[1][label]" class="form-control" placeholder="{{ __('Field value') }}" required> -->
                                <select class="form-control" name="form_builder[1][required]" required>
                                    <option value="">{{ __('Select Required Type') }}</option>
                                    <option value="1">@lang('Yes')</option>
                                    <option value="0">@lang('No')</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-3">{{ __('Submit') }}</button>

        </form>
        <hr>
        </div>
    </div>


@stop

@section('scripts')
    
<script type="text/javascript">
'use strict';
  function isEmpty(el){
      return !$.trim(el.html())
  }

  let id = 2;

$("#lang-btn").on('click', function(){

    $("#lang-section").append(''+
            `<div class="lang-area mb-3">
            <span class="remove lang-remove"><i class="fas fa-times"></i></span>
            <div class="row">
                <div class="col-md-4">
                    <select class="form-control type" name="form_builder[${id}][type]" required>
                        <option value="">{{ __('Select Input Type') }}</option>
                        <option value="Input">@lang('Input')</option>
                        <option value="Image">@lang('Image')</option>
                        <option value="Textarea">@lang('Textarea')</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <input type="text" name="form_builder[${id}][label]" class="form-control" placeholder="{{ __('Input Label value') }}" required>
                </div>

                <div class="col-md-4">
                    <select class="form-control" name="form_builder[${id}][required]" required>
                        <option value="">{{ __('Select Required Type') }}</option>
                        <option value="1">@lang('Yes')</option>
                        <option value="0">@lang('No')</option>
                    </select>
                </div>

            </div>
          </div>`+
          '');
      id ++;
});

$(document).on('click','.lang-remove', function(){

    $(this.parentNode).remove();
    if(id && id >1){
      id --;
    }
    if (isEmpty($('#lang-section'))) {

      $("#lang-section").append(''+
            `<div class="lang-area mb-3">
            <span class="remove lang-remove"><i class="fas fa-times"></i></span>
            <div class="row">
                <div class="col-md-4">
                    <select class="form-control type" name="form_builder[1][type]" required>
                        <option value="">{{ __('Select Input Type') }}</option>
                        <option value="Input">@lang('Input')</option>
                        <option value="Image">@lang('Image')</option>
                        <option value="Textarea">@lang('Textarea')</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <input type="text" name="form_builder[1][label]" class="form-control" placeholder="{{ __('Input Label value') }}" required>
                </div>

                <div class="col-md-4">
                    <select class="form-control" name="form_builder[1][required]" required>
                        <option value="">{{ __('Select Required Type') }}</option>
                        <option value="1">@lang('Yes')</option>
                        <option value="0">@lang('No')</option>
                    </select>
                </div>

            </div>
          </div>`+
          '');
    }

});
</script>

@endsection

@section('style')
    <style>
       .form-control{
           line-height: 1.2 !important
       }
    </style>
@endsection
