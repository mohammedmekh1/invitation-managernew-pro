<?php
/**
 * Plugin Name:       Event Invitations Manager
 * Plugin URI:        https://example.com/
 * Description:       A plugin to manage events, guests, and invitations with QR code functionality.
 * Version:           1.0.0
 * Author:            Jules
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       event-invitations-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 */
function activate_event_invitations_manager() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-invitations-manager-activator.php';
    Event_Invitations_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_event_invitations_manager() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-invitations-manager-deactivator.php';
    Event_Invitations_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_event_invitations_manager' );
register_deactivation_hook( __FILE__, 'deactivate_event_invitations_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-event-invitations-manager.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_event_invitations_manager() {

    $plugin = new Event_Invitations_Manager();
    $plugin->run();

}
run_event_invitations_manager();
