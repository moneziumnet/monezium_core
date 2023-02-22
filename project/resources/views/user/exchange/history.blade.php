@extends('layouts.user')



@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <div class="page-pretitle">
          {{__('Overview')}}
        </div>
        <h2 class="page-title">
          {{__('Exchange History')}}
        </h2>
      </div>

      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">

          <form action="">
            <div class="input-group">
                <input class="form-control shadow-none" type="text" pattern="[^À-ž()/><\][\\;&$@!|]+" placeholder="{{__('Transaction id')}}" name="search" value="{{$search ?? ''}}">
                <button type="submit" class="input-group-text bg-primary text-white border-0"><i class="fas fa-search"></i></button>
            </div>
          </form>
          <a href="{{route('user.exchange.money')}}" class="btn btn-primary"><i class="fas fa-backward me-2"></i> {{__('Back')}}</a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
      <div class="row row-deck row-cards">

          <div class="col-12">
              <div class="card">
                  <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped">
                      <thead>
                        <tr>
                          <th>{{__('Transaction Id')}}</th>
                          <th>{{__('From Currency')}}</th>
                          <th>{{__('From Amount')}}</th>
                          <th>{{__('To Currency')}}</th>
                          <th>{{__('To Amount')}}</th>
                          <th>{{__('Charge')}}</th>
                          <th>{{__('Date')}}</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($exchanges as $item)
                          <tr>
                            <td data-label="{{__('Transaction Id')}}">{{@$item->trnx}}</td>
                            <td data-label="{{__('From Currency')}}">{{@$item->fromCurr->code}}</td>
                            <td data-label="{{__('From Amount')}}">{{amount($item->from_amount,$item->fromCurr->type,2)}} {{@$item->fromCurr->code}}</td>
                            <td data-label="{{__('To Currency')}}">{{@$item->toCurr->code}}</td>
                            <td data-label="{{__('To Amount')}}">{{amount($item->to_amount,$item->toCurr->type,2)}} {{@$item->toCurr->code}}</td>
                            <td data-label="{{__('Charge')}}">{{amount($item->charge,$item->fromCurr->type,2)}} {{$item->fromCurr->code}}</td>
                            <td data-label="{{__('Date')}}">{{dateFormat($item->created_at)}}</td>
                          </tr>
                          @empty
                          <tr>
                              <td class="text-center" colspan="12">{{__('No data found!')}}</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                  @if ($exchanges->hasPages())
                      <div class="card-footer">
                          {{$exchanges->links()}}
                      </div>
                  @endif
              </div>
          </div>
      </div>
  </div>
</div>
@endsection
