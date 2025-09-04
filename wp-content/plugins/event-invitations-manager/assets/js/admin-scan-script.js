jQuery(document).ready(function($) {
    'use strict';

    var video = document.getElementById("scanner-video");
    var canvasElement = document.getElementById("scanner-canvas");
    var canvas = canvasElement.getContext("2d");
    var resultDiv = document.getElementById("scan-result");
    var resultTitle = document.getElementById("result-title");
    var resultMessage = document.getElementById("result-message");
    var lastScannedCode = null;
    var scanTimeout = null;

    // Use facingMode: "environment" to prefer the rear camera
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } }).then(function(stream) {
        video.srcObject = stream;
        video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
        video.play();
        requestAnimationFrame(tick);
    });

    function tick() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvasElement.height = video.videoHeight;
            canvasElement.width = video.videoWidth;
            canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
            var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
            var code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });

            if (code && code.data !== lastScannedCode) {
                lastScannedCode = code.data;
                handleCode(code.data);

                // Pause scanning for a few seconds to prevent multiple scans of the same code
                clearTimeout(scanTimeout);
                scanTimeout = setTimeout(function() {
                    lastScannedCode = null;
                    resultDiv.style.display = 'none';
                }, 3000); // 3 seconds
            }
        }
        requestAnimationFrame(tick);
    }

    function handleCode(code) {
        // AJAX call to verify the guest
        $.ajax({
            url: ajaxurl, // ajaxurl is defined by WordPress in the admin
            type: 'POST',
            data: {
                action: 'eim_verify_guest',
                nonce: eim_admin_scan_nonce, // This will be localized
                unique_code: code
            },
            success: function(response) {
                resultDiv.classList.remove('success', 'error');
                if (response.success) {
                    resultDiv.classList.add('success');
                    resultTitle.textContent = 'تم بنجاح!';
                    resultMessage.textContent = 'المدعو: ' + response.data.guest_name + '. الحالة: ' + response.data.message;
                } else {
                    resultDiv.classList.add('error');
                    resultTitle.textContent = 'خطأ!';
                    resultMessage.textContent = response.data.message;
                }
                resultDiv.style.display = 'block';
            },
            error: function() {
                resultDiv.classList.remove('success', 'error');
                resultDiv.classList.add('error');
                resultTitle.textContent = 'خطأ!';
                resultMessage.textContent = 'حدث خطأ في الشبكة.';
                resultDiv.style.display = 'block';
            }
        });
    }
});
