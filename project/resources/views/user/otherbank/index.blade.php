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
            {{__('External Payments')}}
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
                    @if (count($beneficiaries) == 0)
                        <h3 class="text-center py-5">{{__('No Beneficiary Data Found')}}</h3>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-mobile-md card-table">
                                <thead>
                                <tr>
                                    <th>{{ __('Bank') }}</th>
                                    <th>{{ __('Beneficiary Name') }}</th>
                                    <th>{{ __('Options') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                  @foreach($beneficiaries as $key=>$data)
                                      <tr>
                                          <td data-label="{{ __('Bank') }}">
                                            <div>
                                              {{ $data->bank->title}}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Beneficiary Name') }}">
                                            <div>
                                              {{$data->account_name}}
                                            </div>
                                          </td>
                                          <td data-label="{{ __('Options') }}">
                                            <div class="btn-list">
                                                <a href="{{route('user.other.send',$data->id)}}" class="btn btn-primary">
                                                  {{__('Send')}}
                                                </a>
                                            </div>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $beneficiaries->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')

@endpush

