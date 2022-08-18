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
          {{__('Contract')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{ route('user.contract.create') }}" class="btn btn-primary d-none d-sm-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Create Contract')}}
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
                  @if (count($contracts) == 0)
                      <h3 class="text-center py-5">{{__('No Contract Data Found')}}</h3>
                  @else
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-md card-table">
                              <thead>
                                <tr>
                                  <th>{{__('No')}}</th>
                                  <th>{{__('Title')}}</th>
                                  <th>{{__('Description')}}</th>
                                  <th>{{__('Status')}}</th>
                                  <th>{{__('Action')}}</th>
                                </tr>
                              </thead>
                              <tbody>
                                @php
                                    $counter = 1;
                                @endphp
                              @foreach($contracts as $item)

                                  <tr>
                                      <td data-label="{{ __('No') }}">
                                      <div>
                                        {{$counter}}
                                      </div>
                                    </td>
                                      <td data-label="{{ __('Title') }}">
                                        <div>
                                          {{$item->title}}
                                        </div>
                                      </td>
                                      <td data-label="{{ __('Description') }}">
                                        <div>
                                            @if (strlen($item->description) > 20)
                                                {{(substr($item->description, 0, 10)).' ...'}}
                                            @else
                                                {{($item->description)}}
                                            @endif

                                        </div>
                                      </td>

                                      <td data-label="{{ __('Status') }}">
                                        <div>
                                            @if ($item->status == 0 )
                                                <span class="badge bg-warning">Not Signed</span>
                                            @else
                                                <span class="badge bg-success">Signed</span>
                                            @endif
                                        </div>
                                      </td>

                                      {{-- <td data-label="{{ __('Pay Status') }}">
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
                                      </td> --}}

                                      <td data-label="{{ __('Action') }}">
                                        {{-- <div>
                                          <a href="{{route('user.contract.view',$item->number)}}" class="btn btn-dark btn-sm"><i class="fas fa-eye"></i></a>

                                          @if ($item->status == 0)
                                            <a href="{{route('user.contract.edit',$item->id)}}" class="btn btn-primary btn-sm edit-{{$item->id}}"><i class="fas fa-edit"></i></a>
                                          @else
                                          <a href="javascript:void(0)" class="btn btn-primary btn-sm disabled"><i class="fas fa-edit"></i></a>
                                          @endif

                                          <a href="javascript:void(0)" class="btn btn-secondary btn-sm copy" data-clipboard-text="{{route('contract.view',encrypt($item->number))}}" title="{{__('Copy Invoice URL')}}"><i class="fas fa-copy"></i></a>
                                        </div> --}}
                                      </td>
                                  </tr>
                                  @php
                                  $counter++;
                              @endphp
                              @endforeach
                              </tbody>
                          </table>
                      </div>
                  @endif
              </div>
          </div>
      </div>
  </div>
</div>

@endsection

@push('js')


   {{-- <script src="{{asset('assets/user/js/clipboard.min.js')}}"></script>
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

        var clipboard = new ClipboardJS('.copy');
        clipboard.on('success', function(e) {
           toast('success','Invoice URL Copied')
        });
    </script> --}}

@endpush
