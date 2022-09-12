<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{__($gs->title)}} - @lang('Contract : '.$data->id) </title>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}
    <link rel="stylesheet" href="{{asset('assets/admin/css/font-awsome.min.css')}}">

    <link href="{{asset('assets/user/')}}/css/tabler.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/fontawesome-free/css/all.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/custom.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/')}}/css/demo.min.css" rel="stylesheet"/>

    <script src="{{asset('assets/user/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('assets/front/js/custom.js')}}"></script>

    <link rel="stylesheet" type="text/css" href="{{asset('assets/user/')}}/css/bootstrap-4.3.1.css">
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery-1.12.4.min.js"></script>
    <link type="text/css" href="{{asset('assets/user/')}}/css/jquery-ui.css" rel="stylesheet">
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{asset('assets/user/')}}/js/jquery.signature.js"></script>
    <link rel="stylesheet" type="text/css" href="{{asset('assets/user/')}}/css/jquery.signature.css">

    <style>
        .kbw-signature { width: 100%; height: 200px;}
        #sig canvas{
            width: 100% !important;
            height: auto;
        }
    </style>


    @stack('style')
  </head>

  <body>
    <div class="wrapper mb-3 mt-5">
          <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    <div class="col-sm-12 text-right" style="text-align: right">
                        <a href="{{route('user.contract-pdf', $data->id)}}">
                          <i class="fas fa-file-pdf" aria-hidden="true"></i> {{__('PDF')}}
                        </a> &nbsp;
                      </div>
                    <div class="card card-lg">
                      <div class="card-body">
                        @include('includes.flash')
                        <div class="row">
                            <div class="text-center">
                                <h1>
                                    {{__($data->title)}}
                                </h1>
                            </div>
                            <div class="text-center mt-5">
                                <p>
                                    {{__($description)}}
                                </p>
                            </div>
                            @if ($data->status == 1)
                            <div class="wrapper-image-preview col-md-6">
                                <p class="text-muted text-center"> {{__('Contracter')}} </p>
                                <div class="box full-width">
                                    <div class="back-preview-image" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                                </div>
                            </div>
                            <div class="wrapper-image-preview col-md-6">
                                <p class="text-muted text-center">{{__('You already signed')}}</p>
                                <div class="box full-width">
                                    <div class="back-preview-image" style="background-image: url({{ $data->customer_image_path ? asset('assets/images/'.$data->customer_image_path) : '' }});"></div>
                                </div>
                            </div>
                            @else
                                <div class="wrapper-image-preview col-md-6">
                                    <p class="text-muted text-center"> {{__('Contracter')}} </p>
                                    <div class="box full-width">
                                        <div class="back-preview-image" style="background-image: url({{ $data->contracter_image_path ? asset('assets/images/'.$data->contracter_image_path) : '' }});"></div>
                                    </div>
                                </div>
                                <form method="POST" action="{{route('user.contract.sign', $data->id)}}" class="col-md-6">
                                    @csrf
                                    <div class="col-md-12">
                                        <label class="mb-3" for="">{{__('Signature:')}}</label>
                                        <div id="sig" ></div>
                                        <textarea id="signature64" name="signed" style="display: none"></textarea>
                                    </div>
                                    <div class="col-md-11 mx-auto">
                                        @if (auth()->user())

                                        <div class="back-preview-image" id="preview_image" style= "display: none;"></div>
                                        <div class="form-group mt-3">
                                            <select class="form-select shadow-none" id="sign_select" name="sign_path">
                                                <option value="" selected class="text-center" >{{__('Select Photo')}}</option>
                                                <option value="{{auth()->user()->signature}}" >{{__('Signature')}}</option>
                                                <option value="{{auth()->user()->stamp}}" >{{__('Stamp')}}</option>
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                    <button id="clear" class="btn btn-primary btn-sm mt-2">{{__('Clear Signature')}}</button>
                                    <input type="hidden" name="contract_id" value="{{$data->id}}">
                                    <br/>
                                    <div class="text-center">
                                    <button type="submit" class="btn btn-primary ">{{__('SIGN')}}</button>
                                    </div>
                                </form>
                            @endif


                        </div>
                        <p class="text-muted text-center mt-5">{{__('Thank you very much for doing new contract. We look forward to working with
                            you again!')}} <br> <small class="mt-5">{{__('All right reserved ')}} <a href="{{url('/')}}">{{$gs->title}}</a></small></p>

                      </div>
                    </div>
                </div>
            </div>
          </div>
      </div>
      <script src="{{asset('assets/admin/js/jquery.min.js')}}"></script>
      <!-- Tabler Core -->
      <script src="{{asset('assets/user/')}}/js/tabler.min.js"></script>
      <script src="{{asset('assets/user/')}}/js/demo.min.js"></script>
        <script type="text/javascript">
            var sig = $('#sig').signature({syncField: '#signature64', syncFormat: 'PNG'});
            let mainurl = '{{ url('/') }}';
            $('#clear').click(function(e) {
                e.preventDefault();
                sig.signature('clear');
                $("#signature64").val('');
                $("#sign_select").val('');
                document.getElementById("preview_image").style.display = "none";
                document.getElementById("sig").style.display = "block";
            });
            $('#sign_select').change(function(){
                var sign_select = $('#sign_select').val();
                var image_path =  `${mainurl}/assets/images/${sign_select}`;
                console.log(image_path)
                document.getElementById("preview_image").style.display = "block";
                    $('#preview_image').css('background-image',"url("+image_path+")" )
                document.getElementById("sig").style.display = "none";
            })
        </script>
      {{-- @include('notify.alert') --}}
      @stack('script')

</body>
</html>
