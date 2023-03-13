@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Pincode Setting')}}
          </h2>
        </div>
       </div>
    </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row row-deck row-cards">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>@lang('Please click this to join our Telegram bot : ')</label>
                            <a href='https://t.me/moneziumbot' class="" alt="" target="_blank">{{__('Click here me')}}</a>
                        </div>
                    </div>
                    <form action="{{route('user.telegram.pin.generate')}}" id="form" method="post" enctype="multipart/form-data">
                    @csrf
                        <div class="row ">
                            <div class="col-md-6">
                                <div class="form-label ">@lang('Telegram Pin Code : ')</div>
                                <input type="text" name="code" id="code" class="form-control shadow-none mb-2" value="{{$telegram->pincode ?? ''}}" readonly>
                            </div>

                            <div class="col-md-4">
                                <div class="form-label">&nbsp;</div>
                                <button type="submit" class="btn btn-primary w-100 create">
                                    @lang('Generate')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card mt-1">
                <div class="card-body">
                    <div class="row ">
                        <div class="col-md-6">
                            <label>@lang('Please scan QR code to join our Whatsapp bot : ')</label>
                            <img src="{{generateQR('https://wa.me/'.$gs->whatsapp_bot_number.'?text=Join%20cleft%20poach')}}" class="" alt="">
                            <a href='https://web.whatsapp.com/send?phone={{$gs->whatsapp_bot_number}}&text=Join%20cleft%20poach' class="" alt="" target="_blank">{{__(' Or Click here me')}}</a>
                        </div>
                    </div>
                    <form action="{{route('user.whatsapp.pin.generate')}}" id="form" method="post" enctype="multipart/form-data">
                    @csrf
                        <div class="row ">
                            <div class="col-md-6">
                                <div class="form-label ">@lang('Whatsapp Pin Code : ')</div>
                                <input type="text" name="code" id="code" class="form-control shadow-none mb-2" value="{{$whatsapp->pincode ?? ''}}" readonly>
                            </div>

                            <div class="col-md-4">
                                <div class="form-label">&nbsp;</div>
                                <button type="submit" class="btn btn-primary w-100 create">
                                    @lang('Generate')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>

@endsection
