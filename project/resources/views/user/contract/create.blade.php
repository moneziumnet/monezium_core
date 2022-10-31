@extends('layouts.user')

@push('css')

@endpush

@section('contents')
<div class="container-xl">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col">
          <h2 class="page-title">
            {{__('Create Contract')}}
          </h2>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">

              <a href="{{ route('user.contract.index') }}" class="btn btn-primary d-sm-inline-block">
                  <i class="fas fa-backward me-1"></i> {{__('Contract List')}}
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
                    <form id="contract-form" action="{{ route('user.contract.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row form-group mb-3 mt-3">
                            <div class="col-md-6 mb-3">
                                <div class="form-label required">@lang('Select Contractor')</div>
                                <select class="form-select shadow-none" name="contractor" id="contractor" required>
                                    <option value="" selected>@lang('Select')</option>
                                    @foreach ($clientlist as $user)
                                        @php
                                            $client_item = array(
                                                'name' => $user->company_name ?? $user->name,
                                                'email' => $user->email,
                                                'address' => $user->address,
                                                'phone' => $user->phone,
                                                'registration_no' => $user->registration_no,
                                                'vat_no' => $user->vat_no,
                                                'contact_person' => $user->contact_person
                                            )
                                        @endphp
                                        <option value="Beneficiary {{$user->id}}" type="beneficiary" data="{{json_encode($client_item)}}" >{{$user->company_name ?? $user->name}}</option>
                                    @endforeach
                                    @php
                                    $client_item = App\Models\User::select('name','email','address','phone')
                                        ->find(auth()->id());
                                    @endphp
                                    <option value="User {{auth()->user()->id}}" type="user" data="{{json_encode($client_item)}}" >{{auth()->user()->company_name ?? auth()->user()->name}}</option>
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
                                                'name' => $user->company_name ?? $user->name,
                                                'email' => $user->email,
                                                'address' => $user->address,
                                                'phone' => $user->phone,
                                                'registration_no' => $user->registration_no,
                                                'vat_no' => $user->vat_no,
                                                'contact_person' => $user->contact_person
                                            )
                                        @endphp
                                        <option data="{{json_encode($client_item)}}" type="beneficiary" value="Beneficiary {{$user->id}}" >{{$user->company_name ?? $user->name}}</option>
                                        @endforeach
                                        @php
                                        $client_item = App\Models\User::select('name','email','address','phone')
                                            ->find(auth()->id());
                                        @endphp
                                        <option value="User {{auth()->user()->id}}" type="user" data="{{json_encode($client_item)}}" >{{auth()->user()->company_name ?? auth()->user()->name}}</option>
                                    </select>
                                    <button type="button"  data-bs-toggle="tooltip" data-bs-original-title="@lang('Add New Beneficiary')" class="input-group-text beneficiary"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Contract Name')}}</label>
                                    <input name="title" id="title" class="form-control" autocomplete="off" placeholder="{{__('Enter Title')}}" type="text" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label required">{{__('Amount')}}</label>
                                    <input name="amount" id="amount" class="form-control" autocomplete="off" placeholder="{{__('Enter Amount')}}" type="number" step="any" required>
                                </div>
                            </div>
                        </div>

                        <div class="row form-group mb-3 mt-3">
                            <div class="col-md-4 mb-3">
                                <div class="form-label">{{__('Title')}}</div>
                                <input type="text" name="desc_title[]" class="form-control shadow-none itemname">
                            </div>
                            <div class="col-md-7 mb-3">
                                <div class="form-label">{{__('Text')}} <span class="pattern-help"><i class="fas fa-question-circle"></i></span></div>
                                <textarea type="text" rows="5" name="desc_text[]" class="form-control shadow-none itemvalue"></textarea>
                            </div>
                            <div class="col-md-1 mb-3">
                                <div class="form-label">&nbsp;</div>
                                <button type="button" class="btn btn-primary w-100 desc-add"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="description-extra-container"></div>

                        <input name="user_id" type="hidden" class="form-control" value="{{auth()->id()}}">

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary submit-btn w-100" >{{__('Submit')}}</button>
                        </div>
                        <div class="modal modal-blur fade" id="modal-pattern-help" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                <div class="modal-status bg-primary"></div>
                                <div class="modal-body text-center py-4">
                                    <i  class="fas fa-question-circle fa-3x text-primary mb-2"></i>
                                    <h3>{{__('How to write description')}}</h3>
                                    <div class="row form-group mb-3 mt-3">
                                        <div class="col-md-4 mb-3"><div class="form-label">{{__('Pattern name')}}</div></div>
                                        <div class="col-md-8 mb-3"><div class="form-label">{{__('Value')}}</div></div>
                                    </div>
                                    <div class="row form-group my-3">
                                        <div class="col-md-4 mb-3">
                                            <input type="text" class="form-control shadow-none itemname" readonly value="Amount" >
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <input type="number" id="amount2" step="any" name="amount2" class="form-control shadow-none itemvalue" readonly >
                                        </div>
                                    </div>
                                    <div class="default-contractor-pattern-container">
                                    </div>
                                    <div class="default-client-pattern-container">
                                    </div>
                                    <div class="row form-group mb-3 mt-3">
                                        <div class="col-md-4 mb-3">
                                            <input type="text" name="item[]" class="form-control shadow-none itemname"  >
                                        </div>
                                        <div class="col-md-7 mb-3">
                                            <input type="text" name="value[]" class="form-control shadow-none itemvalue"  >
                                        </div>
                                        <div class="col-md-1 mb-3">
                                            <button type="button" class="btn btn-primary w-100 add"><i class="fas fa-plus"></i></button>
                                        </div>
                                    </div>
                                    <div class="extra-container"></div>
                                    <div class="row form-group mt-3 text-start">
                                            <label class="form-label">{{__('Description')}}</label>
                                            <textarea name="description" class="form-control" readonly>{{__('Hello, {Client Name}.
I need {Amount} from you.')}}</textarea>
                                    </div>
                                    <div class="row form-group mt-3 text-start">
                                        <label class="form-label">{{__('Preview')}}</label>
                                        <textarea name="description" class="form-control" readonly>{{__('Hello, Aleksander.
I need 1000 from you.')}}</textarea>
                                    </div>
                                    <div class="row form-group mt-3 text-start">
                                        <button type="button" class="btn w-100" data-bs-dismiss="modal">Close</button>
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
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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
                        <div class="row">
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Beneficiary Type')}}</label>
                                <select id="bene_type" class="form-select" name="type" required>
                                    <option value="RETAIL"> {{__("RETAIL")}}</option>
                                    <option value="CORPORATE"> {{__("CORPORATE")}}</option>
                                </select>
                            </div>
                            <div id='retail' style="display: block" >

                                <div class="form-group mt-3 col-md-6">
                                    <label class="form-label required">{{__('First Name')}}</label>
                                    <input name="firstname" id="firstname" class="form-control" autocomplete="off" placeholder="{{__('John')}}" type="text" value="{{ old('firstname') }}" required>
                                </div>

                                <div class="form-group mt-3 col-md-6">
                                    <label class="form-label required">{{__('Last Name')}}</label>
                                    <input name="lastname" id="lastname" class="form-control" autocomplete="off" placeholder="{{__('Doe')}}" type="text" value="{{ old('lastname') }}" required>
                                </div>
                            </div>
                            <div id='corporate' style="display: none">
                                <div class="form-group mt-3 col-md-6">
                                    <label class="form-label required">{{__('Company Name')}}</label>
                                    <input name="company_name" id="company_name" class="form-control" autocomplete="off" placeholder="{{__('Tech LTD')}}" type="text" value="{{ old('company_name') }}" >
                                </div>
                            </div>
                            <hr class="my-3"/>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Email')}}</label>
                                <input name="email" id="email" class="form-control shadow-none" placeholder="{{__('user@email.com')}}" type="email" value="{{ old('email') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Address')}}</label>
                                <input name="address" id="address" class="form-control shadow-none" placeholder="{{__('Address')}}" type="text" value="{{ old('address') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Phone Number')}}</label>
                                <input name="phone" id="phone" class="form-control shadow-none" placeholder="{{__('+123456789')}}" type="text" value="{{ old('phone') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Registration NO')}}</label>
                                <input name="registration_no" id="registration_no" class="form-control shadow-none" placeholder="{{__('Registration NO')}}" type="text" value="{{ old('registration_no') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('VAT NO')}}</label>
                                <input name="vat_no" id="vat_no" class="form-control shadow-none" placeholder="{{__('VAT NO')}}" type="text" value="{{ old('vat_no') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Contact Person')}}</label>
                                <input name="contact_person" id="contact_person" class="form-control shadow-none" placeholder="{{__('Contact Person')}}" type="text" value="{{ old('contact_person') }}" required>
                            </div>
                            <hr class="my-3"/>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Bank Name')}}</label>
                                <input name="bank_name" id="bank_name" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Name')}}" type="text" value="{{ old('bank_name') }}" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Bank Address')}}</label>
                                <input name="bank_address" id="bank_address" class="form-control" autocomplete="off" placeholder="{{__('Enter Bank Address')}}" type="text" value="{{ old('bank_address') }}" min="1" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('SWIFT/BIC')}}</label>
                                <input name="swift_bic" id="swift_bic" class="form-control" autocomplete="off" placeholder="{{__('MEINATWW')}}" type="text" value="{{ old('swift_bic') }}" min="1" required>
                            </div>
                            <div class="form-group mt-3 col-md-6">
                                <label class="form-label required">{{__('Account/IBAN')}}</label>
                                <input name="account_iban" id="account_iban" class="form-control" autocomplete="off" placeholder="{{__('Enter Account/IBAN')}}" type="text" value="{{ old('account_iban') }}" min="1" required>
                            </div>
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
    $('#bene_type').on('change', function(){
        if ($('#bene_type').val() == 'RETAIL') {
            document.getElementById('retail').style.display = "block";
            $("#firstname").prop('required',true);
            $("#lastname").prop('required',true);
            document.getElementById('corporate').style.display = "none";
            $("#company_name").prop('required',false);
        }
        else if ($('#bene_type').val() == 'CORPORATE') {
            document.getElementById('retail').style.display = "none";
            $("#firstname").prop('required',false);
            $("#lastname").prop('required',false);
            document.getElementById('corporate').style.display = "block";
            $("#company_name").prop('required',true);
        }
    })
    var contractor_select_html = "";
    var client_select_html = "";
    $(document).ready(function() {
        contractor_select_html = $('#contractor').html();
        client_select_html = $('#client').html();
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
                <div class="col-md-8 mb-3">
                    <input type="text" name="default_value[]" class="form-control shadow-none itemvalue" value="${values[index]}" readonly >
                </div>
            </div>
            `;

        }
        $('.' + div_name).html(str_html);
    }
    $('#amount').on('change', function() {
        $('#amount2').val($('#amount').val());
    });
    $('.add').on('click',function(){
        $('.extra-container').append(`
            <div class="row form-group mb-3 mt-3">
                <div class="col-md-4 mb-3">
                    <input type="text" name="item[]" class="form-control shadow-none itemname" required>
                </div>
                <div class="col-md-7 mb-3">
                    <input type="text" name="value[]" class="form-control shadow-none itemvalue" required>
                </div>
                <div class="col-md-1 mb-3">
                    <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `);
    });

    $('.desc-add').on('click',function(){
        $('.description-extra-container').append(`
            <div class="row form-group mb-3 mt-3">
                <div class="col-md-4 mb-3">
                    <input type="text" name="desc_title[]" class="form-control shadow-none itemname">
                </div>
                <div class="col-md-7 mb-3">
                    <textarea type="text" rows="5" name="desc_text[]" class="form-control shadow-none itemvalue"></textarea>
                </div>
                <div class="col-md-1 mb-3">
                    <button type="button" class="btn btn-danger w-100 remove"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `);
    });

    $(document).on('click','.remove',function () {
        $(this).closest('.row').remove()
    })
    $('.beneficiary').on('click',function() {
        $('#modal-success').modal('show')
    })
    $('.pattern-help').on('click', function(){
        $("#modal-pattern-help").modal('show')
    })
</script>
@endpush
