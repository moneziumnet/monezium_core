@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    @include('user.merchant.tab')
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Merchant Shop List')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{ route('user.merchant.shop.create') }}" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Create Shop')}}
          </a>
        </div>
      </div>
    </div>
  </div>
</div>



<div class="page-body">
    <div class="container-xl mt-3 mb-3">
        <div class="row row-cards">
            <div class="row justify-content " style="max-height: 1600px;overflow-y: scroll;">
                    @if (count($shoplist) == 0)
                        <h3 class="text-center py-5">{{__('No Merchant Shop Found')}}</h3>
                    @else
                    @foreach($shoplist as $key=>$val)
                      <div class="col-lg-3 mb-3">
                          <div class="card">
                              <img class="back-preview-image"

                                  src="{{asset('assets/images')}}/{{$val->logo}}"
                              alt="Image placeholder">
                              <!-- Card body -->
                              <div class="card-body">
                                  <div class="row mb-2">
                                      <div class="col-8">
                                      </div>
                                      <div class="col-4 text-end">
                                      <a class="mr-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                          <i class="fas fa-chevron-circle-down"></i>
                                      </a>
                                      <div class="dropdown-menu dropdown-menu-left">
                                          @if ($val->status == 0)
                                              <a href="{{route('user.merchant.shop.edit',$val->id)}}" class="dropdown-item"><i class="fas fa-edit me-2"></i>{{__('Edit')}}</a>
                                              <a href="javascript:void(0)" class="dropdown-item disabled" d><i class="fas fa-file-contract me-2"></i>{{__('View')}}</a>
                                            @else
                                                <a href="javascript:void(0)" class="dropdown-item disabled"><i class="fas fa-edit me-2"></i>{{__('Edit')}}</a>
                                                <a href="{{route('user.merchant.shop.view_product',$val->id)}}" class="dropdown-item" ><i class="fas fa-file-contract me-2"></i>{{__('View')}}</a>
                                            @endif
                                            <a href="javascript:void(0)" class="dropdown-item" data-bs-target="#delete{{$val->id}}" data-bs-toggle="modal" ><i class="fas fa-trash-alt me-2"></i>{{__('Delete')}}</a>
                                      </div>
                                      </div>
                                  </div>
                                  <div class="row mb-3">
                                      <div class="col-12">
                                      <h5 class="h4 mb-2 font-weight-bolder">{{__('Shop Name: ')}}{{$val->name}}</h5>
                                      <h5 class="mb-1">{{__('Shop URl: ')}} {{$val->url}}</h5>
                                      <p class="mb-1">{{__('Document: ')}} <a href ="{{asset('assets/doc/'.$val->document)}}" class="primary" attributes-list download >{{ __('Download Document')}} </a></p>
                                      @if($val->status==1)
                                          <span class="badge badge-pill bg-success"><i class="fas fa-check"></i> {{__('Approved')}}</span>
                                      @else
                                          <span class="badge badge-pill bg-danger"><i class="fas fa-ban"></i> {{__('Pending')}}</span>
                                      @endif
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="modal fade modal-blur" id="delete{{$val->id}}" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered" role="document">
                                  <div class="modal-content">
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      <div class="modal-status bg-success"></div>

                                      <div class="modal-body text-center py-4">
                                          <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                          <h3>{{__('Confirm Delete')}}</h3>
                                          <p class="text-center mt-3">{{ __("Do you want to delete this Shop?") }}</p>
                                        </div>

                                        <div class="modal-footer">
                                          <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</a>
                                          <a href="{{route('user.merchant.shop.delete', $val->id)}}" class="btn shadow-none btn-primary" >@lang('Proceed')</a>
                                        </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                    @endforeach
                    @endif
                </div>
        </div>
    </div>
  </div>

@endsection

@push('js')
    <script>
        'use strict';
        $('.delete').on('click',function() {
            $('#modal-success').find('form').attr('action',$(this).data('route'))
            $('#modal-success').modal('show')
        })
    </script>
@endpush

