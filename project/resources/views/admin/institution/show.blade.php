@extends('layouts.load')
@section('content')

<div class="content-area no-padding">
    <div class="add-product-content1">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">

                        <div class="table-responsive show-table">
                            <table class="table">
                                <tr>
                                    <th>{{ __("Institution ID#") }}</th>
                                    <td>{{$data->id}}</td>
                                </tr>
                                <tr>
                                    <th>{{ __("Institution Photo") }}</th>
                                    <td>
                                        <img src="{{ $data->photo ? asset('assets/images/admins/'.$data->photo):asset('assets/images/noimage.png')}}" alt="{{ __("No Image") }}">

                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __("Institution Name") }}</th>
                                    <td>{{$data->name}}</td>
                                </tr>
                                <tr>
                                    <th>{{ __("Institution Email") }}</th>
                                    <td>{{$data->email}}</td>
                                </tr>
                                <tr>
                                    <th>{{ __("Institution Phone") }}</th>
                                    <td>{{$data->phone}}</td>
                                </tr>
                                <tr>
                                    <th>{{ __("Joined") }}</th>
                                    <td>{{$data->created_at->diffForHumans()}}</td>
                                </tr>
                            </table>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection