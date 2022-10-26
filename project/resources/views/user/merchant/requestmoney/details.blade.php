@extends('layouts.user')


@section('contents')
<div id="loader" style="display:none;"></div>
<div  style="display:block;" id="myDiv" class="animate-bottom" >
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Request Money Details')}}
          </h2>
        </div>

      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
          <div class="col-12">
              <div class="card mb-4">
                <div class="card-body">
                    <div class="heading-area">
                        <h4 class="title">
                        {{__('Request Money')}}
                        </h4>
                    </div>
                    <div class="table-responsive-sm">
                        <table class="table">
                            <tbody>
                            <tr>
                                <th class="45%" width="45%">{{__('Request From')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ $from->company_name ?? $from->name }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Shop')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ $data->merchant_shop->name }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Request To')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ $to ? ($to->company_name ?? $to->name) : $data->receiver_name }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Amount')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ $data->currency->symbol.$data->amount }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Cost')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ $data->currency->symbol.$data->cost }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Amount To Get')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ $data->currency->symbol.($data->amount - $data->cost) }}</td>
                            </tr>
                                @if($data->status == 1)
                                    @php
                                        $bclass = "success";
                                        $bstatus = "Completed";
                                    @endphp
                                @elseif($data->status == 2)
                                    @php
                                        $bclass = "danger";
                                        $bstatus = "Cancelled";
                                    @endphp
                                @else
                                    @php
                                        $bclass = "warning";
                                        $bstatus = "Pending";
                                    @endphp
                                @endif
                            <tr>
                                <th class="45%" width="45%">{{__('Status')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ $bstatus }}</td>
                            </tr>

                            <tr>
                                <th width="45%">{{__('Details')}}</th>
                                <td width="10%">:</td>
                                <td width="45%">{{ $data->details }}</td>
                            </tr>

                            <tr>
                                <th width="45%">{{__('Request Date')}}</th>
                                <td width="10%">:</td>
                                <td width="45%">{{ $data->created_at->diffForHumans() }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
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

    $("#sendBtn").on('click',function(){
      $("#requestMoney").prop("action",$(this).data('href'))
    })
    $("#sendprocess").on('click',function(){
        document.getElementById("loader").style.display = "block";
        document.getElementById("myDiv").style.display = "none";
    })
    $("#sendprocess").on('click',function(){
        document.getElementById("loader").style.display = "block";
        document.getElementById("myDiv").style.display = "none";
    })
    $("#cancelBtn").on('click',function(){
      $("#cancelRequestMoney").prop("action",$(this).data('href'))
    })
  </script>
@endpush

