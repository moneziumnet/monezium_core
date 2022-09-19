@extends('layouts.user')

@push('css')
@endpush

@section('contents')
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        {{ __('Edit ICO Token') }}
                    </h2>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">

                        <a href="{{ route('user.ico.mytoken') }}" class="btn btn-primary d-sm-inline-block">
                            <i class="fas fa-backward me-1"></i> {{ __('ICO Token List') }}
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
                    <div class="card p-5">
                        <form id="shop-form" action="{{ route('user.ico.update', $item->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="form-group mt-2 mb-3">
                                <label class="form-label required">{{ __('Name') }}</label>
                                <input name="name" id="name" class="form-control shadow-none"
                                    placeholder="{{ __('Name') }}" type="text" value="{{ $item->name }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Code') }}</label>
                                <input name="code" id="code" class="form-control shadow-none"
                                    placeholder="{{ __('Code') }}" type="text" value="{{ $item->currency->code }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('Symbol') }}</label>
                                <input name="symbol" id="symbol" class="form-control shadow-none"
                                    placeholder="{{ __('Symbol') }}" type="text"
                                    value="{{ $item->currency->symbol }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{ __('Price') }}</label>
                                <input name="price" id="price" class="form-control shadow-none"
                                    placeholder="{{ __('Price') }}" type="number" step="any"
                                    value="{{ $item->price }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{ __('Total Supply') }}</label>
                                <input name="total_supply" id="total_supply" class="form-control shadow-none"
                                    placeholder="{{ __('Total supply') }}" type="number"
                                    value="{{ $item->total_supply }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{ __('End Date') }}</label>
                                <input name="end_date" id="end_date" class="form-control shadow-none"
                                    placeholder="{{ __('End Date') }}" type="date" value="{{ $item->end_date }}"
                                    required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label required">{{ __('White paper') }}</label>
                                <input name="whitepaper" id="whitepaper" class="form-control" type="file"
                                    accept=".doc,.docx">
                            </div>

                            <div class="form-footer">
                                <button type="submit"
                                    class="btn btn-primary submit-btn w-100">{{ __('Update') }}</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
@endpush
