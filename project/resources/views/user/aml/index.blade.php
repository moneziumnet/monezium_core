@extends('layouts.user')

@section('styles')

@endsection

@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
        @include('user.aml.tab')
    <div class="row align-items-center">
      <div class="col">
        <h2 class="page-title">
          {{__('KYC Form')}}
        </h2>
        <div class="page-pretitle">
          {{__('We need to know more about you.')}}
        </div>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
      <div class="row row-cards">
          <div class="col-12">
              <div class="card p-5">
                  <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                    @if (count($informations) == 0)
                        <h3 class="text-center py-5">{{__('No KYC Request Found')}}</h3>
                    @else
                      @includeIf('includes.flash')
                        <form action="{{route('user.aml.kyc.store')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{$KycForms->id}}">
                            @foreach ($informations as $key=>$field)
                                @if ($field['type'] == "Input" || $field['type'] == "Textarea" )
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label {{$field['required'] == 1 ? 'required':'Optional'}}">@lang($field['label'])</label>
                                    @if ($field['type'] == "Input")
                                    <input type="text" name="{{strtolower(str_replace(' ', '_', $field['label']))}}" class="form-control" autocomplete="off" placeholder="@lang($field['label'])" min="1" {{$field['required'] == 1 ? 'required':'Optional'}}>
                                    @else
                                    <textarea class="form-control" name="{{strtolower(str_replace(' ', '_', $field['label']))}}" placeholder="@lang($field['label'])"></textarea>
                                    @endif
                                </div>
                                @elseif($field['type'] == "Image")
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label {{$field['required'] == 1 ? 'required':'Optional'}}">@lang($field['label'])</label>
                                    <input type="file" name="{{strtolower(str_replace(' ', '_', $field['label']))}}" class="form-control" autocomplete="off" {{$field['required'] == 1 ? 'required':'Optional'}}>
                                </div>
                                @endif
                            @endforeach

                            {{-- <label class="form-check">
                                    <input class="form-check-input shadow-none" type="checkbox" name="sendlink" checked>
                                    <span class="form-check-label">@lang('Send online selfie link to email')</span>
                            </label> --}}
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                            </div>
                        </form>
                    @endif
                 </div>
          </div>
      </div>
  </div>
</div>

@endsection

@push('js')
<script language="JavaScript">
'use strict'
</script>
@endpush
