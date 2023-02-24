<li class="list-group-item d-flex justify-content-between">@lang('Institution Fee')<span>{{amount($transaction->charge,$transaction->currency->type,2)}} {{$transaction->currency->code}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Bank Fee')<span>{{amount($webhook_request->charge,$transaction->currency->type,2)}} {{$transaction->currency->code}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Profit')<span >{{amount(($transaction->charge - $webhook_request->charge),$transaction->currency->type,2)}} {{$transaction->currency->code}}</span></li>

