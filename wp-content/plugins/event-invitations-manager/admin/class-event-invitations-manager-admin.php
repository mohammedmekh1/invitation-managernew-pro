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

        // Enqueue a general admin script for all our pages
        wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . '../assets/js/admin-script.js', array( 'jquery' ), $this->version, true );

        // Enqueue scanner scripts for the scan page
        if ( $hook === 'invitations_page_event-invitations-manager-scan' ) {
            wp_enqueue_script('jsqr', 'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js', array(), '1.4.0', true);
            wp_enqueue_script($this->plugin_name . '-scan', plugin_dir_url( __FILE__ ) . '../assets/js/admin-scan-script.js', array( 'jquery', 'jsqr' ), $this->version, true);

            wp_localize_script(
                $this->plugin_name . '-scan',
                'eim_admin_scan_nonce',
                wp_create_nonce( 'eim_verify_guest_nonce' )
            );
        }

        // Enqueue stats scripts for the statistics page
        if ( $hook === 'invitations_page_event-invitations-manager-statistics' ) {
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
            wp_enqueue_script($this->plugin_name . '-stats', plugin_dir_url( __FILE__ ) . '../assets/js/admin-stats.js', array( 'jquery', 'chartjs' ), $this->version, true);
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
            'دعوات المناسبات',
            'الدعوات',
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_occasions_page' ),
            'dashicons-groups',
            25
        );

        // Add sub-menu page for Occasions
        add_submenu_page(
            $this->plugin_name,
            'المناسبات',
            'المناسبات',
            'manage_options',
            $this->plugin_name, // This makes it the default page
            array( $this, 'display_occasions_page' )
        );

        // Add sub-menu page for Guests
        add_submenu_page(
            $this->plugin_name,
            'المدعوون',
            'المدعوون',
            'manage_options',
            $this->plugin_name . '-guests',
            array( $this, 'display_guests_page' )
        );

        // Add sub-menu page for Statistics
        add_submenu_page(
            $this->plugin_name,
            'الإحصائيات',
            'الإحصائيات',
            'manage_options',
            $this->plugin_name . '-statistics',
            array( $this, 'display_statistics_page' )
        );

        // Add sub-menu page for Scan QR Code
        add_submenu_page(
            $this->plugin_name,
            'مسح رمز QR',
            'مسح رمز QR',
            'manage_options',
            $this->plugin_name . '-scan',
            array( $this, 'display_scan_page' )
        );

        // Add sub-menu page for Settings
        add_submenu_page(
            $this->plugin_name,
            'الإعدادات',
            'الإعدادات',
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

    /**
     * Handles the export of guests to a CSV file.
     *
     * @since    1.0.0
     */
    public function handle_guest_export() {
        if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'export_guests' ) {
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'eim_export_guests_nonce' ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You do not have permission to export guests.' );
        }

        global $wpdb;
        $table_guests = $wpdb->prefix . 'eim_guests';
        $table_occasions = $wpdb->prefix . 'eim_occasions';

        $sql = "SELECT g.name, g.email, o.name as occasion_name, g.unique_code, g.rsvp_status, g.plus_one_attending, g.check_in_status FROM {$table_guests} g LEFT JOIN {$table_occasions} o ON g.occasion_id = o.id ORDER BY o.name, g.name";
        $guests = $wpdb->get_results( $sql, ARRAY_A );

        $filename = 'event-guests-' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM to fix encoding in Excel
        fputs($output, "\xEF\xBB\xBF");

        $header = array('Guest Name', 'Email', 'Occasion', 'Invitation Link', 'RSVP Status', 'Brought +1', 'Checked In');
        fputcsv($output, $header);

        foreach ($guests as $guest) {
            $guest['invitation_link'] = home_url('/invitation/' . $guest['unique_code'] . '/');
            unset($guest['unique_code']); // No need to show the raw code in export
            fputcsv($output, $guest);
        }

        fclose($output);
        exit;
    }

    /**
     * Register the settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting( 'eim_settings_group', 'eim_option_primary_color' );
        register_setting( 'eim_settings_group', 'eim_option_email_subject' );
        register_setting( 'eim_settings_group', 'eim_option_email_body' );

        add_settings_section( 'eim_template_section', 'تخصيص قالب الدعوة', array( $this, 'print_template_section_info' ), 'event-invitations-manager-settings' );
        add_settings_field( 'eim_primary_color', 'اللون الأساسي', array( $this, 'primary_color_callback' ), 'event-invitations-manager-settings', 'eim_template_section' );

        add_settings_section( 'eim_email_section', 'إعدادات البريد الإلكتروني', array( $this, 'print_email_section_info' ), 'event-invitations-manager-settings' );
        add_settings_field( 'eim_email_subject', 'موضوع البريد الإلكتروني', array( $this, 'email_subject_callback' ), 'event-invitations-manager-settings', 'eim_email_section' );
        add_settings_field( 'eim_email_body', 'محتوى البريد الإلكتروني', array( $this, 'email_body_callback' ), 'event-invitations-manager-settings', 'eim_email_section' );
    }

    public function print_template_section_info() {
        echo 'قم بتخصيص مظهر صفحة الدعوة العامة.';
    }
    public function print_email_section_info() {
        echo 'قم بإعداد قالب البريد الإلكتروني الذي يتم إرساله للمدعوين. يمكنك استخدام الرموز التالية: `[guest_name]`, `[occasion_name]`, `[invitation_link]`';
    }

    public function primary_color_callback() {
        printf( '<input type="text" id="eim_option_primary_color" name="eim_option_primary_color" value="%s" />', esc_attr( get_option('eim_option_primary_color', '#006A4E') ) );
    }
    public function email_subject_callback() {
        printf( '<input type="text" id="eim_option_email_subject" name="eim_option_email_subject" value="%s" style="width: 100%%" />', esc_attr( get_option('eim_option_email_subject', 'دعوة لحضور [occasion_name]') ) );
    }
    public function email_body_callback() {
        $content = get_option('eim_option_email_body', "مرحباً [guest_name],\n\nنتشرف بدعوتكم لحضور [occasion_name].\n\nيمكنكم تأكيد حضوركم عبر الرابط التالي:\n[invitation_link]\n\nمع خالص التقدير.");
        wp_editor( $content, 'eim_option_email_body', array('textarea_name' => 'eim_option_email_body') );
    }

    /**
     * Sends the invitation email to a guest.
     *
     * @since    1.0.0
     * @param    int    $guest_id    The ID of the guest to email.
     */
    public function send_invitation_email( $guest_id ) {
        global $wpdb;
        $table_guests = $wpdb->prefix . 'eim_guests';
        $table_occasions = $wpdb->prefix . 'eim_occasions';

        $guest = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_guests WHERE id = %d", $guest_id ) );

        if ( ! $guest || empty( $guest->email ) ) {
            return; // Don't send if no guest or no email
        }

        $occasion = $wpdb->get_row( $wpdb->prepare( "SELECT name FROM $table_occasions WHERE id = %d", $guest->occasion_id ) );
        $occasion_name = $occasion ? $occasion->name : '';

        $subject_template = get_option( 'eim_option_email_subject', 'دعوة لحضور [occasion_name]' );
        $body_template = get_option( 'eim_option_email_body', "مرحباً [guest_name],\n\nنتشرف بدعوتكم لحضور [occasion_name].\n\nيمكنكم تأكيد حضوركم عبر الرابط التالي:\n[invitation_link]\n\nمع خالص التقدير." );

        $invitation_link = home_url('/invitation/' . $guest->unique_code . '/');

        // Replace placeholders
        $replacements = array(
            '[guest_name]'      => $guest->name,
            '[occasion_name]'   => $occasion_name,
            '[invitation_link]' => $invitation_link,
        );

        $subject = str_replace( array_keys($replacements), array_values($replacements), $subject_template );
        $body = nl2br( str_replace( array_keys($replacements), array_values($replacements), $body_template ) );

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail( $guest->email, $subject, $body, $headers );
    }
}
