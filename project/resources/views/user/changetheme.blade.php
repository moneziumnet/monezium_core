
@extends('layouts.user')

@push('css')
    
@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.settingtab')
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Change Theme')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card p-4">
                    @includeIf('includes.flash')
                    <form id="request-form" action="{{route('user.change.theme')}}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3 mt-3 col-md-6">
                            <label class="form-label required">{{__('Current Theme')}}</label>
                            <select name="website_theme" id="website_theme" class="form-select" required>
                                <option value="0" @if ($website_theme == 0)
                                    selected                                   
                                @endif>{{__('Default')}}</option>
                                <option value="1" @if ($website_theme == 1)
                                    selected                          
                                @endif>{{__('Custom')}}</option>
                            </select>
                        </div>


                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn">{{__('Submit')}}</button>
                        </div>

                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')

@endpush