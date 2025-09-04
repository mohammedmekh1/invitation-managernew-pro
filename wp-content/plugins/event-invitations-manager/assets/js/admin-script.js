jQuery(document).ready(function($) {
    'use strict';

    // Handle the copy button for invitation links
    $('.eim-copy-button').on('click', function(e) {
        e.preventDefault();
        var targetInput = $($(this).data('target'));

        if (targetInput.length > 0) {
            targetInput.select();
            try {
                // Modern browsers
                navigator.clipboard.writeText(targetInput.val()).then(function() {
                    // Success feedback
                    var originalText = e.target.textContent;
                    e.target.textContent = 'Copied!';
                    setTimeout(function() {
                        e.target.textContent = originalText;
                    }, 2000);
                }).catch(function(err) {
                    // Error feedback
                    console.error('Could not copy text: ', err);
                });
            } catch (err) {
                // Fallback for older browsers
                try {
                    document.execCommand('copy');
                     var originalText = e.target.textContent;
                    e.target.textContent = 'Copied!';
                    setTimeout(function() {
                        e.target.textContent = originalText;
                    }, 2000);
                } catch (err) {
                    console.error('Fallback copy failed: ', err);
                }
            }
        }
    });
});
