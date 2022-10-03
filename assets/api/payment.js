var mt_window = null;
var mt_payment_element = document.getElementById('mt-payment-system');
var mt_payment_interval_id = null;
var mt_check_window = function () {
  if (mt_window && mt_window.closed) {
    window.clearInterval(mt_payment_interval_id);
    mt_window = null;
  }
}

mt_payment_element.addEventListener("click", function() {
  if(mt_window){
    mt_window.focus()
  } else {
    const mt_sitekey = mt_payment_element.getAttribute('data-sitekey');
    const mt_currency = mt_payment_element.getAttribute('data-currency');
    const mt_amount = mt_payment_element.getAttribute('data-amount');
    const device_width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    const window_features = `left=${device_width / 2 - 240},top=100,width=480,height=600`;

    mt_window = window.open(
      `http://lion.saas.test/genius/access?site_key=${mt_sitekey}&&currency=${mt_currency}&&amount=${mt_amount}`, "", window_features
    );

    mt_payment_interval_id = window.setInterval(mt_check_window, 500);
  }
});

window.addEventListener("message", function (event) {
  const response = event.data;
  switch(response.type) {
    case 'mt_payment_success':
      const mt_payment_success = mt_payment_element.getAttribute("fn-success");
      window[mt_payment_success](response.payload);
      break;
    case 'mt_payment_error':
      const mt_payment_error = mt_payment_element.getAttribute("fn-error");
      window[mt_payment_error](response.payload);
      break;
    default:
      break;
  } 
});