<div class="row">
    <div class="col-12">
        <h1>{{ __($type) }} : {{ $invoice->number }}</h1>
    </div>
</div>
<div class="row my-5">
    <div class="col-6">
        @if($invoice->product_id)
            <span class="h3">{{__("Product")}}: </span>{{$invoice->product->name}}
        @endif
        @if($invoice->contract_id)
            <div class="mt-3">
                <span class="h3">{{__("Contract")}}:</span>
                <a class="text-primary" href="{{route('contract.view',['id' => encrypt($invoice->contract->id), 'role' => encrypt('contractor')])}}" target="_blank">
                    {{ $invoice->contract->title }}
                </a>
            </div>
        @endif
        @if($invoice->contract_aoa_id)
            <div class="mt-3">
                <span class="h3">{{__("Contract AOA")}}:</span>
                <a class="text-primary" href="{{route('aoa.view',['id' => encrypt($invoice->aoa->id), 'role' => encrypt('contractor')])}}" target="_blank">
                    {{ $invoice->aoa->title }}
                </a>
            </div>
        @endif
    </div>
    <div class="col-6 text-end">
        <p class="h3">{{ __('From') }}</p>
        <address>
            {{ @$user->address }}<br>
            {{ @$user->email }}<br>
            {{ @$user->phone }}<br>
        </address>
        <p class="h3">{{ __('To') }}</p>
        <address>
            {{ $invoice->address }}<br>
            {{ $invoice->email }}
        </address>
    </div>
</div>
<table class="table table-transparent table-responsive">
    <thead>
        <tr>
            <th class="text-center" style="width: 1%">{{ __('SL') }}</th>
            <th>{{ __('Item') }}</th>
            <th class="text-end" style="width: 1%">{{ __('Amount') }}</th>
        </tr>
    </thead>
    @php
        $total = $invoice->final_amount;
        $arr_tax = array();
    @endphp
    @foreach ($invoice->items as $k => $value)
        <tr>
            <td class="text-center">{{ ++$k }}</td>
            <td><p class="strong mb-1">{{ $value->name }}</p></td>
            <td class="text-end">{{ $invoice->currency->symbol }}{{ amount($value->amount,1,2) }}</td>
        </tr>
        @php
            $item = DB::table('taxes')->where('id', $value->tax_id)->first();
            if($item){
                array_push($arr_tax, array(
                    "name"=> $item->name,
                    "rate"=> $item->rate,
                    "price"=> $item->rate * $value->amount / 100
                ));
                $total += $item->rate * $value->amount / 100;
            }
        @endphp
    @endforeach
    <tr>
        <td colspan="12" class="text-end fw-bold">
            <div class="row">
                <div class="col-4 offset-6 text-end">
                    {{ __('Subtotal : ') }}
                </div>
                <div class="col-2">
                    {{ $invoice->currency->symbol . amount($invoice->final_amount, 1, 2) }}
                </div>
            </div>
        </td>
    </tr>
    @foreach ($arr_tax as $tax_item)
    <tr>
        <td colspan="12" class="text-end fw-bold">
            <div class="row">
                <div class="col-4 offset-6 text-end">
                    {{ $tax_item['name'].' '.$tax_item['rate'].'%' }}
                </div>
                <div class="col-2">
                    {{ $invoice->currency->symbol . amount($tax_item['price'], 1, 2) }}
                </div>
            </div>
        </td>
    </tr>
    @endforeach
    <tr>
        <td colspan="12" class="text-end fw-bold">
            <div class="row">
                <h3 class="col-4 offset-6 text-end">
                    {{ __('Total') }}
                </h3>
                <h3 class="col-2">
                    {{ $invoice->currency->symbol . amount($total, 1, 2) }}
                </h3>
            </div>
        </td>
    </tr>
</table>
<p class="text-muted text-center mt-5">
    {{ __('Thank you very much for doing business with us. We look forward to working with you again!') }} <br/>
    <small class="mt-5">{{ __('All right reserved ') }} 
        <a href="{{ url('/') }}">{{ $gs->title }}</a>
    </small>
</p>