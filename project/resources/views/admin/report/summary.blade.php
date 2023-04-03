@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="d-sm-flex align-items-center justify-content-between">
            <h5 class=" mb-0 text-gray-800 pl-3">{{ __('Summary Fee') }}</h5>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>

                <li class="breadcrumb-item"><a
                        href="{{ route('admin.report.transaction.summary') }}">{{ __('Summary Fee') }}</a></li>
            </ol>
        </div>
    </div>
    <div class="page-header d-print-none">
            <form action=""  class=" mt-3 row d-flex justify-content-end">
                <div class="form-group col-lg-2">
                    <div class="input-group">
                        <input class="form-control shadow-none mr-2" type="date" placeholder="{{__('Start Time')}}" name="s_time" value="{{$s_time ?? ''}}"  >
                    </div>
                </div>
                <div class="form-group col-lg-2">
                    <div class="input-group">
                        <input class="form-control shadow-none mr-2" type="date" placeholder="{{__('End Time')}}" name="e_time" value="{{$e_time ?? ''}}"  >
                    </div>
                </div>
                <div class="form-group col-lg-3">
                    <select  class="form-control shadow-none" onChange="window.location.href=this.value">
                        <option value="{{filter('remark','all_mark')}}">@lang('All Fee')</option>
                        @foreach ($remark_list as $value)
                            <option value="{{filter('remark',$value)}}" {{request('remark') == $value ? 'selected':''}}>@lang(ucwords(str_replace('_',' ',$value)))</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-lg-1">
                <button type="submit" class="btn bg-primary text-white"><i class="fas fa-search"></i></button>
                </div>
            </form>
    </div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards justify-content-center">
          <div class="col-sm-12 text-right mb-3" style="text-align: right">
            {{__('Total Fee Balance: '.$balance)}}
          </div>
          @if ($flag)
                <div class="card">
                        @if (count($transactions) == 0)

                            <h3 class="text-center py-5">{{__('No Transaction Data Found')}}</h3>
                        @else
                            <div class="row mb-3 p-3" style="max-height: 600px;overflow-y: scroll;">
                                @foreach ($transactions as $key=>$data)
                                <div class="col-xl-4 col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-3">{{__(ucwords(str_replace('_',' ',$data['fee'])))}}</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{__($data['balance'])}}</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-euro-sign fa-2x text-success"></i>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                            </div>
                        @endif
                </div>

            @else
            <div class="col-12">
                <div class="card">
                        @if (count($transactions) == 0)

                        <h3 class="text-center py-5">{{__('No Transaction Data Found')}}</h3>
                        @else
                        <div class="table-responsive">

                            <table class="table text-wrap datatable">
                            <thead>
                                <tr>
                                    <!--<th class="w-1">@lang('No').</th>-->
                                    <th>@lang('Date') / @lang('Transaction ID')</th>
                                    <th>@lang('Sender')</th>
                                    <th>@lang('Receiver')</th>
                                    <th >@lang('Description')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Fee')</th>
                                    <th class="text-end"  style="padding-right: 28px;">@lang('Details')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $key=>$data)
                                <tr>
                                    <td data-label="@lang('Date')">{{dateFormat($data->created_at,'d-M-Y')}} </br> {{__(str_dis($data->trnx))}} </td>

                                    <td style="white-space: normal; max-width:200px;" data-label="@lang('Sender')">
                                        {{__(json_decode($data->data)->sender ?? "")}}
                                    </td>
                                    <td data-label="@lang('Receiver')">
                                        {{__(json_decode($data->data)->receiver ?? "")}}
                                    </td>
                                    <td   style="white-space: normal; max-width:400px;" data-label="@lang('Description')">
                                        {{__(json_decode($data->data)->description ?? "")}} </br> <span class="badge badge-dark">{{ucwords(str_replace('_',' ',$data->remark))}}</span>
                                    </td>
                                    <td data-label="@lang('Amount')">
                                        <span class="{{$data->type == '+' ? 'text-success':'text-danger'}}">{{$data->type}} {{amount($data->amount,$data->currency->type,2)}} {{$data->currency->code}}</span>
                                    </td>
                                    <td data-label="@lang('Fee')" class="text-end">
                                        <span class="{{$data->type == '+' ? 'text-danger':'text-danger'}}">{{'-'}} {{amount($data->charge,$data->currency->type,2)}} {{$data->currency->code}}</span>
                                    </td>
                                    <td data-label="@lang('Details')" class="text-end">
                                        <button class="btn btn-primary btn-sm details" data-data="{{$data}}">@lang('Details')</button>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                            </table>
                        </div>
                        {{ $transactions->links() }}
                        @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content">
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
                    <div class="col mt-2">
                        <a href="#" class="btn w-100" data-bs-dismiss="modal">
                        @lang('Close')
                        </a>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script type="text/javascript">
      'use strict';

      $('.details').on('click',function () {
        var url = "{{url('admin/bank/report/transaction/summary/details/')}}"+'/'+$(this).data('data').id
        $.get(url,function (res) {
          if(res == 'empty'){
            $('.list-group').html("<p>@lang('No details found!')</p>")
          }else{
            $('.list-group').html(res)
          }
          $('#modal-success').modal('show')
        })
      })
      $('.send_email').on('click',function() {
            $('#modal-success-mail').modal('show');
        })
    </script>
@endsection
