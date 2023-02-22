<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="card-header tab-card-header">
            <ul class="nav nav-pills card-header-tabs" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ menu('user.ico') }}" href="{{ route('user.ico') }}" role="button">All Tokens</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ menu('user.ico.mytoken') }}" href="{{ route('user.ico.mytoken') }}"
                        role="button">My Tokens</a>
                </li>
            </ul>
        </div>
        <div class="row align-items-center mt-3">
            <div class="col">
                <div class="page-pretitle">
                    {{ __('Overview') }}
                </div>
                <h2 class="page-title">
                    {{ __('ICO Token List') }}
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-ico-token">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Create ICO Token') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-ico-token" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-status bg-primary"></div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                <h3>{{ __('Add New ICO Token') }}</h3>
                <div class="row text-start">
                    <div class="col">
                        <form action="{{ route('user.ico.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="form-group mt-2 mb-3">
                                    <label class="form-label required">{{ __('Name') }}</label>
                                    <input name="name" id="name" class="form-control shadow-none"
                                        placeholder="{{ __('Name') }}" type="text" pattern="[^()/><\][\\\-;&$@!|]+" value="{{ old('name') }}"
                                        required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Code') }}</label>
                                    <input name="code" id="code" class="form-control shadow-none"
                                        placeholder="{{ __('Code') }}" type="text" pattern="[^()/><\][\\;&$@!|]+" value="{{ old('code') }}" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Symbol') }}</label>
                                    <input name="symbol" id="symbol" class="form-control shadow-none"
                                        placeholder="{{ __('Symbol') }}" type="text" pattern="[^()/><\][\\;&$@!|]+" value="{{ old('symbol') }}" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label">{{ __('Address') }}</label>
                                    <input name="address" id="address" class="form-control shadow-none"
                                        placeholder="{{ __('Address') }}" type="text" pattern="[^()/><\][\\;&$@!|]+" value="{{ old('address') }}" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label required">{{ __('Price') }}</label>
                                    <input name="price" id="price" class="form-control shadow-none"
                                        placeholder="{{ __('Price') }}" type="number" step="any"
                                        value="{{ old('price') }}" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label required">{{ __('Total Supply') }}</label>
                                    <input name="total_supply" id="total_supply" class="form-control shadow-none"
                                        placeholder="{{ __('Total supply') }}" type="number"
                                        value="{{ old('total_supply') }}" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label required">{{ __('End Date') }}</label>
                                    <input name="end_date" id="end_date" class="form-control shadow-none"
                                        placeholder="{{ __('End Date') }}" type="date"
                                        value="{{ old('end_date') }}" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="form-label required">{{ __('White paper') }}</label>
                                    <input name="whitepaper" id="whitepaper" class="form-control" type="file"
                                        accept=".doc,.docx, .pdf" required>
                                </div>

                            </div>
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            <div class="row mt-3">
                                <div class="col">
                                    <button type="submit" class="btn btn-primary w-100 confirm">
                                        {{ __('Confirm') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
