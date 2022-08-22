@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
    @include('user.ex_payment_tab')
      <div class="row align-items-center mt-3">
        <div class="col">
          <h2 class="page-title">
            {{__('Beneficiary Details')}}
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
                  <div class="table-responsive-sm">
                      <table class="table mb-0">
                          <tbody>
                            <tr>
                              <th class="45%" width="45%">@lang('Bank Name')</th>
                              <td width="10%">:</td>
                              <td class="45%" width="45%">{{ $data->bank->title }}</td>
                            </tr>
                            <tr>
                            <th class="45%" width="45%">@lang('Beneficiary Name')</th>
                            <td width="10%">:</td>
                            <td class="45%" width="45%">{{ $data->account_name }}</td>
                          </tr>
                            <tr>
                              <th class="45%" width="45%">{{__('Beneficiary Address')}}</th>
                              <td width="10%">:</td>
                              <td class="45%" width="45%">{{ $data->address }}</td>
                            </tr>
                            <tr>
                              <th class="45%" width="45%">{{__('Bank Address')}}</th>
                              <td width="10%">:</td>
                              <td class="45%" width="45%">{{ $data->bank_address }}</td>
                            </tr>
                            <tr>
                              <th class="45%" width="45%">{{__('SWIFT/BIC')}}</th>
                              <td width="10%">:</td>
                              <td class="45%" width="45%">{{ $data->swift_bic }}</td>
                            </tr>
                            <tr>
                              <th class="45%" width="45%">{{__('Account/IBAN')}}</th>
                              <td width="10%">:</td>
                              <td class="45%" width="45%">{{ $data->account_iban }}</td>
                            </tr>

                            @foreach (json_decode($data->details,true) as $key=>$value)
                              @if ($value[1] == 'file')
                              <tr>
                                  <th width="45%">{{$key}}</th>
                                  <td width="10%">:</td>
                                  <td width="45%"><a href="{{asset('assets/images/'.$value[0])}}" download=""><img src="{{asset('assets/images/'.$value[0])}}" class="img-thumbnail"></a></td>
                              </tr>
                              @else
                                  <tr>
                                      <th width="45%">{{$key}}</th>
                                      <td width="10%">:</td>
                                      <td width="45%">{{ $value[0] }}</td>
                                  </tr>
                              @endif
                          @endforeach

                          </tbody>
                      </table>
                  </div>
              </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')

@endpush

