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

                        <div class="row form-group mb-3 mt-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-label required">@lang('Select Contractor')</div>
                                <select class="form-select shadow-none" id="contractor" name="contractor" required>
                                    <option value="" selected>@lang('Select')</option>
                                    @foreach ($clientlist as $user)
                                        @php
                                            $client_item = array(
                                                'name' => $user->name,
                                                'email' => $user->email,
                                                'address' => $user->address,
                                                'phone' => $user->phone,
                                                'registration_no' => $user->registration_no,
                                                'vat_no' => $user->vat_no,
                                                'contact_person' => $user->contact_person
                                            )
                                        @endphp
                                      <option value="Beneficiary {{$user->id}}" type="beneficiary" data="{{json_encode($client_item)}}" {{ $data->contractor_type == 'App\Models\Beneficiary' && $user->id == $data->contractor_id ? 'selected' : ''}} >{{$user->name}}</option>
                                    @endforeach
                                    <option type="user" value="User {{auth()->user()->id}}" data="{{json_encode($client_item)}}" {{ $data->contractor_type == 'App\Models\User' && $user->id == auth()->user()->id ? 'selected' : ''}} >{{auth()->user()->name}}</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-label required">@lang('Select Client')</div>
                                <div class="input-group">
                                    <select class="form-select shadow-none" id="client" name="client" required>
                                        <option value="" selected>@lang('Select')</option>
                                        @foreach ($clientlist as $user)
                                        @php
                                            $client_item = array(
                                                'name' => $user->name,
                                                'email' => $user->email,
                                                'address' => $user->address,
                                                'phone' => $user->phone,
                                                'registration_no' => $user->registration_no,
                                                'vat_no' => $user->vat_no,
                                                'contact_person' => $user->contact_person
                                            )
                                        @endphp
                                        <option value="{{$user->id}}" type="beneficiary" data="{{json_encode($client_item)}}" {{ $data->client_type == 'App\Models\Beneficiary' && $user->id == $data->contractor_id ? 'selected' : ''}} >{{$user->name}}</option>
                                        @endforeach
                                        @php
                                        $client_item = App\Models\User::select('name','email','address','phone')
                                            ->find(auth()->id());
                                        @endphp
                                        <option value="User {{auth()->user()->id}}" type="user" data="{{json_encode($client_item)}}" {{ $data->client_type == 'App\Models\User' && $user->id == auth()->user()->id ? 'selected' : ''}} >{{auth()->user()->name}}</option>
                                    </select>
                                    <button type="button"  data-bs-toggle="tooltip" data-bs-original-title="@lang('Add New Beneficiary')" class="input-group-text beneficiary"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Contract AOA Name')}}</label>
                                    <input name="title" id="title" class="form-control" autocomplete="off" placeholder="{{__('Enter Title')}}" type="text" value="{{$data->title}}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Amount')}}</label>
                                    <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('Enter Amount')}}" type="number" step="any" value="{{$data->amount}}" required>
                                </div>
                            </div>
                        </div>

                        @php
                            $information = $data->information ? json_decode($data->information, True) : array("" => null);
                        @endphp
                        @foreach ($information as $key => $value)

                            <div class="row form-group mb-3 mt-3">
                                <div class="col-md-4 mb-3">
                                    @if ($loop->first)
                                    <div class="form-label">{{__('Title')}}</div>
                                    @endif
                                    <input type="text" name="desc_title[]" class="form-control shadow-none itemname" value="{{$key}}" >
                                </div>
                                <div class="col-md-7 mb-3">
                                    @if ($loop->first)
                                    <div class="form-label">{{__('Text')}} <span class="pattern-help"><i class="fas fa-question-circle"></i></span></div>
                                    @endif
                                    <textarea type="text" name="desc_text[]" class="form-control shadow-none itemvalue">{{$value}}</textarea>
                                </div>
                                @if ($loop->first)
                                    <div class="col-md-1 mb-3">
                                        <div class="form-label">&nbsp;</div>
                                        <button type="button" class="btn btn-primary w-100 desc-add"><i class="fas fa-plus"></i></button>
                                    </div>
                                @else
                                    <div class="col-md-1 mb-3">
                                        <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        <div class="description-extra-container"></div>

                        <input name="contract_id" type="hidden" class="form-control" value="{{$data->contract_id}}">

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Update')}}</button>
                        </div>

                        <div class="modal modal-blur fade" id="modal-pattern-help" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="modal-status bg-primary"></div>
                                <div class="modal-body text-center py-4">
                                    <i  class="fas fa-question-circle fa-3x text-primary mb-2"></i>
                                    <h3>{{__('How to write text')}}</h3>
                                    <div class="row form-group mb-3 mt-3">
                                        <div class="col-md-4 mb-3"><div class="form-label">{{__('Pattern name')}}</div></div>
                                        <div class="col-md-8 mb-3"><div class="form-label">{{__('Value')}}</div></div>
                                    </div>
                                    <div class="row form-group my-3">
                                        <div class="col-md-4 mb-3">
                                            <input type="text" class="form-control shadow-none itemname" readonly value="Amount" >
                                        </div>
                                        <div class="col-md-7 mb-3">
                                            <input type="number" id="amount2" step="any" name="amount2" class="form-control shadow-none itemvalue" value="{{$data->amount}}" readonly >
                                        </div>
                                    </div>
                                    <div class="default-contractor-pattern-container">
                                        @foreach (json_decode($data->default_pattern) as $key =>$value)
                                        @if(str_contains($key, 'Contractor'))
                                        <div class="row form-group mb-3 mt-3">
                                            <div class="col-md-4 mb-3">
                                                <input type="text" name="default_item[]" class="form-control shadow-none itemname" value="{{$key}}" readonly>
                                            </div>
                                            <div class="col-md-7 mb-3">
                                                <input type="text" name="default_value[]" class="form-control shadow-none itemvalue" value="{{$value}}" readonly>
                                            </div>
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                    <div class="default-client-pattern-container">
                                        @foreach (json_decode($data->default_pattern) as $key =>$value)
                                        @if(str_contains($key, 'Client'))
                                        <div class="row form-group mb-3 mt-3">
                                            <div class="col-md-4 mb-3">
                                                <input type="text" name="default_item[]" class="form-control shadow-none itemname" value="{{$key}}" readonly>
                                            </div>
                                            <div class="col-md-7 mb-3">
                                                <input type="text" name="default_value[]" class="form-control shadow-none itemvalue" value="{{$value}}" readonly>
                                            </div>
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                    @foreach (json_decode($data->pattern, True) as $key => $value)
                                        <div class="row form-group mb-3 mt-3">
                                            <div class="col-md-4 mb-3">
                                                <input type="text" name="item[]" class="form-control shadow-none itemname" value="{{$key}}" >
                                            </div>
                                            <div class="col-md-7 mb-3">
                                                <input type="text" name="value[]" class="form-control shadow-none itemvalue" value="{{$value}}" >
                                            </div>
                                            @if ($loop->first)
                                                <div class="col-md-1 mb-3">
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
                                    <div class="row form-group mt-3 text-start">
                                        <div class="col-12">
                                            <label class="form-label">{{__('Text')}}</label>
                                            <textarea name="description" class="form-control" readonly>{{__('Hello, {name}. 
I need {amount} from you.')}}</textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group mt-3 text-start">
                                        <div class="col-12">
                                            <label class="form-label">{{__('Preview')}}</label>
                                            <textarea name="description" class="form-control" readonly>{{__('Hello, Aleksander.
I need 1000 from you.')}}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="modal-status bg-primary"></div>
        <div class="modal-body text-center py-4">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
            <h3>{{__('Create New Beneficiary')}}</h3>
            <div class="row text-start">
                <div class="col">
                    <form action="{{route('user.contract.beneficiary.create')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mt-2">
                            <label class="form-label required">{{__('Name')}}</label>
                            <input name="name" id="name" class="form-control shadow-none" placeholder="{{__('Name')}}" type="text" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group mt-2">
                            <label class="form-label required">{{__('Email')}}</label>
                            <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('user@email.com')}}" type="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="form-group mt-2">
                            <label class="form-label required">{{__('Phone Number')}}</label>
                            <input name="phone" id="phone" class="form-control shadow-none" placeholder="{{__('+123456789')}}" type="text" value="{{ old('phone') }}" required>
                        </div>
                        <div class="form-group mt-2">
                            <label class="form-label required">{{__('Registration NO')}}</label>
                            <input name="registration_no" id="registration_no" class="form-control shadow-none" placeholder="{{__('Registration NO')}}" type="text" value="{{ old('registration_no') }}" required>
                        </div>
                        <div class="form-group mt-2">
                            <label class="form-label required">{{__('VAT NO')}}</label>
                            <input name="vat_no" id="vat_no" class="form-control shadow-none" placeholder="{{__('VAT NO')}}" type="text" value="{{ old('vat_no') }}" required>
                        </div>
                        <div class="form-group mt-2">
                            <label class="form-label required">{{__('Contact Person')}}</label>
                            <input name="contact_person" id="contact_person" class="form-control shadow-none" placeholder="{{__('Contact Person')}}" type="text" value="{{ old('contact_person') }}" required>
                        </div>
                        <input type="hidden" name="user_id" value="{{auth()->id()}}">
                        <div class="row mt-3">
                            <div class="col"><a href="#" class="btn w-100" data-bs-dismiss="modal">
                                {{__('Cancel')}}
                                </a>
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary w-100 confirm">
                                {{__('Confirm')}}
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

@push('js')
<script>
    'use strict';
    var contractor_select_html = "";
    var client_select_html = "";
    $(document).ready(function() {
        contractor_select_html = $('#contractor').html().replace(/selected/g, '');
        client_select_html = $('#client').html().replace(/selected/g, '');
        var old_value1 = $('#contractor').val();
        var old_value2 = $('#client').val();
        var client = $('#client option:selected');
        if(client.attr('type') == 'user') {
            $('#contractor').html(contractor_select_html);
            var option_list = $('#contractor option[type="user"]');
            option_list.remove();
        }
        var contractor = $('#contractor option:selected');
        if(contractor.attr('type') == 'user') {
            $('#client').html(client_select_html);
            var option_list = $('#client option[type="user"]');
            option_list.remove();
        }
        $('#contractor').val(old_value1);
        $('#client').val(old_value2);
    })
    $('#contractor').on('change', function() {
        var contractor = $('#contractor option:selected');
        var old_value = $('#client').val();
        if(contractor.attr('type') == 'user') {
            const data = JSON.parse(contractor.attr('data'));
            setDefaultDiv(
                'default-contractor-pattern-container',
                ['Contractor Name', 'Contractor Email', 'Contractor Address', 'Contractor Phone'],
                [data.name, data.email, data.address, data.phone, ]
            );
            $('#client').html(client_select_html);
            var option_list = $('#client option[type="user"]');
            option_list.remove();
        } else if(contractor.attr('type') == 'beneficiary') {
            const data = JSON.parse(contractor.attr('data'));
            setDefaultDiv(
                'default-contractor-pattern-container',
                ['Contractor Name', 'Contractor Email', 'Contractor Address', 'Contractor Phone', 'Contractor Registration No','Contractor VAT No'],
                [data.name, data.email, data.address, data.phone, data.registration_no,data.vat_no]
            );
            $('#client').html(client_select_html);
        } else {
            $('.default-contractor-pattern-container').html('');
            $('#client').html(client_select_html);
        }
        $('#client').val(old_value);
    })

    $('#client').on('change', function() {
        var client = $('#client option:selected');
        var old_value = $('#contractor').val();
        if(client.attr('type') == 'user') {
            const data = JSON.parse(client.attr('data'));
            setDefaultDiv(
                'default-client-pattern-container',
                ['Client Name', 'Client Email', 'Client Address', 'Client Phone'],
                [data.name, data.email, data.address, data.phone, ]
            );
            $('#contractor').html(contractor_select_html);
            var option_list = $('#contractor option[type="user"]');
            option_list.remove();
        } else if(client.attr('type') == 'beneficiary') {
            const data = JSON.parse(client.attr('data'));
            setDefaultDiv(
                'default-client-pattern-container',
                ['Client Name', 'Client Email', 'Client Address', 'Client Phone', 'Client Registration No','Client VAT No'],
                [data.name, data.email, data.address, data.phone, data.registration_no,data.vat_no]
            );
            $('#contractor').html(contractor_select_html);
        } else {
            $('.default-client-pattern-container').html('');
            $('#contractor').html(contractor_select_html);
        }
        $('#contractor').val(old_value);
    });
    var setDefaultDiv = function(div_name, keys, values){
        let str_html = "";
        for (let index = 0; index < keys.length; index++) {
            str_html += `
            <div class="row form-group mb-3 mt-3">
                <div class="col-md-4 mb-3">
                    <input type="text" name="default_item[]" class="form-control shadow-none itemname" value="${keys[index]}" readonly>
                </div>
                <div class="col-md-7 mb-3">
                    <input type="text" name="default_value[]" class="form-control shadow-none itemvalue" value="${values[index]}" readonly >
                </div>
            </div>
            `;
            
        }
        $('.' + div_name).html(str_html);
    }
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

    $('.desc-add').on('click',function(){
        $('.description-extra-container').append(`
            <div class="row form-group mb-3 mt-3">
                <div class="col-md-4 mb-3">
                    <input type="text" name="desc_title[]" class="form-control shadow-none itemname">
                </div>
                <div class="col-md-7 mb-3">
                    <textarea type="text" name="desc_text[]" class="form-control shadow-none itemvalue"></textarea>
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
    $('.beneficiary').on('click',function() {
        $('#modal-success').modal('show')
    })
    $('.pattern-help').on('click', function(){
        $("#modal-pattern-help").modal('show')
    })
    $('#amount').on('change', function() {
        $('#amount2').val($('#amount').val());
    });
</script>
@endpush
