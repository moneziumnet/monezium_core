<div class="modal-status bg-primary"></div>
<div class="modal-body text-center py-4">
    <i class="fas fa-info-circle fa-3x text-primary mb-2"></i>
    <h3>@lang('Beneficiary Details')</h3>
    <ul class="list-group details-list mt-2">
        <li class="list-group-item">@lang('Beneficiary Name')<span>{{ $beneficiary->name }}</span></li>
        <li class="list-group-item">@lang('Beneficiary Email')<span>{{ $beneficiary->email }}</span></li>
        <li class="list-group-item">@lang('Beneficiary Address')<span>{{ $beneficiary->address }}</span></li>
        <li class="list-group-item">@lang('Beneficiary Phone')<span>{{ $beneficiary->phone }}</span></li>
        <li class="list-group-item">@lang('Beneficiary Registration NO')<span>{{ $beneficiary->registration_no }}</span></li>
        <li class="list-group-item">@lang('Beneficiary VAT NO')<span>{{ $beneficiary->vat_no }}</span></li>
        <li class="list-group-item">@lang('Beneficiary Contact')<span>{{ $beneficiary->contact_person }}</span></li>
        <li class="list-group-item">@lang('Bank Name')<span>{{ $beneficiary->bank_name }}</span></li>
        <li class="list-group-item">@lang('Bank Address')<span>{{ $beneficiary->bank_address }}</span></li>
        <li class="list-group-item">@lang('Account IBAN')<span>{{ $beneficiary->account_iban }}</span></li>
        <li class="list-group-item">@lang('SWIFT/BIC')<span>{{ $beneficiary->swift_bic }}</span></li>
        <li class="list-group-item">@lang('Type')<span>{{ $beneficiary->type}}</span></li>
    </ul>
</div>
<div class="modal-footer">
    <div class="w-100">
        <div class="row">
            <div class="col">
                <button class="btn w-100 closed" data-bs-dismiss="modal">
                    @lang('Close')
                </button>
            </div>
        </div>
    </div>
</div>
