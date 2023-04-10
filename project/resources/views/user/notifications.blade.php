@extends('layouts.user')

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('User Notifications')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
					<div class="table-responsive">
						<table class="table card-table table-vcenter text-wrap datatable">
						  <thead>
							<tr>
								<th>@lang('Date')</th>
								<th>@lang('Description')</th>
							</tr>
						  </thead>
						  <tbody>
						  @php
							$i = ($notifications->currentpage() - 1) * $notifications->perpage() + 1;
						  @endphp
							@forelse ($notifications as $key=>$data)
							<tr>
								<td data-label="@lang('Date')">{{dateFormat($data->created_at,'d-M-Y h:i:s')}} </td>

								<td data-label="@lang('Description')">
									@php
										echo nl2br($data->description)
									@endphp
								</td>
							</tr>
							@empty
							  <p>@lang('NO DATA FOUND')</p>
							@endforelse

						  </tbody>
						</table>
					  </div>
                      {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

