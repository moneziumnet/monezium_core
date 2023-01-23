@extends('layouts.user')

@push('css')

@endpush



@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Campaign Pay')}}
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">

            <a class="btn btn-primary d-sm-inline-block" href="{{route('user.campaign.donate', $campaign->id)}}">
              <i class="fas fa-backward me-1"></i> {{__('Back')}}
            </a>
      </div>
    </div>
  </div>
</div>


            <div class="page-body">
            <div class="container-xl">
                <div class="row row-cards">
                    <div class="col-12">
                        <div class="card p-5">
                                @includeIf('includes.flash')
                                <form action="{{route('user.merchant.campaign.crypto.pay', $campaign->id)}}" method="GET" id="pay_form" enctype="multipart/form-data">
                                    <div class="text-center">
                                        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
                                        <div class="col">
                                            <h2>{{$campaign->title}}
                                            </h2>
                                            <div class="page-pretitle">
                                            {{$campaign->description}}
                                            </div>
                                        </div>
                                        <h3></h3>
                                    </div>
                                    <div class="form-group mb-3 mt-3">
                                        <label class="form-label required">{{__('Amount')}}({{$campaign->currency->code}})</label>
                                        @if ($campaign->amount == 0)
                                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="number" step="any" value="{{ $campaign->amount }}" required>
                                        @else
                                        <input name="amount" id="amount" class="form-control" autocomplete="off"  type="text" value="{{ $campaign->amount }}" readonly required>
                                        @endif
                                    </div>
                                    <input type="hidden" name="campaign_id" value="{{$campaign->id}}">
                                    <div class="form-footer">
                                        @foreach($cryptolist as $currency)
                                        <button type="submit" id="submit" name="link_pay_submit" value="{{$currency->id}}" class="btn btn-primary w-100">{{$currency->curr_name}} - {{$currency->code}}</button>
                                        <h3></h3>
                                    @endforeach
                                    </div>
                                </form>
                        </div>
                    </div>
                </div>
            </div>
            </div>


@endsection
