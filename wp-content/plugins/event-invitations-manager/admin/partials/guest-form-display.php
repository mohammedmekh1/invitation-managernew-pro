<?php
/**
 * Provide an admin area view for the add/edit guest form
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/admin/partials
 */

global $wpdb;
$table_guests = $wpdb->prefix . 'eim_guests';
$table_occasions = $wpdb->prefix . 'eim_occasions';

$message = '';
$notice_class = '';

$guest_id = isset( $_GET['guest'] ) ? absint( $_GET['guest'] ) : 0;
$is_edit_mode = $guest_id > 0;

// Default values
$default_data = array(
    'name' => '',
    'occasion_id' => '',
);

// --- Helper function to generate a unique code ---
if ( ! function_exists( 'eim_generate_unique_code' ) ) {
    function eim_generate_unique_code( $guest_name, $guest_id ) {
        // Simple but effective: hash a combination of a unique ID, guest details, and time
        $unique_string = uniqid() . $guest_name . $guest_id . time();
        return substr( wp_hash( $unique_string ), 0, 12 ); // 12-char hash
    }
}


// Handle form submission
if ( isset( $_POST['submit'] ) && check_admin_referer( 'eim_save_guest_action', 'eim_save_guest_nonce' ) ) {

    $item = array();
    $item['name'] = sanitize_text_field( $_POST['name'] );
    $item['occasion_id'] = absint( $_POST['occasion_id'] );

    if ( $is_edit_mode ) {
        $wpdb->update( $table_guests, $item, array( 'id' => $guest_id ) );
        $message = __( 'Guest updated successfully!', 'eim' );
    } else {
        // Insert first to get an ID
        $wpdb->insert( $table_guests, $item );
        $new_guest_id = $wpdb->insert_id;

        // Now generate unique code with the new ID and update the record
        $unique_code = eim_generate_unique_code( $item['name'], $new_guest_id );
        $wpdb->update( $table_guests, array( 'unique_code' => $unique_code ), array( 'id' => $new_guest_id ) );

        $guest_id = $new_guest_id;
        $message = __( 'Guest added successfully!', 'eim' );
    }

    $notice_class = 'notice-success';
}

// If in edit mode, get the guest data from the DB
$guest = $is_edit_mode ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_guests WHERE id = %d", $guest_id ), ARRAY_A ) : $default_data;

// Get all occasions for the dropdown
$occasions = $wpdb->get_results( "SELECT id, name FROM $table_occasions ORDER BY name ASC" );

?>
<?php if ( ! empty( $message ) ) : ?>
    <div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
        <p><?php echo esc_html( $message ); ?></p>
        <p><a href="<?php echo admin_url('admin.php?page=event-invitations-manager-guests'); ?>"><?php _e('&larr; Back to Guests List', 'eim'); ?></a></p>
    </div>
<?php endif; ?>

<h2><?php echo $is_edit_mode ? __('Edit Guest', 'eim') : __('Add New Guest', 'eim'); ?></h2>

<form method="post">
    <input type="hidden" name="guest_id" value="<?php echo $guest_id; ?>">
    <?php wp_nonce_field( 'eim_save_guest_action', 'eim_save_guest_nonce' ); ?>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label for="name"><?php _e( 'Guest Name', 'eim' ); ?></label></th>
                <td><input type="text" name="name" id="name" value="<?php echo esc_attr( $guest['name'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="occasion_id"><?php _e( 'Occasion', 'eim' ); ?></label></th>
                <td>
                    <select name="occasion_id" id="occasion_id" required>
                        <option value=""><?php _e( 'Select an Occasion', 'eim' ); ?></option>
                        <?php foreach ( $occasions as $occasion ) : ?>
                            <option value="<?php echo esc_attr( $occasion->id ); ?>" <?php selected( $guest['occasion_id'], $occasion->id ); ?>>
                                <?php echo esc_html( $occasion->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>

    <?php submit_button( $is_edit_mode ? __( 'Update Guest', 'eim' ) : __( 'Add Guest', 'eim' ) ); ?>
</form>
