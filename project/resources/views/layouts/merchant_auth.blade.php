{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$gs->title.'-'}}@yield('title')</title>
    
    
    <link rel="stylesheet" href="{{asset('assets/merchant/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/merchant/css/font-awsome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/merchant/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('assets/merchant/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/merchant/css/components.css')}}">
    @stack('style')
    <!-- Favicon -->
</head>
<body>
     <div id="app">
        <section class="section">
            <div class="container-xl">
                <div class="row">
                    @yield('content')
                </div>
            </div>
        </section>     
    </div>
    <script src="{{asset('assets/merchant/js/jquery.min.js')}}"></script>
    <script src="{{asset('assets/merchant/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/merchant/js/scripts.js')}}"></script>
    @include('notify.alert')
    @stack('script')
</body>
</html> --}}

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
   <meta name="description" content="{{ $gs->title }}">
  <link href="{{ asset('assets/images/'.$gs->favicon) }}" rel="icon">
  <title>{{ $gs->title }}</title>
  <link href="{{ asset('assets/admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ asset('assets/admin/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ asset('assets/admin/css/ruang-admin.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/admin/css/custom.css') }}" rel="stylesheet">

</head>

<body class="bg-gradient-login">
  <!-- Login Content -->
  <div class="container">
      <div class="card shadow-sm my-5 login--card">
        <div class="card-body p-0">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
              <div class="login-form">
                <div class="text-center">
                  <h1 class="h4 text-gray-900 mb-4">{{ __('Merchant Login') }}</h1>
                </div>
                @include('includes.merchant.form-login')

                <form id="loginform" action="#" method="POST" class="user">
                  {{ csrf_field() }}
                  <div class="form-group">
                    <input name="email"  type="email" class="form-control" id="exampleInputEmail" aria-describedby="emailHelp"
                      placeholder="{{ __('Enter Email Address') }}" value="" required >
                  </div>
                  <div class="form-group">
                    <input name="password" type="password" class="form-control" value="" id="exampleInputPassword" placeholder="Password" required>
                  </div>

                  <div class="form-group">
                      <input id="authdata" type="hidden"  value="{{ __('Authenticating...') }}">
                      <button type="submit" class="btn btn-primary submit-btn w-100">{{ __('Login') }}</button>
                  </div>
                </form>

                
              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
  <!-- Login Content -->
  <script src="{{ asset('assets/admin/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
  <script src="{{ asset('assets/admin/js/ruang-admin.js') }}"></script>

<script>
"use strict";

$("#loginform").on('submit',function(e){
  e.preventDefault();
  $('button.submit-btn').prop('disabled',true);
  $('.alert-info').show();
  $('.alert-info p').html($('#authdata').val());
      $.ajax({
         // alert(1);
       method:"POST",
       url:$(this).prop('action'),
       data:new FormData(this),
       dataType:'JSON',
       contentType: false,
       cache: false,
       processData: false,
       success:function(data)
       {
          if ((data.errors)) {
          $('.alert-success').hide();
          $('.alert-info').hide();
          $('.alert-danger').show();
          $('.alert-danger ul').html('');
            for(var error in data.errors)
            {
              $('.alert-danger p').html(data.errors[error]);
            }
          }
          else
          {
            $('.alert-info').hide();
            $('.alert-danger').hide();
            $('.alert-success').show();
            $('.alert-success p').html('Success !');
            window.location = data;
          }
          $('button.submit-btn').prop('disabled',false);
       }

      });

});

</script>

</body>

</html>