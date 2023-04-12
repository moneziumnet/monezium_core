<div class="d-flex">
    <div class="me-auto">
        <h1 class="text-uppercase me-auto display-5 font-weight-bold">{{ __($type) }}</h1>
        <h3>{{ $invoice->number }}</h3>
    </div>
    <div class="text-end">
        <h3 class="font-weight-normal">{{ __('Invoice Total') }}</h3>
        <h1>{{ $invoice->currency->symbol . amount($invoice->final_amount, 1, 2) }} {{ $invoice->currency->code }}</h1>
    </div>
</div>
<div class="row my-5">
    <div class="col-4">
        <div class="row">
            <div class="col-12">
                <p class="h2">{{ __('FROM:') }}</p>
            </div>
            <div class="col-12">
                <div class="h3 font-weight-normal">
                    <p class="me-4"><i class="fas fa-map-marker me-2"></i> {{ @$user->address }}</p>
                    <p class="me-4"><i class="fas fa-envelope me-2"></i> {{ @$user->email }}</p>
                    <p class="me-4"><i class="fas fa-phone me-2"></i> {{ @$user->phone }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="row">
            <div class="col-12">
                <p class="h2">{{ __('TO:') }}</p>
            </div>
            <div class="col-12">
                <div class="h3 font-weight-normal">
                    <p class="me-4"><i class="fas fa-map-marker me-2"></i> {{ $invoice->address }}</p>
                    <p class="me-4"><i class="fas fa-envelope me-2"></i> {{ $invoice->email }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        @if($invoice->product_id)
        <div class="row">
            <h2 class="col-4">{{__("Product")}}:</h2> 
            <h3 class="col-8 font-weight-normal">{{$invoice->product->name}}</h3>
        </div>
        @endif
        @if($invoice->contract_id)
        <div class="row">
            <h2 class="col-4">{{__("Contract")}}:</h2>
            <h3 class="col-8 font-weight-normal">
                <a class="text-primary" href="{{route('contract.view',['id' => encrypt($invoice->contract->id), 'role' => encrypt('contractor')])}}" target="_blank">
                    {{ $invoice->contract->title }}
                </a>
            </h3>
        </div>
        @endif
        @if($invoice->contract_aoa_id)
        <div class="row">
            <h2 class="col-4">{{__("Contract AOA")}}:</h2>
            <h3 class="col-8 font-weight-normal">
                <a class="text-primary" href="{{route('aoa.view',['id' => encrypt($invoice->aoa->id), 'role' => encrypt('contractor')])}}" target="_blank">
                    {{ $invoice->aoa->title }}
                </a>
            </h3>
        </div>
        @endif
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
            <td class="text-end">{{ $invoice->currency->symbol }}{{ numFormat($value->amount) }}</td>
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