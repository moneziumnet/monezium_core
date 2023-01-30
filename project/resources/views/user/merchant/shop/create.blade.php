@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      @include('user.merchant.tab')
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Create Shop')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

              <a href="{{ route('user.merchant.shop.index') }}" class="btn btn-primary d-sm-inline-block">
                  <i class="fas fa-backward me-1"></i> {{__('Merchant Shop List')}}
              </a>
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
                    <form id="shop-form" action="{{ route('user.merchant.shop.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Name')}}</label>
                            <input name="name" id="name" class="form-control" autocomplete="off" placeholder="{{__('Please Input Shop Name')}}" type="text" pattern="[^()/><\][\\;!|]+" required>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Logo')}}</label>
                            <input name="logo" id="logo"  type="file" autocomplete="off"  required accept=".png,.jpg,.gif">
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Document')}}</label>
                            <input  name="document" id="document" autocomplete="off" type="file" accept=".doc,.docx,.pdf">
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Shop URL')}}</label>
                            <input name="url" id="url" class="form-control" autocomplete="off" placeholder="{{__('https://example.com')}}" type="url" required>
                        </div>
                        <input name="merchant_id" type="hidden" class="form-control" value="{{auth()->id()}}">

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Submit')}}</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script>
  'use strict';
</script>
@endpush
