@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">

        <div class="row align-items-center">
          <div class="col">
            <h2 class="page-title">
              {{__('Bank Transaction')}}
            </h2>
          </div>
        </div>
        <div class="col-auto ms-auto d-print-none mt-3">

            <div class="btn-list align-items-center">
                <form action=""  class="d-flex justify-content-end">
                    <div class="form-group me-3">
                        <select  class="form-control me-2 shadow-none" onChange="window.location.href=this.value">
                            <option value="{{filter('bankaccount','')}}">@lang('All Bank')</option>
                            @foreach ($bankaccounts as $value)
                                <option value="{{filter('bankaccount',$value->iban)}}" {{request('bankaccount') == $value->iban ? 'selected':''}}>@lang(ucwords($value->iban))</option>
                            @endforeach
                        </select>
                    </div>

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
                                    <th >@lang('Description')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Fee')</th>
                                    <th class="text-end"  style="padding-right: 28px;">@lang('Details')</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                $i = ($transactions->currentpage() - 1) * $transactions->perpage() + 1;
                            @endphp
                                @foreach ($transactions as $key=>$data)
                                <tr>
                                    <!--<td data-label="@lang('No')">
                                    <div>
                                        <span class="text-muted">{{ $i++ }}</span>
                                    </div>
                                    </td>-->
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
        var url = "{{url('user/transaction/details/')}}"+'/'+$(this).data('data').id
        var pdf_url = "{{url('user/transaction/details/pdf/')}}"+'/'+$(this).data('data').id
        $('.trx_details').text($(this).data('data').details)
        $('#trx_id').val($(this).data('data').id)
        $('.print_pdf').attr('href', pdf_url)
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

@endpush

