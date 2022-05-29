@extends('layouts.user')

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <div class="page-pretitle">
            {{__('Overview')}}
          </div>
          <h2 class="page-title">
            {{__('Pending escrows')}}
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
                    @if (count($escrows) == 0)
                        <h3 class="text-center py-5">{{__('No Pending Escrow Data Found')}}</h3>
                    @else 
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                    <tr>
                                        <th>{{__('Invitor')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Charge')}}</th>
                                        <th>{{__('Charge Bearer')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Date')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  @foreach($escrows as $item)
                                      <tr>
                                          <td data-label="{{ __('Invitor') }}">
                                              <div>
                                                {{$item->user->email}}
                                              </div>
                                          </td>

                                          <td data-label="{{ __('Amount') }}">
                                            <div>
                                                {{numFormat($item->amount)}} {{$item->currency->code}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Charge') }}">
                                            <div>
                                                {{numFormat($item->charge)}} {{$item->currency->code}}
                                            </div>
                                          </td>
                                          
                                          <td data-label="{{ __('Charge Bearer') }}">
                                              <div>
                                                @if ($item->pay_charge == 1)
                                                {{ $item->user->email}}
                                                @else
                                                {{ $item->recipient->email}}
                                                @endif
                                              </div>
                                          </td>

                                         
                                          <td data-label="{{ __('Status') }}">
                                            <div>
                                                @if ($item->status == 1)
                                                    <span class="badge bg-success">@lang('Released')</span>
                                                @elseif ($item->status == 0)
                                                    <span class="badge bg-warning">@lang('On hold')</span>
                                                @elseif ($item->status == 3)
                                                    <span class="badge bg-info">@lang('Disputed')</span>
                                                @elseif ($item->status == 4)
                                                    <span class="badge">@lang('Closed')</span>
                                                @endif
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Date') }}"> 
                                            <div>
                                                {{dateFormat($item->created_at)}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Action') }}">
                                            <div class="btn-list flex-nowrap">
                                                @if ($item->status != 1 && $item->status != 4)
                                                <a href="{{route('user.escrow.dispute',$item->id)}}" class="btn btn-warning btn-sm">@lang('Dispute')</a>
                                                @else
                                                @lang('N/A')
                                                @endif
                                            </div>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $escrows->links() }}
                    @endif
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
                <h3>{{__('Confirm Release?')}}</h3>
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
</div>

@endsection

@push('js')
    <script>
        'use strict';
        $('.release').on('click',function() { 
            $('#modal-success').find('form').attr('action',$(this).data('route'))
            $('#modal-success').modal('show')
        })
    </script>
@endpush