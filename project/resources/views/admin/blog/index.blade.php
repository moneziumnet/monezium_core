@extends('layouts.admin')

@section('content')

<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between">
	<h5 class=" mb-0 text-gray-800 pl-3">{{ __('Posts') }}</h5>
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
		<li class="breadcrumb-item"><a href="javascript:;">{{ __('Blog') }}</a></li>
		<li class="breadcrumb-item"><a href="{{ route('admin.blog.index') }}">{{ __('Posts') }}</a></li>
	</ol>
	</div>
</div>

<div class="row mt-3">
  <div class="col-lg-12">
	@include('includes.admin.form-success')

	<div class="card mb-4">
	  <div class="table-responsive p-3">
        <div class="btn-list align-items-center">
            <div class="d-flex justify-content-around mt-3">
                <div class="form-group mr-3  row">
                    <label for="category" class="col-form-label">{{ __('Category') }}</label>
                    <div class="col">

                    <select name="category" id="category" class="form-control mr-2 shadow-none" >
                        <option value="">@lang('All')</option>
                        @foreach ($modules as $module)
                          <option value="{{$module->name}}">@lang($module->name)</option>
                        @endforeach

                    </select>
                    </div>
                </div>
                <div class="form-group mr-3 row">
                    <label for="global_search" class="col-form-label">{{ __('Search') }}</label>
                    <div class="col">
                    <input class="form-control shadow-none mr-2" type="text"placeholder="{{__('Search')}}" id="global_search" name="global_search" >
                    </div>
                </div>

            </div>
        </div>
		<table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
		  <thead class="thead-light">
			<tr>
                <th>{{ __('Featured Image') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Post Title') }}</th>
                <th>{{ __('Views') }}</th>
                <th>{{ __('Post Date') }}</th>
                <th>{{ __('Tags') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Action') }}</th>
			</tr>
		  </thead>
		</table>
	  </div>
	</div>
  </div>
</div>


<div class="modal fade confirm-modal" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ __("Update Status") }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="text-center">{{ __("You are about to change the status.") }}</p>
				<p class="text-center">{{ __("Do you want to proceed?") }}</p>
			</div>

			<div class="modal-footer">
				<a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
				<a href="javascript:;" class="btn btn-success btn-ok">{{ __("Update") }}</a>
			</div>
		</div>
	</div>
</div>



<div class="modal fade confirm-modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ __("Confirm Delete") }}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="text-center">{{__("You are about to delete this Blog.")}}</p>
				<p class="text-center">{{ __("Do you want to proceed?") }}</p>
			</div>
			<div class="modal-footer">
				<a href="javascript:;" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</a>
				<a href="javascript:;" class="btn btn-danger btn-ok">{{ __("Delete") }}</a>
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
               searching: false,
               ajax:
               {
                    url : '{{ route('admin.blog.datatables') }}',
                    data : function (d) {
                        d.global_search = $('#global_search').val(),
                        d.category = $('#category').val()
                    }
                },
               columns: [

                        { data: 'photo', name: 'photo' },
                        { data: 'category', name: 'category' },
                        { data: 'title', name: 'title' },
                        { data: 'views', name: 'views' },
                        { data: 'date', name: 'date' },
                        { data: 'tags', name: 'tags' },
                        { data: 'status', name: 'status' },
            			{ data: 'action', name: 'action' }

                     ],
                language : {
                	processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
                }
            });
			$(function() {
            $(".btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
                '<a class="btn btn-primary" href="{{route('admin.blog.create')}}">'+
            '<i class="fas fa-plus"></i> {{__('Add New Post')}}'+
            '</a>'+
            '</div>');
            })
            $('#global_search').on('keyup', function () {
                table.draw();
            });
            $('#category').on('change', function () {
                table.draw();
            });

</script>

@endsection
