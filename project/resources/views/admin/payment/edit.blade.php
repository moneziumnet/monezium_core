@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Edit Payment Gateway') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.subinstitution.paymentgateways', $data->subins_id)}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
    <ol class="breadcrumb m-0 py-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.subinstitution.index') }}">{{ __('Sub Institutions List') }}</a></li>
        <li class="breadcrumb-item"><a href="{{route('admin.subinstitution.paymentgateways',$data->subins_id)}}">{{ __('Edit Payment') }}</a></li>
    </ol>
    </div>
</div>

    <div class="card mb-4 mt-3">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Edit Payment Form') }}</h6>
      </div>

      <div class="card-body">

        <form class="geniusform"  action="{{route('admin.payment.update',$data->id)}}" method="POST" enctype="multipart/form-data">

            @include('includes.admin.form-both')

            {{ csrf_field() }}


            @if($data->type == 'automatic')


            <div class="form-group">
              <label for="inp-name">{{ __('Name') }}</label>
              <input type="text" class="form-control" id="inp-name" name="name"  placeholder="{{ __('Enter Name') }}" value="{{ $data->name }}" required>
            </div>


            @if($data->information != null)

              @foreach($data->convertAutoData() as $pkey => $pdata)

              @if($pkey == 'sandbox_check')

                <div class="form-group">
                  <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="pkey[{{ __($pkey) }}]" class="custom-control-input" {{ $pdata == 1  ? 'checked' : '' }} id="{{ $pkey }}">
                    <label class="custom-control-label" for="{{ $pkey }}">
                      {{ __( $data->name.' '.ucwords(str_replace('_',' ',$pkey)) ) }}
                    </label>
                  </div>
                </div>


              @else

              <div class="form-group">
                <label for="inp-{{ __($pkey) }}">{{ __( $data->name.' '.ucwords(str_replace('_',' ',$pkey)) ) }}</label>
                <input type="text" class="form-control" id="inp-{{ __($pkey) }}" name="pkey[{{ __($pkey) }}]"  placeholder="{{ __( $data->name.' '.ucwords(str_replace('_',' ',$pkey)) ) }}" value="{{ $pdata }}" required>
              </div>


              @endif

              @endforeach

            @endif
            <hr>
            @php
                $setCurrency = json_decode($data->currency_id);
                if($setCurrency == 0){
                   $setCurrency = [];
                }
             @endphp
             <div class="col-md-12">
             @foreach(DB::table('currencies')->get() as $dcurr)
                <input  name="currency_id[]"  {{in_array($dcurr->id,$setCurrency) ? 'checked' : ''}} type="checkbox" id="currency_id{{$dcurr->id}}" value="{{$dcurr->id}}">
                <label class="mr-4" for="currency_id{{$dcurr->id}}">{{$dcurr->code}}</label>
                @endforeach
             </div>

            <!-- <div class="form-group">
                <label for="inp-name">{{ __('Sub Institution') }}</label>

                <select class="form-control mb-3" name="ins_id" id="ins_id">
                    <option value="">{{ __('Select Sub Institution') }}</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" @if($user->id == $data->ins_id) selected @endif>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div> -->

            @else

              <div class="form-group">
                <label for="inp-title">{{ __('Name') }}</label>
                <input type="text" class="form-control" id="inp-title" name="name"  placeholder="{{ __('Enter Name') }}" value="{{ $data->name }}" required>
              </div>

              <div class="form-group">
                <label for="inp-subtitle">{{ __('Subtitle') }}</label>
                <input type="text" class="form-control" id="inp-subtitle" name="subtitle"  placeholder="{{ __('Enter Subtitle') }}" value="{{ $data->subtitle }}" required>
              </div>

              @if($data->keyword == null)


              <div class="form-group">
                <label for="inp-details">{{ __('Description') }}</label>
                <textarea name="details" class="form-control summernote" id="inp-details" cols="30" rows="10" >{{ $data->details }}</textarea>
              </div>

              @endif
              @if ($informations == NULL | count($informations) == 0)
                <p>{{ __('No field Added') }}</p>

                @else
                @foreach ($informations as $key=>$info)
                    <div class="lang-area mb-3">
                    <span class="remove lang-remove"><i class="fas fa-times"></i></span>
                    <div class="row">
                        <div class="col-md-4">
                        <input type="text" name="form_builder[{{ $key }}][field]" class="form-control" placeholder="{{ __('Field Name') }}" value="{{ $info['field'] }}">
                        </div>
                        <div class="col-md-7">
                            <input type="text" name="form_builder[{{ $key }}][value]" class="form-control" placeholder="{{ __('Field Name') }}" value="{{ $info['value'] }}">
                        </div>
                    </div>
                    </div>
                @endforeach
              @endif

            @endif

            <button type="submit" id="submit-btn" class="btn btn-primary w-100">{{ __('Submit') }}</button>
        </form>
      </div>
    </div>


@endsection
@section('scripts')
<script type="text/javascript">
  'use strict';
  function isEmpty(el){
      return !$.trim(el.html())
  }

  let id = '{{count($informations) == 0 ? 1 : count($informations) + 1}}';

$("#lang-btn").on('click', function(){

    $("#lang-section").append(''+
            `<div class="lang-area mb-3">
            <span class="remove lang-remove"><i class="fas fa-times"></i></span>
            <div class="row">
              <div class="col-md-4">
                <input type="text" name="form_builder[${id}][field]" class="form-control" placeholder="{{ __('Field Name') }}">
              </div>

              <div class="col-md-7">
                <input type="text" name="form_builder[${id}][value]" class="form-control" placeholder="{{ __('Field Value') }}">
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
              <div class="col-md-6">
                <input type="text" name="form_builder[1][field]" class="form-control" placeholder="{{ __('Field Name') }}">
              </div>

              <div class="col-md-6">
                <input type="text" name="form_builder[1][value]" class="form-control" placeholder="{{ __('Field Value') }}">
              </div>

            </div>
          </div>`+
          '');
    }

});

</script>
@endsection

