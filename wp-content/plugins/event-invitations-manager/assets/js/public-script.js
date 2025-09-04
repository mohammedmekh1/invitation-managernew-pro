jQuery(document).ready(function($) {
    'use strict';

    // RSVP Handler
    $('#rsvp-buttons .icon').on('click', function(e) {
        var $this = $(this);
        if ($this.hasClass('disabled')) {
            return; // Do nothing if buttons are disabled
        }

        var rsvpStatus = $this.data('rsvp');

        // Disable all buttons to prevent multiple clicks
        $('#rsvp-buttons .icon').addClass('disabled');

        $.ajax({
            url: eim_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'eim_handle_rsvp',
                nonce: eim_ajax.nonce,
                guest_id: eim_ajax.guest_id,
                rsvp_status: rsvpStatus
            },
            success: function(response) {
                if (response.success) {
                    var successMsg = 'شكراً لك على استجابتك!';
                    if (response.data.qr_code_url) {
                        successMsg = 'شكراً لك! رمز الـ QR الخاص بك بالأسفل.';
                        // Display the QR code
                        var qrImg = $('<img>').attr('src', response.data.qr_code_url).attr('alt', 'رمز QR الخاص بك');
                        $('#qr-code-container').html(qrImg);
                        // Here you would trigger a modal
                        alert('تم إنشاء رمز QR! يمكنك تحميله أو العثور عليه في الصفحة.');
                    }
                    $('#rsvp-message').text(successMsg).show();
                } else {
                    $('#rsvp-message').text('حدث خطأ. الرجاء المحاولة مرة أخرى.').show();
                    // Re-enable buttons if something went wrong
                    $('#rsvp-buttons .icon').removeClass('disabled');
                }
            },
            error: function() {
                $('#rsvp-message').text('حدث خطأ في الشبكة. الرجاء المحاولة مرة أخرى.').show();
                $('#rsvp-buttons .icon').removeClass('disabled');
            }
        });
    });

    // Info Card Toggler
    $('.info-toggle').on('click', function() {
        var target = $(this).data('target');
        $('.icon-info').not(target).slideUp();
        $(target).slideToggle();
    });

    // Greeting Form Handler
    $('#greeting-form').on('submit', function(e) {
        e.preventDefault();
        var author = $('#greeting-author').val();
        var message = $('#greeting-message').val();

        if (!author || !message) {
            alert('Please fill in your name and message.');
            return;
        }

        $.ajax({
            url: eim_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'eim_handle_greeting',
                nonce: eim_ajax.nonce,
                guest_id: eim_ajax.guest_id,
                author: author,
                message: message
            },
            success: function(response) {
                if (response.success) {
                    // Add new greeting to the top of the list
                    var newGreeting = '<div class="greeting" style="display:none;"><strong>' + author + ':</strong><p>' + message + '</p></div>';
                    $('#greetings-container').prepend(newGreeting);
                    $('#greetings-container .greeting:first').slideDown();

                    // Clear the form
                    $('#greeting-author').val('');
                    $('#greeting-message').val('');
                } else {
                    alert(response.data.message || 'An error occurred.');
                }
            },
            error: function() {
                alert('A network error occurred.');
            }
        });
    });

});
