@extends('layouts.user')

@section('styles')

@endsection

@section('contents')
<div class="container-xl">
  <div class="page-header d-print-none">
    <div class="row align-items-center">
      <div class="col">
        <h2 class="page-title">
          {{__('KYC Form')}}
        </h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
      <div class="row row-cards">
          <div class="col-12">
              <div class="card p-5">
                  <div class="gocover" style="background: url({{ asset('assets/images/'.$gs->loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);"></div>
                      @includeIf('includes.flash')
                      @if (auth()->user()->kyc_method == 'manual')

                        <form action="{{route('user.kyc.submit')}}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @foreach ($userForms as $field)
                                @if ($field->type == 1 || $field->type == 3 )
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label {{$field->required == 1 ? 'required':'Optional'}}">@lang($field->label)</label>
                                    @if ($field->type == 1)
                                    <input pattern="[^()/><\][;!|]+" type="text" name="{{strtolower(str_replace(' ', '_', $field->label))}}" class="form-control" autocomplete="off" placeholder="@lang($field->label)" min="1" {{$field->required == 1 ? 'required':'Optional'}}>
                                    @else
                                    <textarea class="form-control" name="{{strtolower(str_replace(' ', '_', $field->label))}}" placeholder="@lang($field->label)"></textarea>
                                    @endif
                                </div>
                                @elseif($field->type == 2)
                                <div class="form-group mb-3 mt-3">
                                    <label class="form-label {{$field->required == 1 ? 'required':'Optional'}}">@lang($field->label)</label>
                                    <input type="file" name="{{strtolower(str_replace(' ', '_', $field->label))}}" class="form-control" autocomplete="off" {{$field->required == 1 ? 'required':'Optional'}}>
                                </div>
                                @endif
                            @endforeach

                            {{-- <label class="form-check">
                                    <input class="form-check-input shadow-none" type="checkbox" name="sendlink" checked>
                                    <span class="form-check-label">@lang('Send online selfie link to email')</span>
                            </label> --}}
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary w-100">{{__('Submit')}}</button>
                            </div>
                        </form>
                      @else
                      <div id="sumsub-websdk-container"></div>
                      @endif
                 </div>
          </div>
      </div>
  </div>
</div>

<form action="{{route('user.kyc.status')}}" method="POST" enctype="multipart/form-data" id="status_form">
    @csrf
    <input type="hidden" name="id" value="{{auth()->id()}}">
    <input type="hidden" name="status" id="kyc_status" value="2">
</form>
@endsection

@push('js')
<script src = "https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
<script language="JavaScript">
    function launchWebSdk(accessToken, applicantEmail, applicantPhone) {
    let snsWebSdkInstance = snsWebSdk.init(
            accessToken,
            () => this.getNewAccessToken()
        )
        .withConf({
            lang: 'en',
            email: applicantEmail,
            phone: applicantPhone,
            i18n: {"document":{"subTitles":{"IDENTITY": "Upload a document that proves your identity"}}},
            onMessage: (type, payload) => {
                console.log('WebSDK onMessage', type, payload)
            },
            uiConf: {
                customCssStr: ":root {\n  --black: #000000;\n   --grey: #F5F5F5;\n  --grey-darker: #B2B2B2;\n  --border-color: #DBDBDB;\n}\n\np {\n  color: var(--black);\n  font-size: 16px;\n  line-height: 24px;\n}\n\nsection {\n  margin: 40px auto;\n}\n\ninput {\n  color: var(--black);\n  font-weight: 600;\n  outline: none;\n}\n\nsection.content {\n  background-color: var(--grey);\n  color: var(--black);\n  padding: 40px 40px 16px;\n  box-shadow: none;\n  border-radius: 6px;\n}\n\nbutton.submit,\nbutton.back {\n  text-transform: capitalize;\n  border-radius: 6px;\n  height: 48px;\n  padding: 0 30px;\n  font-size: 16px;\n  background-image: none !important;\n  transform: none !important;\n  box-shadow: none !important;\n  transition: all 0.2s linear;\n}\n\nbutton.submit {\n  min-width: 132px;\n  background: none;\n  background-color: var(--black);\n}\n\n.round-icon {\n  background-color: var(--black) !important;\n  background-image: none !important;\n}"
            },
            onError: (error) => {
                console.error('WebSDK onError', error)
            },
        })
        .withOptions({ addViewportTag: false, adaptIframeHeight: true})
        .on('stepCompleted', (payload) => {
            console.log('stepCompleted', payload)
        })
        .on('onError', (error) => {
            console.log('onError', payload)
        })
        .onMessage((type, payload) => {
            console.log('onMessage', type, payload)
            if (payload.reviewResult.reviewAnswer == "GREEN") {
                $('#kyc_status').val(1);
                $('#status_form').submit();
                console.log('onMessage::::', payload.reviewResult.reviewAnswer)
            }
            if (payload.reviewResult.reviewAnswer == "RED") {
                $('#kyc_status').val(2);
                $('#status_form').submit();
                console.log('onMessage::::', payload.reviewResult.reviewAnswer)
            }
        })
        .build();
    snsWebSdkInstance.launch('#sumsub-websdk-container')
}

function getNewAccessToken() {
  return Promise.resolve()
}

    launchWebSdk('{{$token}}','{{auth()->user()->email}}', '{{auth()->user()->phone}}')
</script>
@endpush
