<li class="list-group-item d-flex justify-content-between">@lang('WithDraw Method')<span>{{$data->method->name}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('User Name')<span>{{ $data->user->name }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Amount')<span>{{ showprice($data->amount,$data->currency) }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Charge')<span>{{ showprice($data->charge,$data->currency) }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Status')
  @if ($data->status == '1')
    <span class="badge bg-success">{{__('Completed')}}</span>
  @elseif($data->status == '0')
    <span class="badge bg-warning">{{__('Pending')}}</span>
  @else
    <span class="badge bg-danger">{{__('Rejected')}}</span>
  @endif
</li>