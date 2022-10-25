<li class="list-group-item d-flex justify-content-between">@lang('WithDraw Method')<span>{{$data->method->name}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('User Name')<span>{{ $data->user->name }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Amount')<span>{{$data->currency->symbol}}{{$data->amount}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Charge')<span>{{$data->currency->symbol}}{{$data->charge}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Description')<span>{{$data->user_data}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Status')
  @if ($data->status == '1')
    <span class="badge bg-success">{{__('Completed')}}</span>
  @elseif($data->status == '0')
    <span class="badge bg-warning">{{__('Pending')}}</span>
  @else
    <span class="badge bg-danger">{{__('Rejected')}}</span>
  @endif
</li>
