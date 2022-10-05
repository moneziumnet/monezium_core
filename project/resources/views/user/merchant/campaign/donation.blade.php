@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    @include('user.merchant.tab')
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Donation List')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <a href="{{ route('user.merchant.campaign.index') }}" class="btn btn-primary d-sm-inline-block">
                <i class="fas fa-backward me-1"></i> {{__('Back')}}
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
                    @if (count($donations) == 0)
                        <h3 class="text-center py-5">{{__('No Donations Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('No') }}</th>
                                    <th>{{ __('Campaipn') }}</th>
                                    <th>{{ __('Donator') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Payment') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($donations as $item)
                                      <tr>
                                        <td data-label="{{ __('No') }}">
                                            <div>
                                                {{ $loop->iteration}}
                                            </div>
                                        </td>

                                          <td data-label="{{ __('Campaign') }}">
                                              <div>
                                                {{$item->campaign->title}}
                                              </div>
                                          </td>

                                          <td data-label="{{ __('Donator') }}">
                                            <div>
                                              {{$item->user_name}}
                                            </div>
                                        </td>

                                          <td data-label="{{ __('Amount') }}">
                                            <div>
                                              {{numFormat($item->amount)}} {{$item->currency->code}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Payment') }}">
                                            <div>
                                              {{$item->payment}}
                                            </div>
                                          </td>

                                          <td data-label="{{ __('Date') }}">
                                            <div>
                                                {{dateFormat($item->created_at)}}
                                            </div>
                                        </td>
                                        <td data-label="{{ __('Status') }}">
                                          <div>
                                            @if ($item->status == 0)
                                              <span class="badge bg-secondary">@lang('Pending')</span>
                                            @elseif ($item->status == 1)
                                                <span class="badge bg-success">@lang('Approved')</span>
                                            @endif

                                          </div>
                                        </td>


                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $donations->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
  </div>

@endsection

