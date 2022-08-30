@extends('layouts.user')

@push('css')
<link rel="stylesheet" type="text/css" href="{{asset('assets/user/')}}/css/jquery.signature.css">

<style>
    .kbw-signature { width: 100%; height: 200px;}
    #sig canvas{
        width: 100% !important;
        height: auto;
    }
</style>
@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Edit Aoa')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

              <a href="{{ route('user.contract.aoa', $data->contract_id) }}" class="btn btn-primary d-sm-inline-block">
                  <i class="fas fa-backward me-1"></i> {{__('AoA List')}}
              </a>
            </div>
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
                    <form id="contract-form" action="{{ route('user.contract.aoa.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Title')}}</label>
                            <input name="title" id="title" class="form-control" autocomplete="off" placeholder="{{__('Enter Title')}}" type="text" value="{{$data->title}}" required>
                        </div>

                        @foreach (json_decode($data->pattern, True) as $key => $value)

                        <div class="row form-group mb-3 mt-3">
                            <div class="col-md-6 mb-3">
                                @if ($loop->first)
                                <div class="form-label">{{__('Pattern name')}}</div>
                                @endif
                                <input type="text" name="item[]" class="form-control shadow-none itemname" value="{{$key}}" >
                            </div>
                            <div class="col-md-5 mb-3">
                                @if ($loop->first)
                                <div class="form-label">{{__('Value')}}</div>
                                @endif
                                <input type="text" name="value[]" class="form-control shadow-none itemvalue" value="{{$value}}" >
                            </div>
                            @if ($loop->first)
                                <div class="col-md-1 mb-3">
                                    <div class="form-label">&nbsp;</div>
                                    <button type="button" class="btn btn-primary w-100 add"><i class="fas fa-plus"></i></button>
                                </div>
                            @else
                                <div class="col-md-1 mb-3">
                                    <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    <div class="extra-container"></div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Description')}} {{__('(i.e: if patten is amount, and value is 500,  {amount} is 500)')}}</label>
                            <textarea name="description" class="form-control" id="inp-details" cols="30" rows="10" placeholder="{{__('Description')}}"  required>{{__($data->description)}}</textarea>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <div class="col-md-12">
                                <label class="" for="">{{__('Signature:')}}</label>
                                <br/>
                                <div id="sig" ></div>
                                <br/>
                                <div  id="clear" class="btn btn-primary btn-sm mt-2">{{__('Clear Signature')}}</div>
                                <textarea id="signature64" name="signed" style="display: none" required></textarea>
                            </div>
                        </div>
                        <input name="contract_id" type="hidden" class="form-control" value="{{$data->contract_id}}">

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Update')}}</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery.signature.js"></script>
<script>
  'use strict';
  var sig = $('#sig').signature({syncField: '#signature64', syncFormat: 'PNG'});
            $('#clear').click(function(e) {
                e.preventDefault();
                sig.signature('clear');
                $("#signature64").val('');
            });
    $('.add').on('click',function(){
            $('.extra-container').append(`

                   <div class="row form-group mb-3 mt-3">
                        <div class="col-md-6 mb-3">
                            <input type="text" name="item[]" class="form-control shadow-none itemname" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <input type="text" name="value[]" class="form-control shadow-none itemvalue" required>
                        </div>
                        <div class="col-md-1 mb-3">
                            <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                        </div>
                    </div>

            `);
        })

        $(document).on('click','.remove',function () {
            $(this).closest('.row').remove()
        })
</script>
@endpush
