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
                          <table class="table table-vcenter table-mobile-md card-table datatable">
                              <thead>
                                <tr>
                                  <th>{{__('No')}}</th>
                                  <th>{{__('Contractor')}}</th>
                                  <th>{{__('Beneficiary')}}</th>
                                  <th>{{__('Title')}}</th>
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
                                    <td data-label="{{ __('Contractor') }}">
                                        <div>
                                          {{$item->contractor->name.' ('.$item->contractor->email.')'}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Beneficiary') }}">
                                        <div>
                                          {{$item->beneficiary->name.' ('.$item->beneficiary->email.')'}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Title') }}">
                                        <div>
                                          {{$item->title}}
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
                                            <a href="javascript:void(0)" data-route="{{route('user.contract.delete',$item->id)}}" class="btn btn-dark btn-sm delete" data-bs-toggle="tooltip" data-bs-original-title="@lang('delete')"><i class="fas fa-eraser"></i></a>
                                            <a href="{{route('user.contract.aoa',$item->id)}}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-original-title="@lang('Manage AoA(Act of Acceptance)')"><i class="fas fa-file-contract"></i></a>
                                            <a href="javascript:void(0)" class="btn btn-secondary btn-sm copy" data-clipboard-text="{{route('contract.view',['id' => encrypt($item->id), 'role' => encrypt('contractor')])}}" title="{{__('Copy Contract URL')}}"><i class="fas fa-copy"></i></a>
                                            <a href="javascript:void(0)" data-email="{{$item->contractor->email}}" data-id="{{$item->id}}" class="btn btn-dark btn-sm send_email" data-bs-toggle="tooltip" data-bs-original-title="@lang('Send Email')"><i class="fas fa-mail-bulk"></i></a>

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
                      {{ $contracts ->links() }}
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

<div class="modal modal-blur fade" id="modal-success-mail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
          <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
          <h3>{{__('Send E-mail')}}</h3>
          <div class="row text-start">
              <div class="col">
                  <form action="{{ route('user.contract.send.mail') }}" method="post">
                      @csrf
                      <div class="row">
                          <div class="form-group mt-2">
                              <label class="form-label required">{{__('Email Address')}}</label>
                              <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('test@gmail.com')}}" type="email" required>
                          </div>
                      </div>
                      <input name="contract_id" id="contract_id" type="hidden" required>
                      <div class="row mt-3">
                          <div class="col">
                              <button type="submit" class="btn btn-primary w-100 confirm">
                              {{__('Send')}}
                              </button>
                          </div>
                      </div>
                  </form>
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
           alert('Contract URL Copied.')
        });

        $('.delete').on('click',function() {
            $('#modal-success').find('form').attr('action',$(this).data('route'))
            $('#modal-success').modal('show')
        })

        $('.send_email').on('click',function() {
            $('#modal-success-mail').modal('show');
            $('#modal-success-mail #email').val($(this).data('email'));
            $('#modal-success-mail #contract_id').val($(this).data('id'));
        })
    </script>

@endpush
