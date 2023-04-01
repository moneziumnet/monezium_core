<!doctype html>

<html lang="en">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{$gs->title}}</title>
    <link rel="shortcut icon" href="{{asset('assets/images/'.$gs->favicon)}}">
    <link href="{{ asset('assets/user/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    @if($website_theme == 0)
        <link href="{{asset('assets/user/css/tabler.min.css')}}" rel="stylesheet"/>
    @else
        <link href="{{asset('assets/user/css/tabler-vertical.min.css')}}" rel="stylesheet"/>
    @endif
    <link href="{{asset('assets/user/css/tabler-flags.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/tabler-payments.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/tabler-vendors.min.css')}}" rel="stylesheet"/>
	<link rel="stylesheet" href="{{asset('assets/front/css/toastr.min.css')}}">
    <link href="{{asset('assets/user/css/demo.min.css')}}" rel="stylesheet"/>
    <link href="{{asset('assets/user/css/custom.css')}}" rel="stylesheet"/>
    @stack('css')
  </head>

  <body>
    <div class="wrapper">
      @if($website_theme == 0)
        @includeIf('includes.user.header')
        @includeIf('includes.user.nav')
      @else
        @includeIf('includes.user.nav_vertical')
      @endif
      <div class="page-wrapper">
        @if($website_theme !== 0)
          @includeIf('includes.user.header')
        @endif
        @yield('contents')
        @includeIf('includes.user.footer')
      </div>
    </div>
    <div class="modal fade" id="cameraModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
                <div class="modal-body text-center">
                    <video id="preview" class="p-1 border" style="width:400px;"></video>
                </div>
                <div class="modal-footer justify-content-center">
                  <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('close')</button>
                </div>
          </div>
        </div>
      </div>
	<script>
		let mainurl = '{{ url('/') }}';
	</script>
    <script src="{{asset('assets/user/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('assets/user/js/tabler.min.js')}}"></script>
    <script src="{{asset('assets/user/js/demo.min.js')}}"></script>
    <script src="{{asset('assets/front/js/custom.js')}}"></script>
    <script src="{{asset('assets/user/js/notify.min.js')}}"></script>
    <script src="{{asset('assets/user/js/webcam.min.js')}}"></script>
    <script src="{{asset('assets/tinymce/tinymce.min.js')}}"></script>
    <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>
    <script src="{{asset('assets/user/')}}/js/instascan.min.js"></script>
    <script src="{{asset('assets/user/js/apexcharts.min.js')}}"></script>

    <script>
      'use strict';
      var submit_flag = false;
      $(document).on('submit', '#iban-submit', function(e){
            var ibanapi = '{{ $gs->ibanapi }}';
            var url   = 'https://api.ibanapi.com/v1/validate/' +  $('.iban-input').val() + '?api_key=' + ibanapi;
            if(!submit_flag) {
                if(document.getElementById('iban-submit').checkValidity()) {
                    e.preventDefault();
                    $.ajax({
                        url: url,
                        dataType: 'jsonp',
                        success: function (data) {
                            if(data.result != 200) {
                                $('.iban-validation').removeClass('text-success')
                                $('.iban-validation').addClass('text-danger').text( data.message);
                            }
                            else if (data.result == 200) {
                                $('.iban-validation').removeClass('text-danger')
                                $('.iban-validation').addClass('text-success').text(data.message);
                                submit_flag=true;
                                if((data.data).hasOwnProperty('bank')) {
                                    $('#bank_name').val(data.data.bank.bank_name)
                                    $('#bank_address').val(data.data.bank.address)
                                    $('#swift_bic').val(data.data.bank.bic)
                                }
                                $('#iban-submit').submit();
                            }
                        },
                        error: function(httpObj, textStatus) {
                            $('.iban-validation').removeClass('text-success')
                            $('.iban-validation').addClass('text-danger').text('Iban Validation Api is expired or Api is not correct, Please contact Support Team');
                        }
                    });
                }
            }
        })

        $('.iban-input').on('keypress', function(e){
            var key = e.keyCode;
            if (key === 32) {
            e.preventDefault();
            }
        })


      $('.scan').click(function(){
          var scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5, mirror: false });
          scanner.addListener('scan',function(content){
              $.post("{{ route('scan.qr') }}",{email: content,_token:'{{csrf_token()}}'}, function( data ) {
                  if(data.error){
                      toastr.options =
                      {
                        "closeButton" : true,
                        "progressBar" : true
                      }
                      toastr.error(data.error);
                  } else {
                      $(".camera_value").val(data);
                      $(".camera_value").focusout()
                  }
                  $('#cameraModal').modal('hide')
              });
          });
          Instascan.Camera.getCameras().then(function (cameras){
              if(cameras.length>0){
                  $('#cameraModal').modal('show')
                      scanner.start(cameras[0]);
              } else{
                toastr.options =
                {
                  "closeButton" : true,
                  "progressBar" : true
                }
                toastr.error('No cameras found.');
              }
          }).catch(function(e){
            toastr.options =
            {
              "closeButton" : true,
              "progressBar" : true
            }
            toastr.error('No cameras found.');
          });
      });
      var theme = localStorage.getItem('tablerTheme');
      tinymce.init({
        selector: '#message',
        menubar: false,
        statusbar: false,
        skin: theme == 'dark' ? 'oxide-dark' : 'oxide',
        content_css: theme == 'dark' ? 'dark' : 'tinymce-5'
    });
    </script>
    @stack('js')


<script>
	'use strict';

	@if(Session::has('message'))
	toastr.options =
	{
		"closeButton" : true,
		"progressBar" : true
	}
		toastr.success("{{ session('message') }}");
	@endif

	@if(Session::has('error'))
	toastr.options =
	{
		"closeButton" : true,
		"progressBar" : true
	}
		toastr.error("{{ session('error') }}");
	@endif

	@if(Session::has('info'))
	toastr.options =
	{
		"closeButton" : true,
		"progressBar" : true
	}
		toastr.info("{{ session('info') }}");
	@endif

	@if(Session::has('warning'))
	toastr.options =
	{
		"closeButton" : true,
		"progressBar" : true
	}
		toastr.warning("{{ session('warning') }}");
	@endif
  </script>
  </body>
</html>
