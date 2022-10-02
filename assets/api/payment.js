var mt_window = null;
var mt_payment_element = document.getElementById('mt-payment-system');
var mt_payment_interval_id = window.setInterval(check_mt_window, 500);

mt_payment_element.addEventListener("click", function() {
  if(mt_window){
    mt_window.focus()
  } else {
    const mt_sitekey = mt_payment_element.getAttribute('data-sitekey');
    const device_width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    const window_features = `left=${device_width / 2 - 240},top=100,width=480,height=600`;

    mt_window = window.open(
      "http://lion.saas.test/genius/access?" + mt_sitekey, "", window_features
    );
  }
});

function check_mt_window() {
  if (mt_window && mt_window.closed) {
    window.clearInterval(mt_payment_interval_id);
    mt_window = null;
  }
}

window.addEventListener("message", function (event) {
  const response = event.data;
  switch(response.type) {
    case 'mt_payment_success':
      const mt_payment_success = window[mt_payment_element.getAttribute("fn-success")];
      mt_payment_success(response.payload);
      break;
    case 'mt_payment_error':
      const mt_payment_error = window[mt_payment_element.getAttribute("fn-error")];
      mt_payment_error(response.payload);
      break;
    default:
      break;
  } 
});var mt_window = null;
var mt_payment_element = document.getElementById('mt-payment-system');
var mt_payment_interval_id = window.setInterval(check_mt_window, 500);

mt_payment_element.addEventListener("click", function() {
  if(mt_window){
    mt_window.focus()
  } else {
    const mt_sitekey = mt_payment_element.getAttribute('data-sitekey');
    const device_width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
    const window_features = `left=${device_width / 2 - 240},top=100,width=480,height=600`;

    mt_window = window.open(
      "http://lion.saas.test/genius/access?" + mt_sitekey, "", window_features
    );
  }
});

function check_mt_window() {
  if (mt_window && mt_window.closed) {
    window.clearInterval(mt_payment_interval_id);
    mt_window = null;
  }
}

window.addEventListener("message", function (event) {
  const response = event.data;
  switch(response.type) {
    case 'mt_payment_success':
      const mt_payment_success = window[mt_payment_element.getAttribute("onsuccess")];
      mt_payment_success(response.payload);
      break;
    case 'mt_payment_error':
      const mt_payment_error = window[mt_payment_element.getAttribute("onerror")];
      mt_payment_error(response.payload);
      break;
    default:
      break;
  } 
});