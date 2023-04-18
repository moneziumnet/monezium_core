<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{$gs->title}}</title>

    <link rel="stylesheet" href="{{asset('assets/front/css/bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/animate.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/all.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/lightbox.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/odometer.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/owl.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/main.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/front/css/toastr.min.css')}}">

    <link rel="stylesheet" href="{{ asset('assets/front/css/styles.php?color='.str_replace('#','',$gs->colors)) }}">

    @if ($default_font->font_value)
        <link href="https://fonts.googleapis.com/css?family={{ $default_font->font_value }}&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    @endif

    @if ($default_font->font_family)
        <link rel="stylesheet" id="colorr" href="{{ asset('assets/front/css/font.php?font_familly='.$default_font->font_family) }}">
    @else
        <link rel="stylesheet" id="colorr" href="{{ asset('assets/front/css/font.php?font_familly='."Open Sans") }}">
    @endif

    @stack('css')
    <link rel="shortcut icon" href="{{asset('assets/images/'.$gs->favicon)}}">
</head>

<body>
    <div class="align-items-center d-flex" style="background-color: rgba(0,0,0,.03); min-height:100%;">
        <div class="card p-4 mx-auto" style="max-width:600px;">

            <div class="card-body my-4">
                <div class="text-center text-black my-4">
                    <h3 class="text-dark" ><b>{{__("Welcome")}}</b></h2>
                </div>
                <div class = "text-center my-4">
                    <img src="{{asset('assets/images/'.$gs->logo)}}" width="50%" >
                </div>

                <form id="registerform" class="account-form row gy-3 gx-4 align-items-center" action="{{ route('user.register.submit', $id) }}" method="POST">
                    @includeIf('includes.user.form-both')
                    @csrf
                    <input type="hidden" name="reff" id="reff" value="{{Session::get('affilate')}}">
                    <div class="form-group">
                        <label for="inp-user-type" class="form-label">{{ __('Select Type') }}</label>
                        <select id="test" class="form-control form--control mb-3" name="form_select" onchange="showDiv( this)">
                            <option value="0"> {{__("Private")}}</option>
                            <option value="1"> {{__("Corporate")}}</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label for="name" class="form-label">@lang('Your First Name')</label>
                        <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" id="name" name="firstname" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="name" class="form-label">@lang('Your Last Name')</label>
                        <input type="text" pattern="[^À-ž()/><\][\\\-;&$@!|]+" id="name" name="lastname" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="customer_dob" class="form-label">@lang('Your Birthday')</label>
                        <input type="date" id="customer_dob" name="customer_dob" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="email" class="form-label">@lang('Your Email')</label>
                        <input type="email" id="email" name="email" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-12">
                        <label for="phone" class="form-label">@lang('Your Phone')</label>
                        <input type="number" id="phone" name="phone" class="form-control form--control" required>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Address') }}</label>
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control form--control" id="address" name="address" placeholder="{{ __('Enter Address') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('City') }}</label>
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control form--control" id="city" name="city" placeholder="{{ __('Enter City') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Zip Code') }}</label>
                            <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="form-control form--control" id="zip" name="zip" placeholder="{{ __('Enter Zip Code') }}" value="" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="inp-name" class="form-label">{{ __('Select Country') }}</label>
                            <select class="form-control form--control" name="country" required>
                                <option value="">{{ __('Select Country') }}</option>
                                @foreach(DB::table('countries')->get() as $dta)
                                <option value="{{ $dta->id }}">{{ $dta->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="password" class="form-label">@lang('Your Password')</label>
                            <input type="password" id="password" name="password" class="form-control form--control" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="confirm-password" class="form-label">@lang('Confirm Password')</label>
                            <input type="password" id="confirm-password" name="password_confirmation" class="form-control form--control" required>
                        </div>
                    </div>
                    <div id="private_div" class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="personal-code" class="form-label">{{ __('Personal Code/Number') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="personal_code" name="personal_code" placeholder="{{ __('Enter Personal Code/Number') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="your-id" class="form-label">{{ __('Your ID Number') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="your_id" name="your_id" placeholder="{{ __('Enter Your ID Number') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="your-id" class="form-label" required>{{ __('Provider Authority Name') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="private-input form-control form--control" id="issued_authority" name="issued_authority" placeholder="{{ __('Enter Provider Authority Name') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="date-of-issue" class="form-label">{{ __('Date of Issue') }}</label>
                                    <input type="date" class="private-input form-control form--control datepicker" id="date_of_issue" name="date_of_issue" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="date-of-expire" class="form-label">{{ __('Date of Expire') }}</label>
                                    <input type="date" class="private-input form-control form--control datepicker" id="date_of_expire" name="date_of_expire" placeholder="{{ __('yyyy-mm-dd') }}" value="" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="corporate_div" style="display: none;" class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="dob" class="form-label">{{ __('Company Name') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_name"name="company_name" placeholder="{{ __('Enter Company Name') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    @php
                                        $companytype = ['LIMITED_LIABILITY', 'SOLE_TRADER', 'PARTNERSHIP', 'PUBLIC_LIMITED_COMPANY', 'JOINT_STOCK_COMPANY', 'CHARITY']
                                    @endphp
                                    <label for="inp-user-type" class="form-label">{{ __('Select Company Type') }}</label>
                                    <select id="company_type" class="company-input form-control" name="company_type">
                                        <option value=""> {{__('Select Company Type')}}</option>
                                        @foreach ( $companytype as $type )
                                            <option value="{{$type}}"> {{$type}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="inp-name" class="form-label">{{ __('Company Address') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_address" name="company_address" placeholder="{{ __('Enter Company Address') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="inp-name" class="form-label">{{ __('Company City') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_city" name="company_city" placeholder="{{ __('Enter Company City') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="inp-name" class="form-label">{{ __('Company ZipCode') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_zipcode" name="company_zipcode" placeholder="{{ __('Enter Company ZipCode') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="inp-name" class="form-label">{{ __('Company Country') }}</label>
                                    <select class="company-input form-control form--control" name="company_country">
                                        <option value="">{{ __('Select Country') }}</option>
                                        @foreach(DB::table('countries')->get() as $dta)
                                        <option value="{{ $dta->id }}">{{ $dta->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="dob" class="form-label">{{ __('Registration No') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_reg_no"name="company_reg_no" placeholder="{{ __('Enter Company Registration No') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="dob" class="form-label">{{ __('VAT No') }}</label>
                                    <input type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" class="company-input form-control form--control" id="company_vat_no"name="company_vat_no" placeholder="{{ __('Enter Company VAT No') }}" value="">
                                </div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="form-group">
                                    <label for="dob" class="form-label">{{ __('Registration Date') }}</label>
                                    <input type="date" class="company-input form-control form--control" id="company_dob" name="company_dob" placeholder="{{ __('yyyy-mm-dd') }}" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="global_ip" name="global_ip" class="form-control form--control">
    
    
                    <div class="col-sm-12 d-flex flex-wrap justify-content-between align-items-center">
                        <button type="submit" class="cmn--btn bg--base me-3">
                            @lang('Register Now')
                        </button>
                        <div class="text-end">
                            <a href="{{ route('user.login')}}" class="text--base">@lang('Already have
                                an account ')?</a>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="form-check mt-3 mb-0">
                            <input type="checkbox" id="accept" class="form-check-input" checked>
                            <label class="form-check-label" for="accept">@lang('I accept all the') <a href="#0" class="text--base">@lang('privacy & policy')</a></label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Tabler Core -->
    <script src="{{asset('assets/front/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('assets/front/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/front/js/viewport.jquery.js')}}"></script>
    <script src="{{asset('assets/front/js/odometer.min.js')}}"></script>
    <script src="{{asset('assets/front/js/lightbox.min.js')}}"></script>
    <script src="{{asset('assets/front/js/owl.min.js')}}"></script>
    <script src="{{asset('assets/front/js/toastr.min.js')}}"></script>
    <script src="{{asset('assets/front/js/notify.js')}}"></script>
    <script src="{{asset('assets/front/js/main.js')}}"></script>
    <script src="{{asset('assets/front/js/custom.js')}}"></script>

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
    </script>

    <script>
        'use strict';
        let mainurl = '{{ url('/') }}';
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $.getJSON("https://api.ipify.org/?format=json", function(e) {
                $('#global_ip').val(e.ip);
            });
        })
    </script>
    <script type="text/javascript">
        'use strict';
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    </script>

    <script>
    'use strict';
    function showDiv(select) {
        var company_input_list = $('.company-input');
        var private_input_list = $('.private-input');
        if (select.value == 1) {
            document.getElementById('corporate_div').style.display = "block";
            document.getElementById('private_div').style.display = "none";
            for (let i = 0; i < company_input_list.length; i++) {
                const item = company_input_list[i];
                item.required = true;
            }
            for (let i = 0; i < private_input_list.length; i++) {
                const item = private_input_list[i];
                item.required = false;
            }
        } else {
            document.getElementById('corporate_div').style.display = "none";
            document.getElementById('private_div').style.display = "block";
            for (let i = 0; i < company_input_list.length; i++) {
                const item = company_input_list[i];
                item.required = false;
            }
            for (let i = 0; i < private_input_list.length; i++) {
                const item = private_input_list[i];
                item.required = true;
            }
        }
    }

    </script>


</body>

</html>
