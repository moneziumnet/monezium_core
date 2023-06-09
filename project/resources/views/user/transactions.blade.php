@extends('layouts.user')

@section('title', __('Transactions'))

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Transaction')}}
          </h2>
        </div>
		<!-- <div class="col-auto ms-auto d-print-none">
			<div class="btn-list">

			  <a href="{{ route('user.export.pdf') }}" class="btn btn-primary d-sm-inline-block">
				<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
				{{__('Export pdf')}}
			  </a>
			</div>
		  </div> -->
      </div>
      <div class="col-auto ms-auto d-print-none mt-3">

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
                <div class="form-group me-3">
                    <select  class="form-control me-2 shadow-none" onChange="window.location.href=this.value">
                        <option value="{{filter('remark','')}}">@lang('All Remark')</option>
                        @foreach ($remark_list as $value)
                            <option value="{{filter('remark',$value)}}" {{request('remark') == $value ? 'selected':''}}>@lang(ucwords(str_replace('_',' ',$value)))</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group me-3">
                    <select  class="form-control me-2 shadow-none" onChange="window.location.href=this.value">
                        <option value="{{filter('wallet_id','')}}">@lang('All Account')</option>
                        @foreach ($wallet_list as $value)
                         @if($value != null && DB::table('wallets')->where('id', $value)->first() != null)
                            <option value="{{filter('wallet_id',$value)}}" {{request('wallet_id') == $value ? 'selected':''}}>@lang(DB::table('wallets')->where('id', $value)->first()->wallet_no)</option>
                        @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div class="input-icon">
                        <span class="input-icon-addon">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"></path><path d="M21 21l-6 -6"></path></svg>
                        </span>
                        <input class="form-control shadow-none" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Search ...')}}" name="search" value="{{$search ?? ''}}">
                    </div>
                </div>
                <div class="form-group">

                <button type="submit" class="input-group-text bg-primary text-white border-0 ms-2" style="height: 35px;"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
          <div class="col-sm-12 text-right" style="text-align: right">
            @php
              $str_end_time = $e_time ?? '';
            @endphp
            <a href="{{url('user/transactions-pdf?search='.request('search').'&wallet_id='.request('wallet_id').'&remark='.request('remark').'&s_time='.request('s_time').'&e_time='.$str_end_time)}}" id="download_pdf" class="tx-color">
              <i class="fas fa-file-pdf" aria-hidden="true"></i> {{__('PDF')}}
            </a> &nbsp;
            <a href="{{url('user/transactions-export?search='.request('search').'&wallet_id='.request('wallet_id').'&remark='.request('remark').'&s_time='.request('s_time').'&e_time='.$str_end_time)}}" class="tx-color">
              <i class="fas fa-file-excel" aria-hidden="true"></i> {{__('Export')}}
            </a>
          </div>
            <div class="col-12">
                <div class="card">

					<div class="table-responsive">

						<table class="table card-table table-vcenter text-wrap datatable">
						  <thead>
							<tr>
							    <!--<th class="w-1">@lang('No').</th>-->
								<th>@lang('Date') / @lang('Transaction ID')</th>
								<th>@lang('Sender')</th>
								<th>@lang('Receiver')</th>
								<th >@lang('Description')</th>
								<th class="text-end">@lang('Amount')</th>
								<th class="text-end">@lang('Fee')</th>
								<th class="text-end"  style="padding-right: 28px;">@lang('Details')</th>
							</tr>
						  </thead>
						  <tbody>
							@forelse ($transactions as $key=>$data)
							<tr>
								<td data-label="@lang('Date')">{{dateFormat($data->created_at,'d-M-Y')}} </br> {{__(str_dis($data->trnx))}} </td>

								<td data-label="@lang('Sender')">
									{{__(json_decode($data->data)->sender ?? "")}}
								</td>
								<td data-label="@lang('Receiver')">
									{{__(json_decode($data->data)->receiver ?? "")}}
								</td>
								<td   style="white-space: normal; max-width:400px;" data-label="@lang('Description')">
									{{__(json_decode($data->data)->description ?? "")}} </br> <span class="badge badge-dark">{{ucwords(str_replace('_',' ',$data->remark))}}</span>
								</td>
								<td data-label="@lang('Amount')" class="text-end">
									<span class="{{$data->type == '+' ? 'text-success':'text-danger'}}">{{$data->type}} {{amount($data->amount,$data->currency->type,2)}} {{$data->currency->code}}</span>
								</td>
								<td data-label="@lang('Fee')" class="text-end">
									<span class="{{$data->type == '+' ? 'text-danger':'text-danger'}}">{{'-'}} {{amount($data->charge,$data->currency->type,2)}} {{$data->currency->code}}</span>
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
            {{ $transactions->appends(['search' => $search, 'remark' => $remark ?? '', 'wallet_id' => $wallet_id ?? '', 's_time' => $s_time, 'e_time' =>$e_time])->links() }}

                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-title">
            <div class="ms-3">
              <p>@lang('Transaction Details')</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <hr class="mt-3 mb-5">
          </div>
          <div class="modal-body text-center">
            <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <p class="trx_info_amount"></p>
            <p class="trx_info_date"></p>
            <span class="badge bg-success-lt">{{__("Paid")}}</span>
            <ul class="list-group mt-2">
    
            </ul>
          </div>
            <div class="modal-footer">
            <div class="w-100">
                <div class="row">
                    <div class="col-sm-6">
                        {{-- <a class="print_pdf btn btn-primary w-100" >
                            @lang('Print PDF')
                        </a> --}}
                        <a class="print_pdf tx-color">
                          <i class="fas fa-file-pdf me-1"></i> @lang('Download Pdf')
                        </a>
                    </div>
                    <div class="col-sm-6 d-flex justify-content-end">
                        <a href="#" class="send_email tx-color">
                          <i class="fas fa-print me-1"></i> @lang('Send Email')
                        </a>
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
          <div class="modal-title" style="border-radius: 10px 10px 0 0">
            <div class="ms-3">
              <p>{{('E-mail')}}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
            <div class="modal-body text-center">
              <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
              <h3>{{__('Send E-mail')}}</h3>
              <div class="row text-start">
                  <div class="col">
                      <form action="{{ route('user.trxDetails.mail') }}" method="post">
                          @csrf
                          <div class="row">
                              <div class="form-group mt-2">
                                  <label class="form-label required">{{__('Email Address')}}</label>
                                  <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('test@gmail.com')}}" type="email" value="{{$user->email}}" required>
                              </div>
                          </div>
                          <input name="trx_id" id="trx_id" type="hidden" required>
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
<script>
      'use strict';

      $('.details').on('click',function () {
        var url = "{{url('user/transaction/details/')}}"+'/'+$(this).data('data').id
        var pdf_url = "{{url('user/transaction/details/pdf/')}}"+'/'+$(this).data('data').id
        $('.trx_details').text($(this).data('data').details)
        $('#trx_id').val($(this).data('data').id)
        $('.print_pdf').attr('href', pdf_url)
        $.get(url,function (res) {
          if(res == 'empty'){
            $('.list-group').html("<p>@lang('No details found!')</p>")
          }else{
            var parser = new DOMParser();
            var node = parser.parseFromString(res, "text/html");
            $('.trx_info_amount').text(node.getElementById("trnx_amount").textContent + " to " + node.getElementById("Receiver").textContent);
            $('.trx_info_date').text("Completed " + node.getElementById("trnx_date").textContent);
            $('.list-group').html(res)
          }
          $('#modal-success').modal('show')
        })
      })
      $('.send_email').on('click',function() {
            $('#modal-success-mail').modal('show');
        })
    </script>

@endpush
