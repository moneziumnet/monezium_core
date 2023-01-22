@extends('layouts.admin')

@section('content')
<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between">
  <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Add New Gateway') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.subinstitution.paymentgateways', $subins_id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
  <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.index') }}">{{ __('Sub Institutions List') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.paymentgateways',$subins_id)}}">{{ __('Paymentgateway List') }}</a></li>
  </ol>
  </div>
</div>

<div class="row justify-content-center mt-3">
<div class="col-md-10">
  <div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-primary">{{ __('Payment Gateway Form') }}</h6>
    </div>

    <div class="card-body">
      <div class="gocover" style="background: url({{asset('assets/images/'.$gs->admin_loader)}}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
      <form class="geniusform" action="{{route('admin.payment.store')}}" method="POST" enctype="multipart/form-data">

          @include('includes.admin.form-both')

          {{ csrf_field() }}

          <div class="form-group">
            <label for="title">{{ __('Name') }}</label>
            <input type="text"  pattern="[^()/><\][-;!|]+" class="form-control" id="name" name="name" placeholder="{{ __('Enter Name') }}" value="" required>
          </div>

          <div class="form-group">
            <label for="subtitle">{{ __('Subtitle') }}</label>
            <input type="text" pattern="[^()/><\][;!|]+" class="form-control" id="subtitle" name="subtitle" placeholder="{{ __('Enter Subtitle') }}" value="" required>
          </div>

          <div class="form-group">
            <label for="details">{{ __('Description ') }}</label>
            <textarea class="form-control summernote"  id="details" name="details" required rows="3" placeholder="{{__('Description')}}"></textarea>
          </div>

          <div class="form-group">
            <input type="hidden" class="form-control" id="subins_id" name="subins_id"  value="{{$subins_id}}" required>
          </div>

          <a href="javascript:;" id="lang-btn" class="add-fild-btn d-flex justify-content-center"><i class="icofont-plus"></i> {{__('Add More Field')}}</a>


          <div class="lang-tag-top-filds" id="lang-section">
              <label for="instruction">{{ __("Required Information") }}</label>
              <div class="lang-area mb-3">
                  <span class="remove lang-remove"><i class="fas fa-times"></i></span>
                  <div class="row">
                <div class="col-md-4">
                    <input type="text" pattern="[^()/><\][;!|]+" name="form_builder[1][field]" class="form-control" placeholder="{{ __('Field Name') }}">
                </div>

                <div class="col-md-7">
                    <input type="text" pattern="[^()/><\][;!|]+" name="form_builder[1][value]" class="form-control" placeholder="{{ __('Field value') }}">
                </div>
            </div>
            </div>
        </div>

        <button type="submit" id="submit-btn" class="btn btn-primary w-100 mt-3">{{ __('Submit') }}</button>
    </form>
    </div>
  </div>
</div>

</div>
@endsection


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
                <input type="text" pattern="[^()/><\][;!|]+" name="form_builder[${id}][field]" class="form-control" placeholder="{{ __('Field Name') }}">
              </div>

              <div class="col-md-7">
                <input type="text" pattern="[^()/><\][;!|]+" name="form_builder[${id}][value]" class="form-control" placeholder="{{ __('Field value') }}">
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
                <input type="text" pattern="[^()/><\][;!|]+" name="form_builder[1][field]" class="form-control" placeholder="{{ __('Field Name') }}">
              </div>

              <div class="col-md-7">
                <input type="text" pattern="[^()/><\][;!|]+" name="form_builder[1][value]" class="form-control" placeholder="{{ __('Field value') }}">
              </div>

            </div>
          </div>`+
          '');
    }

});

</script>

@endsection
