<li class="list-group-item d-flex justify-content-between">@lang('Beneficiary Name')<span>{{$item->beneficiary->name}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Beneficiary Email')<span>{{$item->beneficiary->email}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Beneficiary Address')<span>{{$item->beneficiary->address}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Beneficiary Phone')<span>{{$item->beneficiary->phone}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Bank Name')<span>{{$item->beneficiary->bank_name}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Bank Address')<span>{{$item->beneficiary->bank_address}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('SWIFT/BIC')<span>{{$item->beneficiary->swift_bic}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Account/IBAN')<span>{{$item->beneficiary->account_iban}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Amount')<span>{{ $item->currency->symbol }}{{ amount($item->amount, 1, 2) }} {{ $item->currency->code }}</span></li>
<li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Description')<span>{{ $item->description }}</span></li>
@php
    $subbank = App\Models\SubInsBank::whereId($item->subbank)->first();
@endphp
<li class="list-group-item d-flex justify-content-between">@lang('Sender Bank Name')<span>{{$subbank->name}}</span></li>
