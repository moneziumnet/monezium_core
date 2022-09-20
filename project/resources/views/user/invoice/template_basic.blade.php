<div class="row">
    <div class="col-12">
        <h1>{{ __($type) }} : {{ $invoice->number }}</h1>
    </div>
</div>
<div class="row my-5">
    <div class="col-6">
        @if($invoice->product_id)
            <span class="h3">Product: </span>{{$invoice->product->name}}
        @endif
        @if($invoice->contract_id)
            <div class="mt-3">
                <span class="h3">Contract:</span>
                <a class="text-primary" href="{{route('contract.view',['id' => encrypt($invoice->contract->id), 'role' => encrypt('contractor')])}}" target="_blank">
                    {{ $invoice->contract->title }}
                </a>
            </div>
        @endif
        @if($invoice->contract_aoa_id)
            <div class="mt-3">
                <span class="h3">Contract AOA:</span>
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
    @foreach ($invoice->items as $k => $value)
        <tr>
            <td class="text-center">{{ ++$k }}</td>
            <td><p class="strong mb-1">{{ $value->name }}</p></td>
            <td class="text-end">{{ $invoice->currency->symbol }}{{ numFormat($value->amount) }}</td>
        </tr>
    @endforeach
    <tr>
        <td colspan="12" class="text-end fw-bold">
            {{ __('Total : ' . $invoice->currency->symbol . numFormat($invoice->final_amount)) }}
        </td>
    </tr>
</table>
<p class="text-muted text-center mt-5">
    {{ __('Thank you very much for doing business with us. We look forward to working with you again!') }} <br/>
    <small class="mt-5">{{ __('All right reserved ') }} 
        <a href="{{ url('/') }}">{{ $gs->title }}</a>
    </small>
</p>