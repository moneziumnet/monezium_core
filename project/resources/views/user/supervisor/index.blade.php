@extends('layouts.user')

@push('css')
<style>
.form-control {
    /* Other styling... */
    width: auto;
    display: inherit;
    margin-bottom: 15px;

}
</style>
@endpush

@section('contents')
<div class="container-fluid">
    <div class="page-header d-print-none">
      <div class="row align-items-center">
        <div class="col-md-12 d-flex justify-content-between">
          <h2 class="page-title">
            {{__('Charge Fee')}}
          </h2>
          <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#editModal" class="btn btn-primary" > {{__("All Edit")}}</a>
        </div>
      </div>
    </div>
</div>


<div class="page-body">
    <div class="container-fluid">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="tab-pane fade show p-3 active" id="modules" role="tabpanel" aria-labelledby="modules-tab">
                        <div class="">
                        <table id="geniustable" class="table table-hover  dt-responsive cell-border text-center row-border table-bordered" cellspacing="0" width="100%">
                            <thead class="thead-light">
                             <tr>
                              <th colspan="2" >{{__('Fee')}}</th>
                              <th colspan="2" >{{__('Golbal')}}</th>
                              <th colspan="2" >
                                @if (check_user_type(4))
                                {{__('Supervisor')}}</th>
                                @elseif (DB::table('managers')->where('manager_id', auth()->id())->first())
                                {{__('Manager')}}</th>
                                @endif
                              <th rowspan="2">{{__('Action')}}</th>
                             </tr>
                             <tr>
                                <th >{{__('Name')}}</th>
                                <th>{{__('Type')}}</th>
                                <th >{{__('Percent')}}</th>
                                <th>{{__('Fixed')}}</th>
                                <th >{{__('Percent')}}</th>
                                <th>{{__('Fixed')}}</th>
                            </tr>
                            </thead>
                        </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-success" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-status bg-primary"></div>
        <div class="modal-body py-4">
        <div class=" text-center">
            <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
        </div>
        <ul class="list-group mt-2">

        </ul>
        </div>
        <div class="modal-footer">
        </div>
    </div>
    </div>
  </div>

  <div class="modal modal-blur fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-body text-center m-2">
        <i  class="fas fa-info-circle fa-3x text-primary mb-2"></i>
        <h3>@lang('Plan Details')</h3>
        <form method="post" action="{{route('user.supervisor.charge.all.update', $data->id)}}" class="mt-3">
          @csrf
          <div class="row">
            <div class="col-md-4">
              <label class="h5">{{__("Charge Name")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("Percent Charge (%)")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("Fixed Charge")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("From")}}</label>
            </div>
            <div class="col-md-2">
              <label class="h5">{{__("Till")}}</label>
            </div>
          </div>
          <div style="max-height: 600px;overflow-y: scroll; overflow-x: hidden;">
            @foreach ($global_list as $item)
            <div class="row border-bottom mt-2 p-2 align-items-center">
              <div class="form-group col-md-4 ">
                <label class="h6">{{$item->name}}</label>
              </div>
              <input  type="hidden" name="name_{{$item->id}}" class="form-control" value="{{$item->name}}">
              <input  type="hidden" name="user_id_{{$item->id}}" class="form-control" value="{{$data->id}}">
              <input  type="hidden" name="slug_{{$item->id}}" class="form-control" value="{{$item->slug}}">
              @php
                $customplan =  DB::table('charges')->where('user_id',$data->id)->where('plan_id', 0)->where('name', $item->name)->first();
                if($customplan) {
                  $charge = $customplan->data;
                }
                else {
                  $charge = json_encode($item->data);
                }
                $item_key = $item->id;
              @endphp
              @foreach (json_decode($charge) as $key => $value )
              @switch($key)
                @case('percent_charge')
                  <div class="form-group col-md-2 ">
                    <input type="number" step="any" name="{{$key}}_{{$item_key}}" class="form-control" style="width: 100%;" value="{{@$value}}">
                  </div>
                  @break
                @case('fixed_charge')
                  <div class="form-group col-md-2 ">
                    <input type="number" step="any" name="{{$key}}_{{$item_key}}" class="form-control" style="width: 100%;" value="{{@$value}}">
                  </div>
                  @break
                @case('from')
                  <div class="form-group col-md-2 ">
                    <input type="number" step="any" name="{{$key}}_{{$item_key}}" class="form-control" style="width: 100%;" value="{{@$value}}">
                  </div>
                  @break
                @case('till')
                  <div class="form-group col-md-2 ">
                    <input type="number" step="any" name="{{$key}}_{{$item_key}}" class="form-control" style="width: 100%;" value="{{@$value}}">
                  </div>
                  @break
              
                @default
                  
              @endswitch
              @endforeach
              
            </div>
            @endforeach
          </div>
            <button type="submit" id="submit-btn" class="mt-3 btn btn-primary w-100">{{ __('Save') }}</button>
        </form>
        </div>
    </div>
    </div>
  </div>
<!--Row-->
@endsection
@push('js')
<script src="{{asset('assets/admin/js/plugin.js')}}"></script>
<script type="text/javascript">
    "use strict";

    var table = $('#geniustable').DataTable({
           ordering: false,
           processing: true,
           serverSide: true,
           searching: false,
           bPaginate: false,
           scrollX:        false,
           ajax: '{{ route('user-pricingplan-datatables',$data->id) }}',
           columns: [
                { data: 'name', name: 'name' },
                { data: 'type', name: 'type' },
                { data: 'percent', name: 'percent' },
                { data: 'fixed', name:'fixed' },
                { data: 'percent_customer', name: 'percent_customer' },
                { data: 'fixed_customer', name:'fixed_customer' },
                { data: 'action', name: 'action' },
            ],
        });

        function getDetails (id=null)
        {
            if (id) {
                var url = "{{url('user/pricingplan/edit/')}}"+'/'+id
                $.get(url,function (res) {
                  if(res == 'empty'){
                    $('.list-group').html("<p>@lang('No details found!')</p>")
                  }else{
                    $('.list-group').html(res)
                  }
                });
            }

              $('#modal-success').modal('show')
        }

        function createDetails(id)
        {
                var url = "{{url('user/pricingplan/create/')}}"+'/'+'{{$data->id}}'+'/'+`${id}`
                console.log(url);
                $.get(url,function (res) {
                  if(res == 'empty'){
                    $('.list-group').html("<p>@lang('No details found!')</p>")
                  }else{
                    $('.list-group').html(res)
                  }
                });
                $('#modal-success').modal('show')
        }
        $('.closed').click(function() {
            $('#modal-success').modal('hide');
        });

</script>

@endpush
@section('scripts')



@endsection
