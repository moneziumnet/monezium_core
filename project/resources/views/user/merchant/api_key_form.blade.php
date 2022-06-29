@extends('layouts.user')

@push('css')
    
@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('QR CODE')}}
          </h2>
        </div>
      </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        
        <div class="row row-cards mt-2">
            <div class="col-xl-12">
              
                <div class="card">
                    <div class="card-header">
                        <h6>@lang('Business Api Key')</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <div class="col-md-3">
                                <h6 class="mt-2">@lang('Access Key :')</h6>
                            </div>
                            <div class="col-md-9">
                                <input class="form-control public" data-clipboard-text="{{@$cred->access_key}}" type="text" value="{{@$cred->access_key}}" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-3">
                                <h6 class="mt-2">@lang('Service Mode :')</h6>
                            </div>
                            <div class="col-md-9">
                                <select class="form-control mode">
                                    <option value="0" {{$cred->mode == 0 ? 'selected':''}}>@lang('Test Mode')</option>
                                    <option value="1" {{$cred->mode == 1 ? 'selected':''}}>@lang('Active Mode')</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mt-3 text-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmModal">@lang('Generate New')</button>
                        </div>
                    </div>
                </div>
            
            </div>
        </div>
    </div>
</div>

@endsection
@push('script')
<script src="{{asset('assets/user/js/clipboard.min.js')}}"></script>
<script>
        $(function () {
            var public = new ClipboardJS('.public');
            public.on('success', function(e){
                 toast('success','@lang('Access key has been copied')')
            });

            $('.mode').on('change',function () { 
                $.get("{{route('merchant.api.service.mode')}}",function( res ) {
                    toast('success',res)
                })
            })
           
        });
</script>
@endpush