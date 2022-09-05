@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    @include('user.invoicetab')
    <div class="row align-items-center mt-3">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Incoming Invoices')}}
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
                  @if (count($invoices) == 0)
                      <h3 class="text-center py-5">{{__('No Invoice Data Found')}}</h3>
                  @else
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-md card-table">
                              <thead>
                                <tr>
                                  <th>{{__('Invoice')}}</th>
                                  <th>{{__('Beneficiary')}}</th>
                                  <th>{{__('Amount')}}</th>
                                  <th>{{__('Pay Status')}}</th>
                                  <th>{{__('Description')}}</th>
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
                                      <td data-label="{{ __('Beneficiary') }}">
                                        <div>
                                          {{$item->beneficiary->name}}
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
                                        <label class="">
                                            <small class="form-check-label pay-status-{{$item->id}} badge {{$item->payment_status == 1 ? 'bg-success':'bg-secondary'}}">{{$item->payment_status == 1 ? 'Paid':'Unpaid'}}</small>
                                          </label>
                                          @else
                                            {{__('N/A')}}
                                          @endif
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Description') }}">
                                        <div>
                                          {{str_dis($item->description)}}
                                        </div>
                                      </td>



                                      <td data-label="{{ __('Action') }}">
                                        <div>
                                          <a href="{{route('user.invoice.incoming.view',$item->number)}}" class="btn btn-dark btn-sm" data-bs-toggle="tooltip" data-bs-original-title="{{__('Preview')}}"><i class="fas fa-eye"></i></a>

                                          @if ($item->status != 2 && $item->payment_status != 1)
                                            <a href="{{route('user.invoice.incoming.edit',$item->id)}}" class="btn btn-primary btn-sm edit-{{$item->id}}" data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit')}}"><i class="fas fa-edit"></i></a>
                                          @else
                                          <a href="javascript:void(0)" class="btn btn-primary btn-sm disabled" data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit')}}"><i class="fas fa-edit"></i></a>
                                          @endif



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



@endsection

@push('js')


   <script src="{{asset('assets/user/js/clipboard.min.js')}}"></script>
    <script>
        'use strict';

        var clipboard = new ClipboardJS('.copy');
        clipboard.on('success', function(e) {
           toast('success','Invoice URL Copied')
        });
    </script>

@endpush
