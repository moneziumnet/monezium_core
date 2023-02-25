<li class="list-group-item d-flex justify-content-between">@lang('Institution Fee')<span>{{amount($tran_fee,1,2)}} {{$def_code}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Bank Fee')<span>{{amount($bank_fee,1,2)}} {{$def_code}}</span></li>
<li class="list-group-item d-flex justify-content-between">@lang('Profit')<span >{{amount(($tran_fee - $bank_fee),1,2)}} {{$def_code}}</span></li>

