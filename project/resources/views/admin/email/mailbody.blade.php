<div
  style="
    background-color: #e9e9e9;
    padding: 10px 0px;
    font-family: sans-serif;
  "
>
  <table
    width="100%"
    style="max-width: 600px"
    cellpadding="0"
    cellspacing="0"
    border="0"
    align="center"
  >
    <tbody>
      <tr>
        <td>
          <div style="width: 100%; max-width: 600px">
            <table
              cellpadding="0"
              cellspacing="0"
              border="0"
              align="center"
              style="
                  margin-top: 20px;
                  margin-bottom: 20px;
              "
            >
              <tbody>
                <tr>
                  <td>
                    <img
                      src="{{asset('assets/images/'.$logo)}}"
                      alt="mtsystem-brand"
                      height="50"
                      style="margin-right: 20px"
                    />
                  </td>
                  <td>
                    <h2 style="margin: 0;">{{$title}}</h2>
                  </td>
                </tr>
              </tbody>
            </table>
            <div
              style="
                padding: 20px;
                background-color: white;
                border: 1px solid #d7d7d7;
              "
            >
            {!! $email_body !!}
            </div>
            <div
              style="
                padding: 20px;
                font-size: 12px;
                text-align: center;
                line-height: 30px;
                color: #767676;
              "
            >
              <div>
                <a
                  style="color: inherit"
                  href="{{url('/privacy')}}"
                  >Privacy &amp; Policy</a
                >
                |
                <a style="color: inherit" href="{{url('/about')}}"
                  >About Us</a
                >
              </div>
              <div>{{$street}}</div>
              <div>{!! $copyright !!}</div>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>