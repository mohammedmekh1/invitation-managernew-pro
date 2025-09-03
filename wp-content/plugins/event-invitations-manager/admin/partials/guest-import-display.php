<?php
/**
 * Provide an admin area view for the guest import form
 */

global $wpdb;
$table_guests = $wpdb->prefix . 'eim_guests';
$table_occasions = $wpdb->prefix . 'eim_occasions';
$message = '';
$notice_class = '';

// --- Re-use the helper function to generate a unique code ---
if ( ! function_exists( 'eim_generate_unique_code' ) ) {
    function eim_generate_unique_code( $guest_name, $guest_id ) {
        $unique_string = uniqid() . $guest_name . $guest_id . time();
        return substr( wp_hash( $unique_string ), 0, 12 );
    }
}

if ( isset( $_POST['eim_import_nonce'] ) && wp_verify_nonce( $_POST['eim_import_nonce'], 'eim_import_action' ) ) {
    if ( isset( $_FILES['guest_csv'] ) && $_FILES['guest_csv']['error'] == 0 ) {

        $occasion_id = absint($_POST['occasion_id']);
        if ( empty($occasion_id) ) {
            $message = 'Please select an occasion.';
            $notice_class = 'notice-error';
        } else {
            $file = $_FILES['guest_csv'];
            $file_type = wp_check_filetype( $file['name'], array('csv' => 'text/csv') );

            if ( $file_type['ext'] === 'csv' ) {
                $csv_file = fopen( $file['tmp_name'], 'r' );
                $imported_count = 0;

                // Skip header row
                fgetcsv($csv_file);

                while ( ( $row = fgetcsv( $csv_file ) ) !== FALSE ) {
                    $guest_name = sanitize_text_field( $row[0] );
                    if ( ! empty( $guest_name ) ) {
                        $item = ['name' => $guest_name, 'occasion_id' => $occasion_id];
                        $wpdb->insert( $table_guests, $item );
                        $new_guest_id = $wpdb->insert_id;
                        $unique_code = eim_generate_unique_code( $guest_name, $new_guest_id );
                        $wpdb->update( $table_guests, array( 'unique_code' => $unique_code ), array( 'id' => $new_guest_id ) );
                        $imported_count++;
                    }
                }
                fclose($csv_file);
                $message = "Successfully imported " . $imported_count . " guests.";
                $notice_class = 'notice-success';
            } else {
                $message = 'Please upload a valid CSV file.';
                $notice_class = 'notice-error';
            }
        }
    } else {
        $message = 'File upload error. Please try again.';
        $notice_class = 'notice-error';
    }
}

$occasions = $wpdb->get_results( "SELECT id, name FROM $table_occasions ORDER BY name ASC" );
?>

<?php if ( ! empty( $message ) ) : ?>
    <div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">
        <p><?php echo esc_html( $message ); ?></p>
    </div>
<?php endif; ?>

<h2><?php _e('Import Guests from CSV', 'eim'); ?></h2>

<p><?php _e('Upload a CSV file with guest names. The file should have one column: <strong>Name</strong>. The first row will be treated as a header and skipped.', 'eim'); ?></p>

<form method="post" enctype="multipart/form-data">
    <?php wp_nonce_field( 'eim_import_action', 'eim_import_nonce' ); ?>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><label for="occasion_id"><?php _e( 'Import to Occasion', 'eim' ); ?></label></th>
                <td>
                    <select name="occasion_id" id="occasion_id" required>
                        <option value=""><?php _e( 'Select an Occasion', 'eim' ); ?></option>
                        <?php foreach ( $occasions as $occasion ) : ?>
                            <option value="<?php echo esc_attr( $occasion->id ); ?>"><?php echo esc_html( $occasion->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="guest_csv"><?php _e( 'CSV File', 'eim' ); ?></label></th>
                <td><input type="file" name="guest_csv" id="guest_csv" accept=".csv" required></td>
            </tr>
        </tbody>
    </table>

    <?php submit_button( __( 'Import Guests', 'eim' ) ); ?>
</form>
