@extends('layouts.user')

@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Invoice:')}} {{$invoice->number}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{route('user.invoice.index')}}" class="btn btn-primary"><i class="fas fa-backward me-1"></i> {{__(' Back')}}</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
    <div class="card card-lg">
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <p class="h3">{{__('From')}}</p>
            <address>
              {{@$user->address}}<br>
              {{@$user->email}}<br>
              {{@$user->phone}}<br>
            </address>
          </div>
          <div class="col-6 text-end">
            <p class="h3">{{__('To')}}</p>
            <address>
                {{$invoice->address}}<br>
                {{$invoice->email}}
            </address>
          </div>
          <div class="col-12 my-5">
            <h1>{{__('Invoice : ')}} {{$invoice->number}}</h1>
          </div>
        </div>
        <table class="table table-transparent table-responsive">
          <thead>
            <tr>
              <th class="text-center" style="width: 1%">{{__('SL')}}</th>
              <th>{{__('Item')}}</th>
              <th class="text-end" style="width: 1%">{{__('Amount')}}</th>
            </tr>
          </thead>
          @foreach ($invoice->items as $k => $value)
          <tr>
            <td class="text-center">{{++$k}}</td>
            <td>
              <p class="strong mb-1">{{ $value->name}}</p>
            </td>
            <td class="text-end">{{$invoice->currency->symbol}}{{ numFormat($value->amount) }}</td>
          </tr>
          @endforeach
         <tr>

             <td colspan="12" class="text-end fw-bold">{{__('Total : '.$invoice->currency->symbol.numFormat($invoice->final_amount))}}</td>
         </tr>
        </table>
        <p class="text-muted text-center mt-5">{{__('Thank you very much for doing business with us. We look forward to working with
            you again!')}} <br> <small class="mt-5">{{__('All right reserved ')}} <a href="{{url('/')}}">{{$gs->title}}</a></small></p>

      </div>
    </div>
</div>
@endsection
