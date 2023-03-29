@extends('layouts.user')

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        @include('user.settingtab')
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Login Activity')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
					<div class="table-responsive">
						<table class="table card-table table-vcenter text-wrap datatable">
						  <thead>
							<tr>
								<th>@lang('Date')</th>
								<th>@lang('Description')</th>
								<th>@lang('URL')</th>
								<th >@lang('IP')</th>
								<th>@lang('User Agent')</th>
							</tr>
						  </thead>
						  <tbody>
						  @php
							$i = ($history->currentpage() - 1) * $history->perpage() + 1;
						  @endphp
							@forelse ($history as $key=>$data)
							<tr>
								<td data-label="@lang('Date')">{{dateFormat($data->created_at,'d-M-Y h:i:s')}} </td>

								<td data-label="@lang('Description')">
									{{__($data->subject)}}
								</td>
								<td data-label="@lang('URL')">
									{{__($data->url)}}
								</td>
                                <td data-label="@lang('IP')">
									{{__($data->ip)}}
								</td>
                                <td data-label="@lang('User Agent')">
									{{__($data->agent)}}
								</td>
							</tr>
							@empty
							  <p>@lang('NO DATA FOUND')</p>
							@endforelse

						  </tbody>
						</table>
					  </div>
                      {{ $history->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

