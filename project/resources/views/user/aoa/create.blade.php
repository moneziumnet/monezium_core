@extends('layouts.user')

@push('css')

<link rel="stylesheet" type="text/css" href="http://keith-wood.name/css/jquery.signature.css">

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
            {{__('Create AoA')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

              <a href="{{ route('user.contract.aoa', $id) }}" class="btn btn-primary d-none d-sm-inline-block">
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
                    <form id="aoa-form" action="{{ route('user.contract.aoa.store', $id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Title')}}</label>
                            <input name="title" id="title" class="form-control" autocomplete="off" placeholder="{{__('Enter Title')}}" type="text" required>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('amount')}}</label>
                            <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('0.0')}}" type="number" step="any" min="1" required>
                        </div>

                        <div class="form-group mb-3 mt-3">
                            <label class="form-label required">{{__('Description')}}</label>
                            <textarea name="description" class="form-control" id="inp-details" cols="30" rows="10" placeholder="{{__('Description')}}" required></textarea>
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
                        <input name="contract_id" type="hidden" class="form-control" value="{{$id}}">

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Submit')}}</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://keith-wood.name/js/jquery.signature.js"></script>
<script>
  'use strict';
  var sig = $('#sig').signature({syncField: '#signature64', syncFormat: 'PNG'});
            $('#clear').click(function(e) {
                e.preventDefault();
                sig.signature('clear');
                $("#signature64").val('');
            });
</script>
@endpush
