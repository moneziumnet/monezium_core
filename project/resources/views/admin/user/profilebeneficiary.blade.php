@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="d-sm-flex align-items-center justify-content-between py-3">
    <h5 class=" mb-0 text-gray-800 pl-3">{{ $data->name }}</h5>
    <ol class="breadcrumb py-0 m-0">
      <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
      <li class="breadcrumb-item"><a href="{{ route('admin.user.index') }}">{{ __('User List') }}</a></li>
    </ol>
  </div>
</div>


<div class="row mt-3">
  <div class="col-lg-12">
    <div class="card tab-card">
      @include('admin.user.profiletab')

      <div class="tab-content" id="myTabContent">
        <div class="m-3">
            @include('includes.admin.form-success')
        </div>
        <div class="tab-pane fade show active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
        <div class="card-body">
            <div class="card mb-4">

                <div class="table-responsive p-3">
                    <table id="geniustable" class="table table-hover dt-responsive text-wrap " cellspacing="0" width="100%">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('Type')</th>
                        <th>@lang('Beneficiary Name')</th>
                        <th>@lang('Bank Name')</th>
                        <th>@lang('Bank Address')</th>
                        <th>@lang('Details')</th>
                        <th>@lang('Action')</th>
                    </tr>
                    </thead>
                    </table>
                </div>
            </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<div class="modal modal-blur fade" id="modal-details" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="beneficiary_details"></div>
        </div>
    </div>
    </div>
</div>
@endsection
@section('scripts')

<script type="text/javascript">
	"use strict";

    var table = $('#geniustable').DataTable({
           ordering: false,
           processing: true,
           serverSide: true,
           searching: true,
           ajax: '{{ route('admin-user-beneficiary-database',$data->id) }}',
           columns: [
                { data: 'type', name: 'type' },
                { data: 'name', name: 'name' },
                { data: 'bank_name', name: 'bank_name' },
                { data: 'bank_address', name: 'bank_address' },
                { data: 'detail', name: 'detail' },
                { data: 'action', name: 'action' },
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

        $(function() {
            $(".btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
                '<a class="btn btn-primary" href="{{route('admin-user-beneficiary-create', $data->id)}}">'+
            '<i class="fas fa-plus"></i> Add New Beneficiary'+
            '</a>'+
            '</div>');
        });

        function getDetails(e) {
		var url = "{{url('admin/user/beneficiary/details/')}}"+'/'+e.target.getAttribute('id');
		$.get(url,function (res) {
			$('.beneficiary_details').html(res);
			$('#modal-details').modal('show');
			$('.closed').on('click', function() {
				$('#modal-details').modal('hide');
			});
		})
	};

</script>

@endsection
