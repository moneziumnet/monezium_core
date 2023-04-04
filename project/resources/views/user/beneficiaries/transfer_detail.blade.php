<li class="list-group-item d-flex justify-content-between">@lang('Beneficiary Name')<span class="list-item-content">{{$item->beneficiary->name}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Beneficiary Email')<span class="list-item-content">{{$item->beneficiary->email}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Beneficiary Address')<span class="list-item-content">{{$item->beneficiary->address}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Beneficiary Phone')<span class="list-item-content">{{$item->beneficiary->phone}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Bank Name')<span class="list-item-content">{{$item->beneficiary->bank_name}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Bank Address')<span class="list-item-content">{{$item->beneficiary->bank_address}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('SWIFT/BIC')<span class="list-item-content">{{$item->beneficiary->swift_bic}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Account/IBAN')<span class="list-item-content">{{$item->beneficiary->account_iban}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Amount')<span class="list-item-content">{{ $item->currency->symbol }}{{ amount($item->final_amount, 1, 2) }} {{ $item->currency->code }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Charge')<span class="list-item-content">{{ $item->currency->symbol }}{{ amount($item->cost, 1, 2) }} {{ $item->currency->code }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Full Amount')<span class="list-item-content">{{ $item->currency->symbol }}{{ amount($item->amount, 1, 2) }} {{ $item->currency->code }}</span></li>
<li class="list-group-item d-flex justify-content-between" style="word-break:break-all;">@lang('Description')<span class="list-item-content">{{ $item->description }}</span></li>
@php
    $subbank = App\Models\SubInsBank::whereId($item->subbank)->first();
@endphp
<li class="list-group-item d-flex justify-content-between">@lang('Sender Bank Name')<span class="list-item-content">{{$subbank->name}}</span></li>
<li hidden="hidden">@lang('Status')<span id="beneficiary_status">{{ $item->status }}</span></li>
