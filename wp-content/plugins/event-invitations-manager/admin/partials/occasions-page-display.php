<?php
/**
 * Provide an admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/admin/partials
 */

// Screen options
$screen = get_current_screen();
$option = $screen->get_option('per_page', 'option');
$per_page = get_user_meta(get_current_user_id(), $option, true);
if ( empty ( $per_page) || $per_page < 1 ) {
    $per_page = $screen->get_option( 'per_page', 'default' );
}

global $wpdb;

$occasions_list_table = new EIM_Occasions_List_Table();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Occasions', 'eim' ); ?></h1>
    <a href="<?php echo admin_url( 'admin.php?page=event-invitations-manager&action=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'eim' ); ?></a>
    <hr class="wp-header-end">

    <?php
    if ( isset( $_GET['action'] ) ) {
        if ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' ) {
            // Include the form for adding/editing occasions
            require_once 'occasion-form-display.php';
        }
    } else {
        // Display the list of occasions
        $occasions_list_table->prepare_items();
        ?>
        <form method="post">
            <?php
            $occasions_list_table->search_box( 'search', 'search_id' );
            $occasions_list_table->display();
            ?>
        </form>
        <?php
    }
    ?>
</div>
