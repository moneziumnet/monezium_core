@extends('layouts.admin')


@section('content')
<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between">
	<h5 class=" mb-0 text-gray-800 pl-3">{{ __('Withdraw Method') }}</h5>
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
		<li class="breadcrumb-item"><a href="{{ route('admin.withdraw') }}">{{ __('Withdraw Method') }}</a></li>
	</ol>

	</div>
</div>

<div class="row mt-3">
    <div class="col-lg-12">
  
      @include('includes.admin.form-success')
  
      <div class="card mb-4">
        <div class="table-responsive p-3">
          <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
            <thead class="thead-light">
              <tr>
                  <th>{{__('ID')}}</th>
                  <th>{{__('Method Name')}}</th>
                  <th>{{__('Fixed Charge')}}</th>
                  <th>{{__('Percentage Charge')}}</th>
                  <th>{{__('Status')}}</th>
                  <th>{{__('Created Date')}}</th>
                  <th>{{__('Action')}}</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>

    {{-- <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header justify-content-end">
                    <div class="card-header-form">
                        <form method="GET" action="{{ route('admin.withdraw.search') }}">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search">
                                <div class="input-group-append">
                                    <button class="btn btn-primary border-0"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-body text-center">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tr>
                                    <th>@lang('Sl')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                @forelse ($withdraws as $key => $withdraw)
                                    <tr>

                                        <td data-label="@lang('Sl')">{{$key +  $withdraws->firstItem() }}</td>
                                        <td data-label="@lang('Name')">{{ $withdraw->name }}</td>

                                        <td data-label="@lang('status')">
                                            @if ($withdraw->status)
                                                <div class="badge badge-success">@lang('Active')</div>
                                            @else
                                                <div class="badge badge-danger">@lang('Inactive')</div>
                                            @endif
                                        </td>

                                        @if (access('withdraw method edit'))
                                        <td data-label="@lang('Action')">
                                            <a href="{{ route('admin.withdraw.edit', $withdraw->id) }}"  class="btn btn-primary update"><i class="fa fa-pen"></i></a>
                                        </td>
                                        @else
                                        @lang('N/A')
                                        @endif
                                    </tr>
                                @empty

                                    <tr>

                                        <td class="text-center" colspan="100%">@lang('No Data Found')</td>

                                    </tr>

                                @endforelse
                            </table>
                        </div>
                    </div>
                    @if ($withdraws->hasPages())
                        <div class="card-footer">
                            {{ $withdraws->links('admin.partials.paginate') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div> --}}


@endsection
@section('scripts')

<script type="text/javascript">
	"use strict";

    var table = $('#geniustable').DataTable({
           ordering: false,
           processing: true,
           serverSide: true,
           searching: true,
           ajax: '{{ route('admin.withdraw.method.datatables',['status' => 'all']) }}',
           columns: [
				{ data: 'id', name: 'id' },
				{ data: 'method', name: 'method' },
				{ data: 'fixed', name: 'fixed' },
				{ data: 'percentage', name: 'percentage' },
				{ data: 'status', name: 'status' },
				{ data: 'created_at', name: 'created_at' },
				{ data: 'action', searchable: false, orderable: false }
            ],
            language : {
                processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
            }
        });

        $(function() {
        $(".btn-area").append('<div class="col-sm-12 col-md-4 pr-3 text-right">'+
            '<a class="btn btn-primary" href="{{route('admin.withdraw.create')}}">'+
        '<i class="fas fa-plus"></i> {{__('Add new Method')}}'+
        '</a>'+
        '</div>');
    });

</script>

@endsection