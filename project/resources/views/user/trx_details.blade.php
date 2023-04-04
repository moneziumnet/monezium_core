
<li class="list-group-item d-flex justify-content-between" style="word-break: break-all;">@lang('Transaction ID')
    <span class="list-item-content" style="margin-left: 60px" id="trnx_id">{{$transaction->trnx}}</span>
</li>
<li class="list-group-item d-flex justify-content-between">@lang('Remark')
    <span class="list-item-content" id="trnx_remark">{{ucwords(str_replace('_',' ',$transaction->remark))}}</span>
</li>
<li class="list-group-item d-flex justify-content-between">@lang('Currency')
    <span class="list-item-content" id="trnx_currency">{{$transaction->currency->code}}</span>
</li>
<li class="list-group-item d-flex justify-content-between">@lang('Amount')
    <span class="list-item-content" id="trnx_amount">{{$transaction->type}}{{amount($transaction->amount,$transaction->currency->type,2)}} {{$transaction->currency->code}}</span>
</li>
<li class="list-group-item d-flex justify-content-between">@lang('Charge')
    <span class="list-item-content" id="trnx_charge">{{amount($transaction->charge,$transaction->currency->type,2)}} {{$transaction->currency->code}}</span>
</li>
<li class="list-group-item d-flex justify-content-between">@lang('Date')
    <span class="list-item-content" id="trnx_date">{{dateFormat($transaction->created_at,'d M y')}}</span>
</li>
@if (isset($transaction->data))
    @foreach ( json_decode($transaction->data) as $key => $value)
        <li class="list-group-item d-flex justify-content-between align-items-center">@lang(ucwords($key))
            <div style="width: 50%; word-break:break-all;" class="text-end list-item-content" id=@lang(ucwords($key))>{{($value)}}</div>
        </li>
    @endforeach
@endif
