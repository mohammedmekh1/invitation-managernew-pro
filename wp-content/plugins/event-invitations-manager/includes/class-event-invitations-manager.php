<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/includes
 * @author     Jules <your-email@example.com>
 */
class Event_Invitations_Manager {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Event_Invitations_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'EIM_VERSION' ) ) {
            $this->version = EIM_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'event-invitations-manager';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Event_Invitations_Manager_Loader. Orchestrates the hooks of the plugin.
     * - Event_Invitations_Manager_i18n. Defines internationalization functionality.
     * - Event_Invitations_Manager_Admin. Defines all hooks for the admin area.
     * - Event_Invitations_Manager_Public. Defines all hooks for the public side of the site.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-event-invitations-manager-loader.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-event-invitations-manager-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-event-invitations-manager-public.php';

        $this->loader = new Event_Invitations_Manager_Loader();

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Event_Invitations_Manager_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu' );
        // Add more admin hooks here, like for enqueuing styles and scripts
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // AJAX handler for QR Code Verification
        $this->loader->add_action( 'wp_ajax_eim_verify_guest', $plugin_admin, 'verify_guest_ajax_handler' );

        // Handler for CSV export
        $this->loader->add_action( 'admin_init', $plugin_admin, 'handle_guest_export' );

        // Register settings
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {

        $plugin_public = new Event_Invitations_Manager_Public( $this->get_plugin_name(), $this->get_version() );

        // Hook for handling the invitation URL
        $this->loader->add_action( 'init', $plugin_public, 'add_rewrite_rule' );
        $this->loader->add_filter( 'query_vars', $plugin_public, 'add_query_vars' );
        $this->loader->add_action( 'template_redirect', $plugin_public, 'template_redirect' );

        // Add more public hooks here, like for enqueuing styles and scripts
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // AJAX handlers for public forms
        $this->loader->add_action( 'wp_ajax_nopriv_eim_handle_rsvp', $plugin_public, 'handle_rsvp' );
        $this->loader->add_action( 'wp_ajax_eim_handle_rsvp', $plugin_public, 'handle_rsvp' );

        $this->loader->add_action( 'wp_ajax_nopriv_eim_handle_greeting', $plugin_public, 'handle_greeting' );
        $this->loader->add_action( 'wp_ajax_eim_handle_greeting', $plugin_public, 'handle_greeting' );
    }




    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Event_Invitations_Manager_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
