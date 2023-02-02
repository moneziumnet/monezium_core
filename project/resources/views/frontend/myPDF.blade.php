<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <!-- CSS files -->
    {{-- <link rel="shortcut icon" href="{{getPhoto($gs->favicon)}}"> --}}
    {{-- <link rel="stylesheet" href="{{public_path('assets/admin/')}}css/font-awsome.min.css"> --}}

    {{-- <link href="{{URL::asset('assets/user/')}}/css/tabler.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-flags.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-payments.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/tabler-vendors.min.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/custom.css" rel="stylesheet"/>
    <link href="{{URL::asset('assets/user/')}}/css/demo.min.css" rel="stylesheet"/>


    <link rel="stylesheet" type="text/css" href="{{URL::asset('assets/user/')}}/css/bootstrap-4.3.1.css">
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery-1.12.4.min.js"></script>
    <link type="text/css" href="{{URL::asset('assets/user/')}}/css/jquery-ui.css" rel="stylesheet">
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="{{URL::asset('assets/user/')}}/js/jquery.signature.js"></script>
    <link rel="stylesheet" type="text/css" href="{{URL::asset('assets/user/')}}/css/jquery.signature.css"> --}}
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>

        #document-des {
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .document-logo {
            height: 8rem;
            width: auto;
        }

        .col-1 {
            flex: 0 0 8.333333%;
            max-width: 8.333333%;
        }

        .col-2 {
            flex: 0 0 16.666667%;
            max-width: 16.666667%;
        }

        .col-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }

        .col-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        .col-5 {
            flex: 0 0 41.666667%;
            max-width: 41.666667%;
        }

        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .col-7 {
            flex: 0 0 58.333333%;
            max-width: 58.333333%;
        }

        .col-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }

        .col-9 {
            flex: 0 0 75%;
            max-width: 75%;
        }

        .col-10 {
            flex: 0 0 83.333333%;
            max-width: 83.333333%;
        }

        .col-11 {
            flex: 0 0 91.666667%;
            max-width: 91.666667%;
        }

        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        .row {
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

    </style>
</head>
<body>
    <div>
        <div class="text-center row text-wrap text-center">
            <div class="mt-3">
                    <img src="{{'data:image/jpeg;base64,'.$image}}" class="document-logo" style="width: auto;">
            </div>
            <div class="mt-3">
                <h3 class="font-weight-bold">
                    {{$gs->disqus}}<br/>
                </h3>

            </div>
            <div class="mt-3 row" style="font-size:8px;">
                <div class="col-4">
                    <b class="font-weight-bold" >{{__('First, Last name / Company name: ')}}</b>
                </div>
                <div class="col-5">
                    <p>{{$user->company_name ?? $user->name}}</p>
                </div>

            </div>
            <div class="mt-3 row" style="font-size:8px;">
                <div class="col-4">
                    <b class="font-weight-bold" >{{__('Personal Code / Company Registration No: ')}}</b>
                </div>
                <div class="col-5">
                    <p>{{$user->company_reg_no ?? $user->personal_code}}</p>
                </div>
            </div>
            <div class="mt-2 row" style="font-size:8px;">
                <div class="col-4">
                    <b class="font-weight-bold" >{{__('Address: ')}}</b>
                </div>
                <div class="col-5">
                    <p>{{$user->company_address ?? $user->address}}<br/>
                    {{$user->company_city ?? $user->city}}, {{$user->company_zipcode ?? $user->zip}}<br/>
                    </p>
                </div>
            </div>
            <div class="mt-2 mb-3 row" style="font-size:8px;">
                <div class="col-4">
                    <b class="font-weight-bold" >{{__('Email: ')}}</b>
                </div>
                <div class="col-5">
                    <p>{{$user->email}}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center row text-wrap text-center">

        <div class="mt-3">
            <h6 class="font-weight-bold">
                {{__('Transaction History')}}<br/>
            </h6>

        </div>
        @if (isset($start_time) && isset($end_time))
        <div>
            <h6 class="font-weight-bold">
                {{$start_time ? __('FROM ') : ''}}{{$start_time ?? '' }}{{__(' TILL ')}}{{$end_time}}<br/>
            </h6>
        </div>
    </div>
    @endif
    <div class="table-responsive mb-3">
        <table class="table card-table table-vcenter text-wrap datatable justify-content-center">
            <thead>
                <tr>
                    <th style="width:15%;font-size:8px;">Date/Transaction No.</th>
                    <th style="width:15%;font-size:8px;">Sender</th>
                    <th style="width:15%;font-size:8px;">Receiver</th>
                    <th style="width:20%;font-size:8px;">Description</th>
                    <th style="width:15%;font-size:8px;">Amount</th>
                    <th style="width:10%;font-size:8px;">Fee</th>
                    <th style="width:10%;font-size:8px;">Currency</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $i = 1;
                @endphp
                @foreach($trans as $tran)
                <tr>

                    <td style="font-size:8px;">{{date('d-m-Y', strtotime($tran->created_at))}} <br/> {{$tran->trnx}}</td>
                    <td style="font-size:8px;">{{__(json_decode($tran->data)->sender ?? "")}}</td>
                    <td style="font-size:8px;">{{__(json_decode($tran->data)->receiver ?? "")}}</td>
                    <td style="text-align: left; font-size:8px;">{{__(json_decode($tran->data)->description ?? "")}}<br/>{{ucwords(str_replace('_',' ',$tran->remark))}}</td>
                    <td style="text-align: left;font-size:8px;">{{$tran->type}} {{amount($tran->amount,$tran->currency->type,2)}}</td>
                    <td style="text-align: left;font-size:8px;">{{'-'}} {{amount($tran->charge,$tran->currency->type,2)}} </td>
                    <td style="text-align: left;font-size:8px;">{{$tran->currency->code}} </td>

                </tr>
                @php
                    $i++;
                @endphp
                @endforeach
            </tbody>
        </table>



    </div>
    <div id="document-des" style="text-align: center; font-size:8px;">
        The document is computer printout and does not require any additional signatures or the Financial Institution's seal.<br/>
    Monezium GE LLC registered in Georgia(Registration number: 4151104933; license number: 398/S/1B-7T/393/2021)cooperating with<br/>
    Monezium Spzoo, registered in Poland(Registration number: 0000728097 ; license number: MIP33/2019)<br/>
    Clear Junction Limited, registered in England with registered number 10266827, Registered address: 4th Floor Imperial House, 15 Kingsway, London, United Kingdom,
    Clear Junction is authorised and regulated by the Financial Conduct Authority under reference number 90068
    </div>
    <script src="{{URL::asset('assets/admin/')}}/js/jquery.min.js"></script>
    <!-- Tabler Core -->
    <script src="{{URL::asset('assets/user/')}}/js/tabler.min.js"></script>
    {{-- <script src="{{public_path('assets/user/')}}js/demo.min.js"></script> --}}
    {{-- @include('notify.alert') --}}
    @stack('script')
</body>
</html>
