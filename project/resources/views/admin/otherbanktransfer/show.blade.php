@extends('layouts.admin')

@section('content')

<div class="card">
	<div class="d-sm-flex align-items-center justify-content-between py-3">
	<h5 class=" mb-0 text-gray-800 pl-3">{{ __(' Bank Transfer') }} <a class="btn btn-primary btn-rounded btn-sm" href="{{route('admin.other.banks.transfer.index')}}"><i class="fas fa-arrow-left"></i> {{ __('Back') }}</a></h5>
	<ol class="breadcrumb m-0 py-0">
		<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
		<li class="breadcrumb-item"><a href="{{ route('admin-user-banks',$data->user_id) }}">{{ __('Bank Transfer Details') }}</a></li>
	</ol>
	</div>
</div>


<div class="row justify-content-center mt-3">
  <div class="col-lg-10">
    <div class="card mb-4">
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="special-box">
                        <div class="heading-area">
                            <h4 class="title">
                            {{__('Transfer Details')}}
                            </h4>
                        </div>
                        <div class="table-responsive-sm">
                            <table class="table">
                                <tbody>
                                <tr>
                                    <th class="45%" width="45%">{{__('Bank Name')}}</th>
                                    <td width="10%">:</td>
                                    <td class="45%" width="45%">{{$data->bank->title}}</td>
                                </tr>

                                <tr>
                                    <th width="45%">{{__('Account Name')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$data->beneficiary->name}}</td>
                                </tr>

                                <tr>
                                    <th width="45%">{{__('Account Number')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$data->beneficiary->account_number}}</td>
                                </tr>

                                <tr>
                                    <th width="45%">{{__('Beneficiary Address')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$data->beneficiary->address}}</td>
                                </tr>
                                <tr>
                                    <th width="45%">{{__('Bank Address')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$data->beneficiary->bank_address}}</td>
                                </tr>
                                <tr>
                                    <th width="45%">{{__('Account IBAN')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$data->beneficiary->account_iban}}</td>
                                </tr>
                                <tr>
                                    <th width="45%">{{__('SWIFT/BIC')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$data->beneficiary->swift_bic}}</td>
                                </tr>
                                <tr>
                                    <th width="45%">{{__('Customer Name')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$bankaccount->user->name}}</td>
                                </tr>
                                <tr>
                                    <th width="45%">{{__('Customer Email')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$bankaccount->user->email}}</td>
                                </tr>
                                <tr>
                                    <th width="45%">{{__('Customer Bank Iban')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$bankaccount->iban}}</td>
                                </tr>

                                <tr>
                                    <th width="45%">{{__('Customer Bank Swift')}}</th>
                                    <td width="10%">:</td>
                                    <td width="45%">{{$bankaccount->swift}}</td>
                                </tr>

                                @if ($data->document)
                                    <tr>
                                        <th width="45%">{{__('Document')}}</th>
                                        <td width="10%">:</td>
                                        <td width="45%">
                                            @php
                                                $arr_file_name = explode('.', $data->document);
                                                $extension = $arr_file_name[count($arr_file_name) - 1];
                                            @endphp
                                            @if(in_array($extension, array('doc','docx')))
                                            <a href ="https://docs.google.com/gview?url={{asset('assets/doc/'.$data->document)}}">{{$data->document}}</a>
                                            @else
                                            <a href ="{{asset('assets/doc/'.$data->document)}}" >{{$data->document}}</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endif

                                @foreach (json_decode($data->beneficiary->details,true) as $key=>$value)
                                    @if ($value[1] == 'file')
                                        <tr>
                                            <th width="45%">{{$key}}</th>
                                            <td width="10%">:</td>
                                            <td width="45%"><img src="{{asset('assets/images/'.$value[0])}}" class="img-thumbnail"></td>
                                        </tr>
                                    @else
                                        <tr>
                                            <th width="45%">{{$key}}</th>
                                            <td width="10%">:</td>
                                            <td width="45%">{{ $value[0] }}</td>
                                        </tr>
                                    @endif
                                @endforeach

                                </tbody>
                            </table>
                        </div>



                    </div>

                </div>
            </div>
        </div>
      </div>
  </div>
</div>


@endsection


@section('scripts')

@endsection


