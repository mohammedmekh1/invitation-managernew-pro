<?php
/**
 * Provide an admin area view for the QR code scanner page.
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div id="scanner-container" style="max-width: 500px; margin: 20px 0;">
        <p>Point a guest's QR code at the camera.</p>
        <video id="scanner-video" playsinline style="width: 100%; height: auto; border: 1px solid #ccc;"></video>
        <canvas id="scanner-canvas" style="display: none;"></canvas>
    </div>

    <div id="scan-result" style="padding: 15px; border-radius: 4px; display: none;">
        <h2 id="result-title"></h2>
        <p id="result-message"></p>
    </div>
</div>
