@extends('layouts.user')

@push('css')
<style>
    /* Center the loader */
    #loader {
      position: absolute;
      left: 50%;
      top: 50%;
      z-index: 1;
      width: 120px;
      height: 120px;
      margin: -76px 0 0 -76px;
      border: 16px solid #f3f3f3;
      border-radius: 50%;
      border-top: 16px solid #3498db;
      -webkit-animation: spin 2s linear infinite;
      animation: spin 2s linear infinite;
    }

    @-webkit-keyframes spin {
      0% { -webkit-transform: rotate(0deg); }
      100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Add animation to "page content" */
    .animate-bottom {
      position: relative;
      -webkit-animation-name: animatebottom;
      -webkit-animation-duration: 1s;
      animation-name: animatebottom;
      animation-duration: 1s
    }

    @-webkit-keyframes animatebottom {
      from { bottom:-100px; opacity:0 }
      to { bottom:0px; opacity:1 }
    }

    @keyframes animatebottom {
      from{ bottom:-100px; opacity:0 }
      to{ bottom:0; opacity:1 }
    }

    #myDiv {
      display: none;
      text-align: center;
    }
    </style>
@endpush


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
                                <td class="45%" width="45%">{{ $from->name }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Request To')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ $to->name }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Amount')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ showprice($data->amount,$data->currency) }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Cost')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ showprice($data->cost,$data->currency) }}</td>
                            </tr>

                            <tr>
                                <th class="45%" width="45%">{{__('Amount To Get')}}</th>
                                <td width="10%">:</td>
                                <td class="45%" width="45%">{{ showprice(($data->amount - $data->cost),$data->currency) }}</td>
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
                            @if ($data->status == 0)
                                <tr>
                                    <td class="text-center" colspan="3">

                                            <a href="javascript:;" id="sendBtn" data-href="{{ route('user.request.money.verify',$data->id) }}" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-success">
                                            {{__('Send')}}
                                            </a>
                                            <a href="javascript:;" id="cancelBtn" data-href="{{ route('user.request.money.cancel',$data->id) }}" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal-cancel">
                                            {{__('Cancel')}}
                                            </a>

                                    </td>
                                </tr>
                            @endif

                            </tbody>
                        </table>
                    </div>
                </div>
              </div>
          </div>
        </div>
    </div>
</div>

<div class="modal modal-blur confirm-modal fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <form id="requestMoney" action="" method="post">
          @csrf
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="modal-status bg-success"></div>

          <div class="modal-body text-center py-4">
            <p class="text-center">{{ __("You are about to change the status.") }}</p>
            <p class="text-center">{{ __("Do you want to proceed?") }}</p>
          </div>

          <div class="modal-footer">
            <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</a>
            <button type="submit" class="btn shadow-none btn--success" id="sendprocess" data-bs-dismiss="modal">@lang('Proceed')</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal modal-blur confirm-modal fade" id="modal-cancel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content">
        <form id="cancelRequestMoney" action="" method="post">
          @csrf
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          <div class="modal-status bg-success"></div>

          <div class="modal-body text-center py-4">
            <p class="text-center">{{ __("You are want to cancel this request money.") }}</p>
            <p class="text-center">{{ __("Do you want to proceed?") }}</p>
          </div>

          <div class="modal-footer">
            <a href="javascript:;" class="btn btn-secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</a>
            <button type="submit" class="btn shadow-none btn--success" id="cancelprocess" data-bs-dismiss="modal">@lang('Proceed')</button>
          </div>
        </form>
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

