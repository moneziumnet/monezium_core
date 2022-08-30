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

              <a href="{{ route('user.contract.index') }}" class="btn btn-primary d-sm-inline-block">
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

                        <div class="row form-group mb-3 mt-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-label">{{__('Pattern name')}}</div>
                                <input type="text" name="item[]" class="form-control shadow-none itemname"  >
                            </div>
                            <div class="col-md-5 mb-3">
                                <div class="form-label">{{__('Value')}}</div>
                                <input type="text" name="value[]" class="form-control shadow-none itemvalue"  >
                            </div>
                            <div class="col-md-1 mb-3">
                                <div class="form-label">&nbsp;</div>
                                <button type="button" class="btn btn-primary w-100 add"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="extra-container"></div>


                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Description')}} {{__('(i.e: if patten is amount, and value is 500,  {amount} is 500)')}}</label>
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
  $('.add').on('click',function(){
            $('.extra-container').append(`

                   <div class="row form-group mb-3 mt-3">
                        <div class="col-md-6 mb-3">
                            <input type="text" name="item[]" class="form-control shadow-none itemname" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <input type="text" name="value[]" class="form-control shadow-none itemvalue" required>
                        </div>
                        <div class="col-md-1 mb-3">
                            <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

            `);
        })

        $(document).on('click','.remove',function () {
            $(this).closest('.row').remove()
        })
</script>
@endpush
