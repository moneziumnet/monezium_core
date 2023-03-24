<li class="list-group-item d-flex justify-content-between">@lang('Transaction ID')<span>{{$transaction->trnx}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Remark')<span class="badge badge-primary">{{ucwords(str_replace('_',' ',$transaction->remark))}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Currency')<span class="font-weight-bold">{{$transaction->currency->code}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Amount')<span class="badge {{$transaction->type == '+' ? 'badge-success':'badge-danger'}}">{{$transaction->type}}{{amount($transaction->amount,$transaction->currency->type,2)}} {{$transaction->currency->code}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Charge')<span>{{amount($transaction->charge,$transaction->currency->type,2)}} {{$transaction->currency->code}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Date')<span>{{dateFormat($transaction->created_at,'d M y')}}</span></li>
@if (isset($transaction->data))
    @foreach ( json_decode(str_replace(array("\r", "\n"), array('\r', '\n'), $transaction->data)) as $key => $value)
        <li class="list-group-item d-flex justify-content-between">@lang(ucwords(str_replace('_', ' ', $key)))<span>{{($value)}}</span></li>
    @endforeach

@endif
