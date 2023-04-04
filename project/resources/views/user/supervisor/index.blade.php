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
        <div class="col">
          <h2 class="page-title">
            {{__('Charge Fee')}}
          </h2>
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
                              <th rowspan="2" >{{__('Fee Type')}}</th>
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
