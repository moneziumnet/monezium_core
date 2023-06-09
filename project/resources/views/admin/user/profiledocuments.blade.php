@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->company_name ?? $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card mt-1 tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        @php
        $currency = defaultCurr();
        @endphp
        @include('includes.admin.form-success')
        <div class="tab-pane fade show p-2 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
            <div class="card mb-4 mt-3 p-2">
            <div class="card-body">
                <div class="row">

                    <div class="table-responsive mb-3 mt-3">
                    <div class="col-sm-12 text-right mb-3">
                        <a class="btn btn-primary" href="{{route('admin-user.createfile', $data->id)}}">
                        <i class="fas fa-plus"></i> {{__('Add Documents')}}
                        </a>
                    </div>
                    <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                        <thead class="thead-light">
                        <tr>

                            <th>{{__('Name')}}</th>
                            <th>{{__('View Document')}}</th>
                            <th>{{__('Type')}}</th>
                            <th>{{__('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!empty($documents))
                            @foreach($documents as $document)
                            <tr>
                                <td>
                                    {{ucfirst($document->name)}}
                                </td>
                                <td>
                                <a href="{{route('admin-user.view-document', $document->id)}}" target="{{$document->type == 'file' ? '_blank' : ''}}">
                                    <button type="button" class="btn btn-primary btn-sm btn-rounded">{{__("View Document")}} </button></a>
                                </td>
                                <td>
                                    {{ucfirst($document->type)}}
                                </td>
                                <td>
                                <div class="btn-group mb-1">
                                    <button type="button" class="btn btn-primary btn-sm btn-rounded dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                    </button>
                                    <div class="dropdown-menu" x-placement="bottom-start">
                                    <a href="javascript:;" data-toggle="modal" data-target="#deleteModal" class="dropdown-item" data-href="{{route('admin-user.document-delete', $document->id)}}">{{__("Delete")}}</a>
                                    </div>
                                </div>
                                </td>

                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                    </div>
                    @if($documents->hasPages())
                        {{ $documents->links() }}
                    @endif
                </div>
               </div>

            </div>
        </div>
       </div>
    </div>
  </div>
</div>
<!--Row-->

<div class="modal fade confirm1-modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-center">{{ __("Do you want to proceed?") }}</p>
      </div>
      <div class="modal-footer">
        <a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
        <a href="javascript:;" class="btn btn-danger btn1-ok">{{ __("Delete") }}</a>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
  "use strict";
  $('.confirm1-modal').on('show.bs.modal', function(e) {
    $(this).find('.btn1-ok').attr('href', $(e.relatedTarget).data('href'));
  });

</script>
@endsection
