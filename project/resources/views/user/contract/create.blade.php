@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Create Contract')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

              <a href="{{ route('user.contract.index') }}" class="btn btn-primary d-none d-sm-inline-block">
                  <i class="fas fa-backward me-1"></i> {{__('Contract List')}}
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
                    @includeIf('includes.flash')
                    <form id="contract-form" action="{{ route('user.contract.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Title')}}</label>
                            <input name="title" id="title" class="form-control" autocomplete="off" placeholder="{{__('Enter Title')}}" type="text" required>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" min="1" required>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Description')}}</label>
                            <textarea name="description" class="form-control" id="inp-details" cols="30" rows="10" placeholder="{{__('Description')}}" required></textarea>
                        </div>
                        <input name="user_id" type="hidden" class="form-control" value="{{auth()->id()}}">

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
