@extends('layouts.user')

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Transaction')}}
          </h2>
        </div>
		<!-- <div class="col-auto ms-auto d-print-none">
			<div class="btn-list">
  
			  <a href="{{ route('user.export.pdf') }}" class="btn btn-primary d-none d-sm-inline-block">
				<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
				{{__('Export pdf')}}
			  </a>
			</div>
		  </div> -->
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                  
					<div class="table-responsive">
              <div class="col-sm-12 text-right" style="text-align: right">
                <a  href="{{route('user.transaction-export')}}">
                  <i class="fas fa-file-excel" aria-hidden="true"></i> {{__('Export')}}
                </a>
              </div>
						<table class="table card-table table-vcenter text-nowrap datatable">
						  <thead>
							<tr>
							  <th class="w-1">@lang('No'). 
								<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm text-dark icon-thick" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="6 15 12 9 18 15" /></svg>
							  </th>
								<th>@lang('Date')</th>
								<th>@lang('Transaction ID')</th>
								<th>@lang('Remark')</th>
								<th>@lang('Amount')</th>
								<th>@lang('Details')</th>
							  <th></th>
							</tr>
						  </thead>
						  <tbody>
							@forelse ($transactions as $key=>$data)
							  <tr>
								<td data-label="@lang('No')">
								  <div>
									<span class="text-muted">{{ $loop->iteration }}</span>
								  </div>
								</td>
								<td data-label="@lang('Date')">{{dateFormat($data->created_at,'d-M-Y')}}</td>
								<td data-label="@lang('Transaction ID')">
								{{__($data->trnx)}}
								</td>
								<td data-label="@lang('Remark')">
								<span class="badge badge-dark">{{ucwords(str_replace('_',' ',$data->remark))}}</span>
								</td>
								<td data-label="@lang('Amoun6t')">
									<span class="{{$data->type == '+' ? 'text-success':'text-danger'}}">{{$data->type}} {{amount($data->amount,$data->currency->type,2)}} {{$data->currency->code}}</span> 
								</td>
								<td data-label="@lang('Details')" class="text-end">
									<button class="btn btn-primary btn-sm details" data-data="{{$data}}">@lang('Details')</button>
								</td>
							  </tr>
							@empty
							  <p>@lang('NO DATA FOUND')</p>
							@endforelse
		  
						  </tbody>
						</table>
					  </div>
                      {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-primary"></div>
            <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>@lang('Transaction Details')</h3>
            <p class="trx_details"></p>
            <ul class="list-group mt-2">
               
            </ul>
            </div>
            <div class="modal-footer">
            <div class="w-100">
                <div class="row">
                <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                    @lang('Close')
                    </a></div>
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
   
      $('.details').on('click',function () { 
        var url = "{{url('user/transaction/details/')}}"+'/'+$(this).data('data').id
        $('.trx_details').text($(this).data('data').details)
        $.get(url,function (res) { 
          if(res == 'empty'){
            $('.list-group').html('<p>@lang('No details found!')</p>')
          }else{
            $('.list-group').html(res)
          }
          $('#modal-success').modal('show')
        })
      })
    </script>

@endpush
