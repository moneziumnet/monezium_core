@extends('layouts.user')

@section('contents')
@php
    $type = $invoice->type ? $invoice->type : 'Invoice';
@endphp
<div class="container-xl">
  <div class="page-header d-print-none">
    @include('user.invoicetab')
    <div class="row align-items-center mt-3">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
            {{__($type)}} : {{$invoice->number}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <a href="{{route('user.invoice.incoming.index')}}" class="btn btn-primary"><i class="fas fa-backward me-1"></i> {{__(' Back')}}</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
    <div class="card card-lg">
      <div class="card-body">
        @if ($invoice->template == 0)
          @include('user.invoice.template_basic')
        @elseif ($invoice->template == 1)
          @include('user.invoice.template_classic')
        @else
          @include('user.invoice.template_pro')
        @endif
      </div>
    </div>
</div>
@endsection
