@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
          <div class="col">
            <h2 class="page-title">
              {{__('All Bank Transaction')}}
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
                    @if (count($transactions) == 0)
                        <h3 class="text-center py-5">{{__('No Transaction Data Found')}}</h3>
                    @else
                        <div class="table-responsive">

                            <table class="table card-table table-vcenter text-nowrap datatable">
                            <thead>
                                <tr>
                                    <!--<th class="w-1">@lang('No').</th>-->
                                    <th>@lang('Date') / @lang('Transaction ID')</th>
                                    <th>@lang('Sender')</th>
                                    <th>@lang('Receiver')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Type')</th>
                                    <th class="text-end"  style="padding-right: 28px;">@lang('Details')</th>
                                </tr>
                            </thead>
                            <tbody>
                            {{-- @php
                                $i = ($transactions->currentpage() - 1) * $transactions->perpage() + 1;
                            @endphp --}}
                                @foreach (json_decode(json_encode($transactions, true)) as $key=>$data)
                                <tr>
                                    <td data-label="@lang('Date')">{{dateFormat($data->date,'d-M-Y')}} </br> {{__(str_dis($data->trnx_no))}} </td>

                                    <td data-label="@lang('Sender')">
                                        {{__(ucfirst($data->sender_name))}}
                                    </td>
                                    <td data-label="@lang('Receiver')">
                                        {{__(ucfirst($data->receiver_name))}}
                                    </td>
                                    <td data-label="@lang('Amount')">
                                         {{$data->amount}} {{$data->currency_code}}
                                    </td>
                                    <td data-label="@lang('Status')">
                                        @if ($data->status == 'complete')
                                        <span class="badge bg-success">{{ __('Completed')}}</span>
                                      @elseif($data->status == 'reject')
                                        <span class="badge bg-danger">{{ __('Rejected')}}</span>
                                      @else
                                        <span class="badge bg-warning">{{ __('Pending')}}</span>
                                      @endif
                                    </td>
                                    <td data-label="@lang('Type')">
                                        {{__(ucfirst($data->type))}}
                                    </td>
                                    <td data-label="@lang('Details')" class="text-end">
                                        @if ($data->status == 'complete')
                                        <button class="btn btn-primary btn-sm details" data-data="{{$data->tran_id}}" data-type="{{$data->type}}">@lang('Details')</button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            </table>
                        </div>
                        {{-- {{ $transactions->links() }} --}}
                    @endif
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

@push('js')
<script>
      'use strict';

      $('.details').on('click',function () {
        var url = "{{url('user/transaction/details/')}}"+'/'+$(this).data('data')
        $('.trx_details').text($(this).data('type').type)
        $('#trx_id').val($(this).data('data'))
        $.get(url,function (res) {
          if(res == 'empty'){
            $('.list-group').html("<p>@lang('No details found!')</p>")
          }else{
            $('.list-group').html(res)
          }
          $('#modal-success').modal('show')
        })
      })
    </script>

@endpush

