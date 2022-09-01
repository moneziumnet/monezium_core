@extends('layouts.user')

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Merchant Checkout Transaction')}}
          </h2>
        </div>
      </div>
      <div class="col-auto ms-auto d-print-none">

        <div class="btn-list align-items-center">
            <form action=""  class="d-flex justify-content-end">
                <div class="form-group">
                    <div class="input-group">
                        <input class="form-control shadow-none me-2" type="date" placeholder="{{__('Start Time')}}" name="s_time" value="{{$s_time ?? ''}}">
                    </div>
                </div>
                <p class="me-2">{{__(' : ')}}</p>
                <div class="form-group">
                    <div class="input-group">
                        <input class="form-control shadow-none me-2" type="date" placeholder="{{__('End Time')}}" name="e_time" value="{{$e_time ?? ''}}">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <input class="form-control shadow-none" type="text" placeholder="{{__('Transaction Id')}}" name="search" value="{{$search ?? ''}}">
                    </div>
                </div>
                <button type="submit" class="input-group-text bg-primary text-white border-0"><i class="fas fa-search"></i></button>
            </form>
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

						<table class="table card-table table-vcenter text-nowrap datatable">
						  <thead>
							<tr>
							  <th class="w-1">@lang('No').
								<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm text-dark icon-thick" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="6 15 12 9 18 15" /></svg>
							  </th>
								<th>@lang('Date')</th>
								<th>@lang('Transaction ID')</th>
								<th>@lang('Sender')</th>
								<th>@lang('Receiver')</th>
								<th>@lang('Remark')</th>
								<th>@lang('Amount')</th>
								<th>@lang('Action')</th>
								<th class="text-end"  style="padding-right: 28px;">@lang('Details')</th>
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
								{{__(str_dis($data->trnx))}}
								</td>
                                <td data-label="@lang('Sender')">
                                    {{__(json_decode($data->data)->sender ?? "")}}
                                </td>
                                <td data-label="@lang('Receiver')">
                                    {{__(json_decode($data->data)->receiver ?? "")}}
                                </td>
								<td data-label="@lang('Remark')">
								<span class="badge badge-dark">{{ucwords(str_replace('_',' ',$data->remark))}}</span>
								</td>
								<td data-label="@lang('Amount')">
									<span class="{{$data->type == '+' ? 'text-success':'text-danger'}}">{{$data->type}} {{amount($data->amount,$data->currency->type,2)}} {{$data->currency->code}}</span>
								</td>
                                <td data-label="@lang('Action')">
                                    @php
                                        if (json_decode($data->data)->status == 1) {
                                        $status  = __('Completed');
                                        $css = 'btn-primary';
                                        } elseif (json_decode($data->data)->status == 2) {
                                        $status  = __('Rejected');
                                        $css = 'btn-danger';
                                        } else {
                                        $status  = __('Pending');
                                        $css = 'btn-primary';
                                        }
                                    @endphp
                                    <a class="btn {{$css}} btn-sm status" data-bs-toggle="modal" data-bs-target="#modal-success-confirm{{$data->id}}" data-data="{{json_decode($data->data)->status}}">{{$status}}</a>
                                    <div class="modal modal-blur fade" id="modal-success-confirm{{$data->id}}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            <div class="modal-status bg-primary"></div>
                                            <div class="modal-body text-center py-4">
                                            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                            <h3>@lang('Do you want to update the status?')</h3>
                                            <p class="trx_details"></p>
                                            <ul class="list-group mt-2">
                                            </ul>
                                            </div>
                                            <div class="modal-footer text-center">
                                            <div class="w-100">
                                                <div class="row justify-content-between">
                                                    <div class="col-md-6">
                                                        <a href="{{route('user.merchant.checkout.transaction.status', ['id' => $data->id, 'status' => 1])}}" class="btn btn-primary" >
                                                            @lang('Complete')
                                                        </a>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <a href="{{route('user.merchant.checkout.transaction.status', ['id' => $data->id, 'status' => 2])}}" class="btn btn-danger" >
                                                            @lang('Reject')
                                                        </a>
                                                    </div>
                                                    </div>
                                            </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
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
