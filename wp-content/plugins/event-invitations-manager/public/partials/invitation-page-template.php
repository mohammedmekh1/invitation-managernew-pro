<?php
/**
 * The template for displaying the invitation page.
 */

require_once plugin_dir_path( __FILE__ ) . '../../includes/lib/qrcode.php';
global $wpdb;

// Get the invitation code from the query variables
$invitation_code = get_query_var('invitation_code');
$guest = null;
$occasion = null;
$greetings = [];

if ( ! empty( $invitation_code ) ) {
    $guest_table = $wpdb->prefix . 'eim_guests';
    $guest = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $guest_table WHERE unique_code = %s", $invitation_code ) );

    if ( $guest ) {
        $occasion_table = $wpdb->prefix . 'eim_occasions';
        $occasion = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $occasion_table WHERE id = %d", $guest->occasion_id ) );

        $greetings_table = $wpdb->prefix . 'eim_greetings';
        $greetings = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $greetings_table WHERE guest_id = %d ORDER BY created_at DESC", $guest->id ) );
    }
}

$rsvp_done = $guest && $guest->rsvp_status !== 'pending';

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php
        $primary_color = get_option('eim_option_primary_color', '#006A4E');
    ?>
    <style>
        body { font-family: sans-serif; background-color: #f0f2f5; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 2em; color: #444; }
        .invitation-image { width: 100%; height: auto; border-radius: 8px; margin-bottom: 20px; }
        .card { background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 20px; text-align: center; }
        .icon-group { display: flex; justify-content: space-around; }
        .icon { cursor: pointer; padding: 10px; border-radius: 50%; transition: background-color 0.3s; }
        .icon:hover { background-color: #e0e0e0; }
        .icon.disabled { cursor: not-allowed; opacity: 0.5; }
        .icon-info { display: none; margin-top: 15px; padding: 10px; background: #fff; border: 1px solid #ccc; }
        #qr-code-container { text-align: center; margin-top: 20px; }
        .greetings-form textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .greetings-list .greeting { border-bottom: 1px solid #eee; padding: 10px 0; }
        .modal { /* Basic modal styles */ }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="container">

    <?php if ( $guest && $occasion ) : ?>
        <div class="header">
            <h1><?php echo esc_html( $occasion->welcome_message ); ?>, <?php echo esc_html( $guest->name ); ?>!</h1>
        </div>

        <img src="<?php echo esc_url( $occasion->invitation_image ); ?>" alt="Invitation" class="invitation-image">

        <!-- QR Code Placeholder -->
        <div id="qr-code-container">
            <?php if ( $guest->rsvp_status === 'attending' && !empty($guest->qr_code_url) ) : ?>
                <a href="<?php echo esc_url( $guest->qr_code_url ); ?>" download="invitation-qr-code.png">
                    <img src="<?php echo esc_url( $guest->qr_code_url ); ?>" alt="رمز QR الخاص بك">
                    <br>
                    <button type="button" style="margin-top:10px;">تحميل الرمز</button>
                </a>
            <?php endif; ?>
        </div>

        <!-- RSVP Card -->
        <div class="card">
            <h3>هل ستحضر؟</h3>
            <div class="icon-group" id="rsvp-buttons">
                <span class="icon <?php if($rsvp_done) echo 'disabled'; ?>" data-rsvp="attending" title="حاضر" style="color: <?php echo esc_attr($primary_color); ?>; font-size: 2em;">✔️</span>
                <span class="icon <?php if($rsvp_done) echo 'disabled'; ?>" data-rsvp="not_attending" title="معتذر" style="color: <?php echo esc_attr($primary_color); ?>; font-size: 2em;">❌</span>
            </div>
            <?php if ($guest && $guest->plus_one_allowed && !$rsvp_done) : ?>
                <div id="plus-one-section" style="margin-top: 15px;">
                    <label>
                        <input type="checkbox" id="plus_one_attending" name="plus_one_attending" value="1">
                        سأحضر معي ضيفاً إضافياً
                    </label>
                </div>
            <?php endif; ?>
            <p id="rsvp-message" style="display:none;"></p>
        </div>

        <!-- Info Card -->
        <div class="card">
            <div class="icon-group">
                <span class="icon info-toggle" data-target="#venue-info">📍 المكان</span>
                <span class="icon info-toggle" data-target="#time-info">⏰ الموعد</span>
                <span class="icon info-toggle" data-target="#location-info">🗺️ الموقع</span>
            </div>
            <div id="venue-info" class="icon-info"><?php echo esc_html($occasion->venue_details); ?></div>
            <div id="time-info" class="icon-info"><?php echo esc_html($occasion->time_details); ?></div>
            <div id="location-info" class="icon-info"><a href="<?php echo esc_url($occasion->location_details); ?>" target="_blank">عرض على الخريطة</a></div>
        </div>

        <!-- Greetings Card -->
        <div class="card greetings-form">
            <h3>اترك تهنئة</h3>
            <form id="greeting-form">
                <input type="text" id="greeting-author" placeholder="اسمك" required><br><br>
                <textarea id="greeting-message" rows="4" placeholder="رسالتك..." required></textarea><br><br>
                <button type="submit">إرسال التهنئة</button>
            </form>
        </div>

        <!-- Greetings Display -->
        <div class="card greetings-list">
            <h3>آخر التهاني</h3>
            <div id="greetings-container">
                <?php foreach ( $greetings as $greeting ) : ?>
                    <div class="greeting">
                        <strong><?php echo esc_html($greeting->author_name); ?>:</strong>
                        <p><?php echo esc_html($greeting->greeting_message); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Data for JS -->
        <script type="text/javascript">
            var eim_ajax = {
                ajax_url: "<?php echo admin_url('admin-ajax.php'); ?>",
                guest_id: "<?php echo $guest->id; ?>",
                nonce: "<?php echo wp_create_nonce('eim_rsvp_nonce'); ?>"
            };
        </script>

    <?php else : ?>
        <div class="header">
            <h1><?php _e('Invalid Invitation', 'eim'); ?></h1>
            <p><?php _e('The invitation code is either invalid or has expired. Please check the link and try again.', 'eim'); ?></p>
        </div>
    <?php endif; ?>

</div>

<?php wp_footer(); ?>
</body>
</html>
