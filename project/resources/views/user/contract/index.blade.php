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
          {{__('Contract')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{ route('user.contract.create') }}" class="btn btn-primary d-sm-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Create Contract')}}
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
                  @if (count($contracts) == 0)
                      <h3 class="text-center py-5">{{__('No Contract Data Found')}}</h3>
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
                              @foreach($contracts as $item)

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
                                            <a href="{{route('user.contract.view',$item->id)}}" class="btn btn-dark btn-sm" data-bs-toggle="tooltip" data-bs-original-title="@lang('view')"><i class="fas fa-eye"></i></a>
                                            @if ($item->status == 0)
                                            <a href="{{route('user.contract.edit',$item->id)}}" class="btn btn-primary btn-sm edit-{{$item->id}}" data-bs-toggle="tooltip" data-bs-original-title="@lang('edit')"><i class="fas fa-edit"></i></a>
                                            @else
                                            <a href="javascript:void(0)" class="btn btn-primary btn-sm disabled" data-bs-toggle="tooltip" data-bs-original-title="@lang('edit')"><i class="fas fa-edit"></i></a>
                                            @endif
                                            <a href="{{route('user.contract.delete',$item->id)}}" class="btn btn-dark btn-sm" data-bs-toggle="tooltip" data-bs-original-title="@lang('delete')"><i class="fas fa-eraser"></i></a>
                                            <a href="{{route('user.contract.aoa',$item->id)}}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-original-title="@lang('Manage AoA(Act of Acceptance)')"><i class="fas fa-file-contract"></i></a>
                                            <a href="javascript:void(0)" class="btn btn-secondary btn-sm copy" data-clipboard-text="{{route('contract.view',encrypt($item->id))}}" title="{{__('Copy Contract URL')}}"><i class="fas fa-copy"></i></a>

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
</div>

@endsection

@push('js')


   <script src="{{asset('assets/user/js/clipboard.min.js')}}"></script>
    <script>
        'use strict';
        var clipboard = new ClipboardJS('.copy');
        clipboard.on('success', function(e) {
           console.log('success','Contract URL Copied')
        });
    </script>

@endpush
