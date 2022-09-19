<li class="list-group-item d-flex justify-content-between">@lang('Name')<span>{{$item->name}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Price')<span>{{ amount($item->price, 1, 2) }} USD</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Code')<span class="badge badge-primary">{{ $item->currency->code }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Symbol')<span>{{ $item->currency->symbol }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Total Supply')<span>{{ $item->total_supply }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Balance')<span>{{ $item->balance }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('End Date')<span>{{ dateFormat($item->end_date) }}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('White Paper')
    <a href ="{{asset('assets/doc/'.$item->white_paper)}}" class="btn btn-primary btn-sm" attributes-list download >{{ __('Download White Paper')}} </a>
</li>