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
          {{__('Vouchers')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <a href="{{ route('user.create.voucher') }}" class="btn btn-primary d-sm-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
            {{__('Create Voucher')}}
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
                  @if (count($vouchers) == 0)
                      <h3 class="text-center py-5">{{__('No Voucher Data Found')}}</h3>
                  @else 
                      <div class="table-responsive">
                          <table class="table table-vcenter table-mobile-lg card-table">
                              <thead>
                              <tr>
                                  <th>{{ __('Voucher Code') }}</th>
                                  <th>{{ __('Amount') }}</th>
                                  <th>{{ __('Status') }}</th>
                                  <th>{{ __('Date') }}</th>
                              </tr>
                              </thead>
                              <tbody>
                                @foreach($vouchers as $item)
                                    <tr>
                                        <td data-label="{{ __('Voucher Code') }}">
                                            <div>
                                              {{$item->code}}
                                            </div>
                                        </td>

                                        <td data-label="{{ __('Amount') }}">
                                          <div>
                                            {{numFormat($item->amount)}} {{$item->currency->code}}
                                          </div>
                                        </td>

                                        <td data-label="{{ __('Status') }}">
                                          <div>
                                            @if ($item->status == 0)
                                              <span class="badge bg-secondary">@lang('Unused')</span>
                                            @elseif ($item->status == 1)
                                                <span class="badge bg-success">@lang('Used')</span>
                                            @endif

                                          </div>
                                        </td>
                                        
                                        <td data-label="{{ __('Date') }}">
                                            <div>
                                              {{dateFormat($item->created_at)}}
                                            </div>
                                        </td>

                                        
                                    </tr>
                                @endforeach
                              </tbody>
                          </table>
                      </div>
                      {{ $vouchers->links() }}
                  @endif
              </div>
          </div>
      </div>
  </div>
</div>

@endsection

@push('js')

@endpush