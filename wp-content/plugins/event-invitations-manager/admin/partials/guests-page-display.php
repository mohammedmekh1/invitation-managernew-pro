<?php
/**
 * Provide an admin area view for the guests page.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/admin/partials
 */

require_once plugin_dir_path( __FILE__ ) . '../class-eim-guests-list-table.php';

$guests_list_table = new EIM_Guests_List_Table();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Guests', 'eim' ); ?></h1>
    <a href="<?php echo admin_url( 'admin.php?page=event-invitations-manager-guests&action=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'eim' ); ?></a>
    <a href="<?php echo admin_url( 'admin.php?page=event-invitations-manager-guests&action=import' ); ?>" class="page-title-action"><?php _e( 'Import Guests', 'eim' ); ?></a>
    <hr class="wp-header-end">

    <?php
    if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' ) ) {
        // Include the form for adding/editing guests
        require_once 'guest-form-display.php';
    } else if ( isset( $_GET['action'] ) && $_GET['action'] == 'import' ) {
        // Include the form for importing guests
        require_once 'guest-import-display.php';
    } else {
        // Display the list of guests
        $guests_list_table->prepare_items();
        ?>
        <form method="post">
            <input type="hidden" name="page" value="event-invitations-manager-guests">
            <?php
            $guests_list_table->display();
            ?>
        </form>
        <?php
    }
    ?>
</div>
