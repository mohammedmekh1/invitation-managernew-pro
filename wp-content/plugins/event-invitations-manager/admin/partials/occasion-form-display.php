<?php
/**
 * Provide an admin area view for the add/edit occasion form
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/admin/partials
 */

global $wpdb;
$table_name = $wpdb->prefix . 'eim_occasions';

$message = '';
$notice_class = '';

$occasion_id = isset( $_GET['occasion'] ) ? absint( $_GET['occasion'] ) : 0;
$is_edit_mode = $occasion_id > 0;

// Default values
$default_data = array(
    'name' => '',
    'event_date' => '',
    'invitation_image' => '',
    'welcome_message' => '',
    'venue_details' => '',
    'time_details' => '',
    'location_details' => ''
);

// Handle form submission
if ( isset( $_POST['submit'] ) && check_admin_referer( 'eim_save_occasion_action', 'eim_save_occasion_nonce' ) ) {

    $item = array();
    $item['name'] = sanitize_text_field( $_POST['name'] );
    $item['event_date'] = sanitize_text_field( $_POST['event_date'] );
    $item['invitation_image'] = esc_url_raw( $_POST['invitation_image'] );
    $item['welcome_message'] = sanitize_textarea_field( $_POST['welcome_message'] );
    $item['venue_details'] = sanitize_textarea_field( $_POST['venue_details'] );
    $item['time_details'] = sanitize_textarea_field( $_POST['time_details'] );
    $item['location_details'] = sanitize_textarea_field( $_POST['location_details'] );

    if ( $is_edit_mode ) {
        $wpdb->update( $table_name, $item, array( 'id' => $occasion_id ) );
        $message = __( 'Occasion updated successfully!', 'eim' );
    } else {
        $wpdb->insert( $table_name, $item );
        $occasion_id = $wpdb->insert_id;
        $message = __( 'Occasion added successfully!', 'eim' );
    }

    $notice_class = 'notice-success';

    // Redirect to the list table page after saving
    // To do this properly, we should process this in the admin_post hook.
    // For now, we show a message.
}

// If in edit mode, get the occasion data from the DB
$occasion = $is_edit_mode ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $occasion_id ), ARRAY_A ) : $default_data;

?>
<?php if ( ! empty( $message ) ) : ?>
    <div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
        <p><?php echo esc_html( $message ); ?></p>
    </div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="occasion_id" value="<?php echo $occasion_id; ?>">
    <?php wp_nonce_field( 'eim_save_occasion_action', 'eim_save_occasion_nonce' ); ?>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label for="name"><?php _e( 'Occasion Name', 'eim' ); ?></label></th>
                <td><input type="text" name="name" id="name" value="<?php echo esc_attr( $occasion['name'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="event_date"><?php _e( 'Event Date', 'eim' ); ?></label></th>
                <td><input type="datetime-local" name="event_date" id="event_date" value="<?php echo esc_attr( date( 'Y-m-d\TH:i', strtotime( $occasion['event_date'] ) ) ); ?>" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="welcome_message"><?php _e( 'Welcome Message', 'eim' ); ?></label></th>
                <td><textarea name="welcome_message" id="welcome_message" rows="5" class="large-text"><?php echo esc_textarea( $occasion['welcome_message'] ); ?></textarea></td>
            </tr>
             <tr>
                <th scope="row"><label for="invitation_image"><?php _e( 'Invitation Image URL', 'eim' ); ?></label></th>
                <td>
                    <input type="text" name="invitation_image" id="invitation_image" value="<?php echo esc_attr( $occasion['invitation_image'] ); ?>" class="regular-text">
                    <button type="button" class="button" id="upload_image_button"><?php _e( 'Upload Image', 'eim' ); ?></button>
                    <p class="description"><?php _e( 'Upload or select an image from the media library.', 'eim' ); ?></p>
                </td>
            </tr>
             <tr>
                <th scope="row"><label for="venue_details"><?php _e( 'Venue Details', 'eim' ); ?></label></th>
                <td><textarea name="venue_details" id="venue_details" rows="3" class="large-text"><?php echo esc_textarea( $occasion['venue_details'] ); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="time_details"><?php _e( 'Time Details', 'eim' ); ?></label></th>
                <td><textarea name="time_details" id="time_details" rows="3" class="large-text"><?php echo esc_textarea( $occasion['time_details'] ); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="location_details"><?php _e( 'Location Details (Google Maps Link)', 'eim' ); ?></label></th>
                <td><input type="url" name="location_details" id="location_details" value="<?php echo esc_attr( $occasion['location_details'] ); ?>" class="regular-text"></td>
            </tr>
        </tbody>
    </table>

    <?php submit_button( $is_edit_mode ? __( 'Update Occasion', 'eim' ) : __( 'Add Occasion', 'eim' ) ); ?>
</form>

<script>
jQuery(document).ready(function($) {
    $('#upload_image_button').click(function(e) {
        e.preventDefault();
        var image_frame;
        if(image_frame){
            image_frame.open();
        }
        // Define image_frame as wp.media object
        image_frame = wp.media({
                           title: 'Select Media',
                           multiple : false,
                           library : {
                                type : 'image',
                            }
                       });

        image_frame.on('select', function(){
            var attachment = image_frame.state().get('selection').first().toJSON();
            $('#invitation_image').val(attachment.url);
        });

        image_frame.open();
    });
});
</script>
