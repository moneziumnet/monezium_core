@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Invoices')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{ route('user.invoice.create') }}" class="btn btn-primary d-sm-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Create Invoice')}}
          </a>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="page-body">
  <div class="container-xl">
      <div class="row row-cards">
          <div class="col-12">
              <div class="card">
                  @if (count($invoices) == 0)
                      <h3 class="text-center py-5">{{__('No Invoice Data Found')}}</h3>
                  @else
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-md card-table">
                              <thead>
                                <tr>
                                  <th>{{__('Invoice')}}</th>
                                  <th>{{__('Invoice To')}}</th>
                                  <th>{{__('Email')}}</th>
                                  <th>{{__('Type')}}</th>
                                  <th>{{__('Amount')}}</th>
                                  <th>{{__('Pay Status')}}</th>
                                  <th>{{__('Publish Status')}}</th>
                                  <th>{{__('Date')}}</th>
                                  <th>{{__('Action')}}</th>
                                </tr>
                              </thead>
                              <tbody>
                              @foreach($invoices as $item)
                                  <tr>
                                      <td data-label="{{ __('Item Number') }}">
                                      <div>
                                        {{$item->number}}
                                      </div>
                                    </td>
                                      <td data-label="{{ __('Invoice To') }}">
                                        <div>
                                          {{$item->invoice_to}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Email') }}">
                                        <div>
                                          {{$item->email}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Type') }}">
                                        <div>
                                          {{$item->type}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Amount') }}">
                                        <div>
                                          {{amount($item->final_amount,$item->currency->type,2)}} {{$item->currency->code}}
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Pay Status') }}">
                                        <div>
                                          @if ($item->status != 2)
                                        <label class="form-check form-switch">
                                            <input class="form-check-input pay_status shadow-none" data-id="{{$item->id}}" type="checkbox" {{$item->payment_status == 1 ? 'checked':''}}>
                                            <small class="form-check-label pay-status-{{$item->id}} badge {{$item->payment_status == 1 ? 'bg-success':'bg-secondary'}}">{{$item->payment_status == 1 ? 'Paid':'Unpaid'}}</small>
                                          </label>
                                          @else
                                            {{__('N/A')}}
                                          @endif
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Publish Status') }}">
                                        <div>
                                          @if (($item->status == 1 || $item->status == 0) && $item->payment_status != 1)
                                          <label class="form-check form-switch">
                                            <input class="form-check-input status shadow-none" type="checkbox" data-id="{{$item->id}}" {{$item->status == 1 ? 'checked':''}}>
                                            <small class="form-check-label status-text-{{$item->id}} badge {{$item->status == 1 ? 'bg-success':'bg-secondary'}}">{{$item->status == 1 ? 'Published':'Un-Published'}}</small>
                                          </label>
                                          @elseif($item->status == 2)
                                            <span class="badge bg-danger">{{__('cancelled')}}</span>
                                            @else
                                            <span class="badge bg-success">{{__('Published')}}</span>
                                          @endif
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Date') }}">
                                        <div>
                                          {{dateFormat($item->created_at,'d M Y')}}
                                        </div>
                                      </td>

                                      <td data-label="{{ __('Action') }}">
                                        <div>
                                          <a href="{{route('user.invoice.view',$item->number)}}" class="btn btn-dark btn-sm" data-bs-toggle="tooltip" data-bs-original-title="{{__('Preview')}}"><i class="fas fa-eye"></i></a>

                                          @if ($item->status != 2 && $item->payment_status != 1)
                                            <a href="{{route('user.invoice.edit',$item->id)}}" class="btn btn-primary btn-sm edit-{{$item->id}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit')}}"><i class="fas fa-edit"></i></a>
                                          @else
                                          <a href="javascript:void(0)" class="btn btn-primary btn-sm disabled" data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit')}}"><i class="fas fa-edit"></i></a>
                                          @endif

                                          <a href="javascript:void(0)" class="btn btn-secondary btn-sm copy" data-clipboard-text="{{route('invoice.view',encrypt($item->number))}}" title="{{__('Copy Invoice URL')}}"><i class="fas fa-copy"></i></a>
                                          <a href="javascript:void(0)" data-route="{{route('user.invoice.send.mail',$item->id)}}" class="btn btn-dark btn-sm send_email" data-bs-toggle="tooltip" data-bs-original-title="@lang('Send Email')"><i class="fas fa-mail-bulk"></i></a>

                                        </div>
                                      </td>
                                  </tr>
                              @endforeach
                              </tbody>
                          </table>
                      </div>
                      {{ $invoices->links() }}
                  @endif
              </div>
          </div>
      </div>
  </div>
</div>


<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Do you want to send to email?')}}</h3>
        </div>
        <div class="modal-footer">
            <div class="w-100">
                <div class="row">
                <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                    {{__('Cancel')}}
                    </a></div>
                <div class="col">
                    <form action="" method="get">
                        <button type="submit" class="btn btn-primary w-100 confirm">
                        {{__('Confirm')}}
                        </button>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

@endsection

@push('js')


   <script src="{{asset('assets/user/js/clipboard.min.js')}}"></script>
    <script>
        'use strict';
        $('.pay_status').on('change',function () {

            var url = "{{route('user.invoice.pay.status')}}"
            var id = $(this).data('id')
            $.post(url,{id:id,_token:'{{csrf_token()}}'},function (res) {
                if(res.paid){
                  //  toast('success',res.paid)
                    $('.pay-status-'+id).addClass('bg-success').text('Paid')
                    return false
                }
                if(res.unpaid){
                   // toast('success',res.unpaid)
                    $('.pay-status-'+id).removeClass('bg-success').addClass('bg-secondary').text('Unpaid')
                    return false
                }
                if(res.error){
                    //toast('error',res.error)
                    return false
                }
            })
        })
        $('.status').on('change',function () {
            var url = "{{route('user.invoice.publish.status')}}"
            var id = $(this).data('id')
            $.post(url,{id:id,_token:'{{csrf_token()}}'},function (res) {
                if(res.unpublish){
                  //  toast('success',res.unpublish)
                    $('.status-text-'+id).removeClass('bg-success').addClass('bg-secondary').text('Un-published')
                    $('.edit-'+id).removeClass('disabled')
                    return false
                }
                if(res.publish){
                  //  toast('success',res.publish)
                    $('.status-text-'+id).addClass('bg-success').text('Published')
                    $('.edit-'+id).addClass('disabled')
                    return false
                }
                if(res.error){
                   // toast('error',res.error)
                    return false
                }
            })
        })
        $('.send_email').on('click',function() {
            $('#modal-success').find('form').attr('action',$(this).data('route'))
            $('#modal-success').modal('show')
        })

        var clipboard = new ClipboardJS('.copy');
        clipboard.on('success', function(e) {
           toast('success','Invoice URL Copied')
        });
    </script>

@endpush
