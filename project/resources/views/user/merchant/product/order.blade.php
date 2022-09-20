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
                        {{ __('Overview') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Product Order List') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>


    <div class="page-body">
        <div class="container-xl mt-3 mb-3">
            <div class="row row-cards">
                <div class="row justify-content">
                    @if (count($orders) == 0)
                        <h3 class="text-center py-5">{{ __('No Merchant Product Order Data Found') }}</h3>
                    @else
                        <div class="card table-responsive mb-4 p-3">
                            <table class="table table-vcenter table-mobile-lg card-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Address') }}</th>
                                        <th>{{ __('Quantity') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $key => $val)
                                        @php
                                            $currency = DB::table('currencies')
                                                ->where('id', $val->product->currency_id)
                                                ->first();
                                        @endphp
                                        <tr>
                                            <td>{{ $val->product->name }}</td>
                                            <td>{{ $val->name }}</td>
                                            <td>{{ $val->email }}</td>
                                            <td>{{ $val->phone }}</td>
                                            <td>{{ $val->address }}</td>
                                            <td>{{ $val->quantity }}</td>
                                            <td>{{ $currency->symbol }}{{ $val->product->amount }}</td>
                                            <td>{{ $currency->symbol }}{{ $val->amount }}</td>
                                            <td>{{ $val->type }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $orders->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
