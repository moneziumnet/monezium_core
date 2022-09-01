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
          {{__('AoA')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{ route('user.contract.aoa.create', $id) }}" class="btn btn-primary d-sm-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Create AoA')}}
          </a>
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
              <div class="card">
                @includeIf('includes.flash')
                  @if (count($aoa_list) == 0)
                      <h3 class="text-center py-5">{{__('No AoA Data Found')}}</h3>
                  @else
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-md card-table">
                              <thead>
                                <tr>
                                  <th>{{__('No')}}</th>
                                  <th>{{__('Title')}}</th>
                                  <th>{{__('Description')}}</th>
                                  <th>{{__('Status')}}</th>
                                  <th>{{__('Action')}}</th>
                                </tr>
                              </thead>
                              <tbody>
                                @php
                                    $counter = 1;
                                @endphp
                              @foreach($aoa_list as $item)

                                  <tr>
                                      <td data-label="{{ __('No') }}">
                                      <div>
                                        {{$counter}}
                                      </div>
                                    </td>
                                      <td data-label="{{ __('Title') }}">
                                        <div>
                                          {{$item->title}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Description') }}">
                                        <div>
                                            @if (strlen($item->description) > 20)
                                                {{(substr($item->description, 0, 10)).' ...'}}
                                            @else
                                                {{($item->description)}}
                                            @endif

                                        </div>
                                      </td>

                                      <td data-label="{{ __('Status') }}">
                                        <div>
                                            @if ($item->status == 0 )
                                                <span class="badge bg-warning">Not Signed</span>
                                            @else
                                                <span class="badge bg-success">Signed</span>
                                            @endif
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Action') }}">
                                        <div>
                                            <a href="{{route('user.contract.aoa.view',$item->id)}}" class="btn btn-dark btn-sm" data-bs-toggle="tooltip" data-bs-original-title="@lang('view')"><i class="fas fa-eye"></i></a>
                                            @if ($item->status == 0)
                                            <a href="{{route('user.contract.aoa.edit',$item->id)}}" class="btn btn-primary btn-sm edit-{{$item->id}}" data-bs-toggle="tooltip" data-bs-original-title="@lang('edit')"><i class="fas fa-edit"></i></a>
                                            @else
                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm disabled" data-bs-toggle="tooltip" data-bs-original-title="@lang('edit')"><i class="fas fa-edit"></i></a>
                                            @endif
                                            <a href="javascript:void(0)" data-route="{{route('user.contract.aoa.delete',$item->id)}}" class="btn btn-dark btn-sm delete" data-bs-toggle="tooltip" data-bs-original-title="@lang('delete')"><i class="fas fa-eraser"></i></a>
                                            <a href="javascript:void(0)" class="btn btn-secondary btn-sm copy" data-clipboard-text="{{route('aoa.view',encrypt($item->id))}}" title="{{__('Copy AoA URL')}}"><i class="fas fa-copy"></i></a>
                                        </div>
                                      </td>
                                  </tr>
                                  @php
                                  $counter++;
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


   <script src="{{asset('assets/user/js/clipboard.min.js')}}"></script>
    <script>
        'use strict';

        var clipboard = new ClipboardJS('.copy');
        clipboard.on('success', function(e) {
           console.log('success','AoA URL Copied')
        });
        $('.delete').on('click',function() {
            $('#modal-success').find('form').attr('action',$(this).data('route'))
            $('#modal-success').modal('show')
        })
    </script>

@endpush
