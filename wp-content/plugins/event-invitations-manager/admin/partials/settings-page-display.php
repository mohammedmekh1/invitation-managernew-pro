<?php
/**
 * Provide an admin area view for the settings page.
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form method="post" action="options.php">
        <?php
            // This prints out all hidden setting fields
            settings_fields( 'eim_settings_group' );
            // This prints out all settings sections and fields
            do_settings_sections( 'event-invitations-manager-settings' );
            submit_button('حفظ الإعدادات');
        ?>
    </form>
</div>
