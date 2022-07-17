@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Verify Google Authenticator Code')}}
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

                    <div class="card-body">
                      <div class="tab-content">
                        <div class="tab-pane active show" id="other-account">
                            @includeIf('includes.flash')
                            <form action="{{route('user.merchant.request.money.send', $id)}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mt-3">
                                    <label class="form-label required">{{__('Google Authenticator Code')}}</label>
                                    <input type="text" class="form-control" name="code" autocomplete="off" required placeholder="@lang('Enter Google Authenticator Code')">
                                </div>

                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                                </div>
                            </form>
                        </div>

                      </div>
                    </div>
                  </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')

@endpush
