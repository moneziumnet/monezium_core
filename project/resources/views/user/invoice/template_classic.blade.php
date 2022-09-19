<div>
    <h2 class="text-end">NO: {{ $invoice->number }}</h2>
    <h1 class="text-uppercase me-auto display-6 font-weight-bold">{{ __($type) }}</h1>
</div>
<div class="row mt-5">
    <div class="col-3">
        <p class="h3">{{ __('From:') }}</p>
    </div>
    <div class="col-9">
        <address class="h3 font-weight-normal">
            {{ @$user->address }}<br>
            {{ @$user->email }}<br>
            {{ @$user->phone }}<br>
        </address>
    </div>
</div>
<div class="row mt-3">
    <div class="col-3">
        <p class="h3">{{ __('Bill To:') }}</p>
    </div>
    <div class="col-9">
        <address class="h3 font-weight-normal">
            {{ $invoice->address }}<br/>
            {{ $invoice->email }}
        </address>
    </div>
</div>
<div class="row mt-3 mb-5">
    @if($invoice->product)
    <div class="row">
        <h3 class="col-3">Product:</h3> 
        <h3 class="col-9 font-weight-normal">{{$invoice->product->name}}</h3>
    </div>
    @endif
    @if($invoice->contract)
    <div class="row">
        <h3 class="col-3">Contract:</h3>
        <h3 class="col-9 font-weight-normal">
            <a class="text-primary" href="{{route('contract.view',['id' => encrypt($invoice->contract->id), 'role' => encrypt('contractor')])}}" target="_blank">
                {{ $invoice->contract->title }}
            </a>
        </h3>
    </div>
    @endif
    @if($invoice->aoa)
    <div class="row">
        <h3 class="col-3">Contract AOA:</h3>
        <h3 class="col-9 font-weight-normal">
            <a class="text-primary" href="{{route('aoa.view',['id' => encrypt($invoice->aoa->id), 'role' => encrypt('contractor')])}}" target="_blank">
                {{ $invoice->aoa->title }}
            </a>
        </h3>
    </div>
    @endif
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