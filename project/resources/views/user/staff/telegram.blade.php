@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Telegram Setting')}}
          </h2>
        </div>
       </div>
    </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
            <div class="card-body">
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
        </div>
    </div>
  </div>
</div>

@endsection
