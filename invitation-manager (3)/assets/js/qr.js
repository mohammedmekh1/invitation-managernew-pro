(function(){
  function onScanSuccess(decodedText, decodedResult) {
    var resEl = document.getElementById('scan-result');
    resEl.innerHTML = 'جارٍ التحقق...';
    var form = new FormData();
    form.append('action','im_verify_qr');
    form.append('_ajax_nonce', IM_QR.nonce);
    form.append('token', decodedText);

    fetch(IM_QR.url, { method:'POST', body: form })
      .then(r=>r.json())
      .then(json=>{
        if(json.success){
          resEl.innerHTML = '<span class="success">تم التأكيد ✅</span> — الضيف: ' + (json.data.guest ? json.data.guest.name : '');
        } else {
          resEl.innerHTML = '<span class="error">رمز غير صالح ❌</span>';
        }
      }).catch(()=>{
        resEl.innerHTML = '<span class="error">حدث خطأ في الخادم</span>';
      });
  }

  function onScanFailure(error) {
    // تجاهل الأخطاء الطفيفة
  }

  document.addEventListener('DOMContentLoaded', function(){
    if(typeof Html5Qrcode === 'undefined') return;
    var html5QrcodeScanner = new Html5QrcodeScanner(
      "reader", { fps: 10, qrbox: 250 }, /* verbose= */ false);
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
  });
})();
