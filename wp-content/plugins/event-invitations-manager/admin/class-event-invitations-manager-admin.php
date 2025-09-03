<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/admin
 * @author     Jules <your-email@example.com>
 */
class Event_Invitations_Manager_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/css/admin-style.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts($hook) {
        // The hook for our main page is 'toplevel_page_event-invitations-manager'
        // The hook for submenu pages is 'invitations_page_event-invitations-manager-...'
        if ( strpos($hook, 'event-invitations-manager') === false ) {
            return;
        }

        // Enqueue media uploader scripts for the occasion form
        if ( $hook === 'toplevel_page_event-invitations-manager' && isset($_GET['action']) ) {
            wp_enqueue_media();
        }

        // Enqueue scanner scripts for the scan page
        if ( $hook === 'invitations_page_event-invitations-manager-scan' ) {
            wp_enqueue_script('jsqr', 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js', array(), '1.4.0', true);
            wp_enqueue_script($this->plugin_name . '-scan', plugin_dir_url( __FILE__ ) . '../assets/js/admin-scan-script.js', array( 'jquery', 'jsqr' ), $this->version, true);

            // Pass a nonce to the script
            wp_localize_script(
                $this->plugin_name . '-scan',
                'eim_admin_scan_nonce',
                wp_create_nonce( 'eim_verify_guest_nonce' )
            );
        }
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_menu() {
        // Add main menu page
        add_menu_page(
            __( 'Event Invitations', 'event-invitations-manager' ),
            __( 'Invitations', 'event-invitations-manager' ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_occasions_page' ),
            'dashicons-groups',
            25
        );

        // Add sub-menu page for Occasions
        add_submenu_page(
            $this->plugin_name,
            __( 'Occasions', 'event-invitations-manager' ),
            __( 'Occasions', 'event-invitations-manager' ),
            'manage_options',
            $this->plugin_name, // This makes it the default page
            array( $this, 'display_occasions_page' )
        );

        // Add sub-menu page for Guests
        add_submenu_page(
            $this->plugin_name,
            __( 'Guests', 'event-invitations-manager' ),
            __( 'Guests', 'event-invitations-manager' ),
            'manage_options',
            $this->plugin_name . '-guests',
            array( $this, 'display_guests_page' )
        );

        // Add sub-menu page for Statistics
        add_submenu_page(
            $this->plugin_name,
            __( 'Statistics', 'event-invitations-manager' ),
            __( 'Statistics', 'event-invitations-manager' ),
            'manage_options',
            $this->plugin_name . '-statistics',
            array( $this, 'display_statistics_page' )
        );

        // Add sub-menu page for Scan QR Code
        add_submenu_page(
            $this->plugin_name,
            __( 'Scan QR Code', 'event-invitations-manager' ),
            __( 'Scan QR Code', 'event-invitations-manager' ),
            'manage_options',
            $this->plugin_name . '-scan',
            array( $this, 'display_scan_page' )
        );

        // Add sub-menu page for Settings
        add_submenu_page(
            $this->plugin_name,
            __( 'Settings', 'event-invitations-manager' ),
            __( 'Settings', 'event-invitations-manager' ),
            'manage_options',
            $this->plugin_name . '-settings',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Render the basic display of the main page.
     *
     * @since    1.0.0
     */
    public function display_occasions_page() {
        require_once 'partials/occasions-page-display.php';
    }

    public function display_guests_page() {
        require_once 'partials/guests-page-display.php';
    }

    public function display_statistics_page() {
        require_once 'partials/statistics-page-display.php';
    }

    public function display_scan_page() {
        require_once 'partials/scan-page-display.php';
    }

    public function display_settings_page() {
        require_once 'partials/settings-page-display.php';
    }

    /**
     * Handles the Guest Verification from QR Code scan via AJAX.
     *
     * @since    1.0.0
     */
    public function verify_guest_ajax_handler() {
        // We are reusing the nonce created in the enqueue_scripts method
        check_ajax_referer( 'eim_verify_guest_nonce', 'nonce' );

        global $wpdb;
        $table_guests = $wpdb->prefix . 'eim_guests';

        $unique_code = sanitize_text_field( $_POST['unique_code'] );

        $guest = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_guests WHERE unique_code = %s", $unique_code ) );

        if ( ! $guest ) {
            wp_send_json_error( array( 'message' => 'Invalid QR Code. Guest not found.' ) );
        }

        if ( $guest->rsvp_status !== 'attending' ) {
            wp_send_json_error( array( 'message' => 'This guest has not RSVP\'d as attending. (' . $guest->name . ')' ) );
        }

        if ( $guest->check_in_status == 1 ) {
            wp_send_json_error( array( 'message' => 'This guest has already been checked in. (' . $guest->name . ')' ) );
        }

        // All checks passed, check the guest in
        $wpdb->update(
            $table_guests,
            array( 'check_in_status' => 1 ),
            array( 'id' => $guest->id )
        );

        wp_send_json_success( array(
            'guest_name' => $guest->name,
            'message' => 'Guest checked in successfully!'
        ) );
    }
}
