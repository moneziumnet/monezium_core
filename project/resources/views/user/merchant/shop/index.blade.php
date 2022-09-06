@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
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
  <div class="container-xl">
      <div class="row row-cards">
          <div class="col-12">
              <div class="card">
                @includeIf('includes.flash')
                  @if (count($shoplist) == 0)
                      <h3 class="text-center py-5">{{__('No Merchant Shop Data Found')}}</h3>
                  @else
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-md card-table">
                              <thead>
                                <tr>
                                  <th>{{__('No')}}</th>
                                  <th>{{__('Name')}}</th>
                                  <th>{{__('Document')}}</th>
                                  <th>{{__('Status')}}</th>
                                  <th>{{__('Action')}}</th>
                                </tr>
                              </thead>
                              <tbody>
                              @foreach($shoplist as $item)
                                  <tr>
                                      <td data-label="{{ __('No') }}">
                                      <div>
                                        {{$loop->iteration}}
                                      </div>
                                    </td>
                                      <td data-label="{{ __('Name') }}">
                                        <div>
                                          {{$item->name}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Document') }}">
                                        <div>
                                            <a href ="{{asset('assets/doc/'.$item->document)}}" attributes-list download > Download Document </a>
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Status') }}">
                                        <div>
                                            @if ($item->status == 0 )
                                                <span class="badge bg-warning">Pending</span>
                                            @else
                                                <span class="badge bg-success">Approved</span>
                                            @endif
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Action') }}">
                                        <div>

                                            @if ($item->status == 0)
                                                <a href="{{route('user.merchant.shop.edit',$item->id)}}" class="btn btn-primary btn-sm edit-{{$item->id}}" data-bs-toggle="tooltip" data-bs-original-title="@lang('edit')"><i class="fas fa-edit"></i></a>
                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm disabled" data-bs-toggle="tooltip" data-bs-original-title="@lang('view product')"><i class="fas fa-file-contract"></i></a>
                                            @else
                                                <a href="javascript:void(0)" class="btn btn-primary btn-sm disabled" data-bs-toggle="tooltip" data-bs-original-title="@lang('edit')"><i class="fas fa-edit"></i></a>
                                                <a href="{{route('user.merchant.shop.view_product',$item->id)}}" class="btn btn-primary btn-sm view-{{$item->id}}" data-bs-toggle="tooltip" data-bs-original-title="@lang('view product')"><i class="fas fa-file-contract"></i></a>
                                            @endif
                                            <a href="javascript:void(0)" data-route="{{route('user.merchant.shop.delete',$item->id)}}" class="btn btn-dark btn-sm delete" data-bs-toggle="tooltip" data-bs-original-title="@lang('delete')"><i class="fas fa-eraser"></i></a>
                                        </div>
                                      </td>
                                  </tr>
                                  @php
                              @endphp
                              @endforeach
                              </tbody>
                          </table>
                      </div>
                  @endif
              </div>
          </div>
      </div>
  </div>
  <div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-primary"></div>
            <div class="modal-body text-center py-4">
                <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <h3>{{__('Confirm Delete?')}}</h3>
            </div>
            <div class="modal-footer">
                <div class="w-100">
                    <div class="row">
                    <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                        {{__('Cancel')}}
                        </a></div>
                    <div class="col">
                        <form action="" method="get">
                            <button type="submit" class="btn btn-primary w-100 confirm">
                            {{__('Confirm')}}
                            </button>
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
    <script>
        'use strict';
        $('.delete').on('click',function() {
            $('#modal-success').find('form').attr('action',$(this).data('route'))
            $('#modal-success').modal('show')
        })
    </script>
@endpush

