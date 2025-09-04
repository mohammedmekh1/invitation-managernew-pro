<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing side.
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/public
 * @author     Jules <your-email@example.com>
 */
class Event_Invitations_Manager_Public {

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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/css/public-style.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../assets/js/public-script.js', array( 'jquery' ), $this->version, false );
    }

    /**
     * Add rewrite rule for the invitation page.
     *
     * @since    1.0.0
     */
    public function add_rewrite_rule() {
        add_rewrite_rule( '^invitation/([^/]*)/?', 'index.php?invitation_code=$matches[1]', 'top' );
    }

    /**
     * Add the 'invitation_code' query variable so WordPress recognizes it.
     *
     * @since    1.0.0
     * @param    array    $vars    The array of existing query variables.
     * @return   array    $vars    The modified array of query variables.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'invitation_code';
        return $vars;
    }

    /**
     * Load a custom template for the invitation page.
     *
     * @since    1.0.0
     */
    public function template_redirect() {
        global $wp_query;

        if ( isset( $wp_query->query_vars['invitation_code'] ) ) {
            // A file to be created
            $template = plugin_dir_path( __FILE__ ) . 'partials/invitation-page-template.php';
            if ( file_exists( $template ) ) {
                // Prevent caching of the invitation page
                header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");

                include( $template );
                exit;
            }
        }
    }

    /**
     * Handles the RSVP submission via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_rsvp() {
        check_ajax_referer( 'eim_rsvp_nonce', 'nonce' );

        global $wpdb;
        $table_name = $wpdb->prefix . 'eim_guests';

        $guest_id = intval( $_POST['guest_id'] );
        $rsvp_status = sanitize_text_field( $_POST['rsvp_status'] );
        $plus_one_attending = isset( $_POST['plus_one'] ) && $_POST['plus_one'] == '1' ? 1 : 0;

        $update_data = array(
            'rsvp_status' => $rsvp_status,
            'plus_one_attending' => $plus_one_attending
        );

        // Update the guest's RSVP status
        $wpdb->update(
            $table_name,
            $update_data,
            array( 'id' => $guest_id )
        );

        $response = array( 'success' => true );

        if ( $rsvp_status === 'attending' ) {
            $guest = $wpdb->get_row( $wpdb->prepare( "SELECT unique_code FROM $table_name WHERE id = %d", $guest_id ) );
            if ( $guest ) {
                $unique_code = $guest->unique_code;

                // Path to save QR codes
                $upload_dir = wp_upload_dir();
                $qr_dir = $upload_dir['basedir'] . '/eim-qrcodes';
                if ( ! is_dir( $qr_dir ) ) {
                    wp_mkdir_p( $qr_dir );
                }

                $qr_file_path = $qr_dir . '/' . $unique_code . '.png';
                $qr_file_url = $upload_dir['baseurl'] . '/eim-qrcodes/' . $unique_code . '.png';

                if ( ! file_exists( $qr_file_path ) ) {
                    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/qrcode.php';
                    // The data to be encoded in the QR code. Using the guest's unique code.
                    $qr = new QRCode($unique_code, array('w' => 400, 'h' => 400, 's' => 'qrh'));
                    $qr->output_image($qr_file_path);
                }

                // Save URL to database
                $wpdb->update( $table_name, array( 'qr_code_url' => $qr_file_url ), array( 'id' => $guest_id ) );

                $response['qr_code_url'] = $qr_file_url;
            }
        }

        wp_send_json_success( $response );
    }

    /**
     * Handles the Greeting submission via AJAX.
     *
     * @since    1.0.0
     */
    public function handle_greeting() {
        check_ajax_referer( 'eim_rsvp_nonce', 'nonce' ); // Re-using the same nonce for simplicity

        global $wpdb;
        $table_name = $wpdb->prefix . 'eim_greetings';

        $guest_id = intval( $_POST['guest_id'] );
        $author = sanitize_text_field( $_POST['author'] );
        $message = sanitize_textarea_field( $_POST['message'] );

        if ( empty($author) || empty($message) ) {
            wp_send_json_error( array( 'message' => 'Name and message are required.' ) );
        }

        $wpdb->insert(
            $table_name,
            array(
                'guest_id' => $guest_id,
                'author_name' => $author,
                'greeting_message' => $message,
                'created_at' => current_time( 'mysql' ),
            )
        );

        wp_send_json_success( array( 'message' => 'Greeting received!' ) );
    }
}
