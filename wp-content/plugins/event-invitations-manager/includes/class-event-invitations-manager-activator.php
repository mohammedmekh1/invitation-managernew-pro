<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Event_Invitations_Manager
 * @subpackage Event_Invitations_Manager/includes
 * @author     Jules <your-email@example.com>
 */
class Event_Invitations_Manager_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table for Occasions
        $table_name_occasions = $wpdb->prefix . 'eim_occasions';
        $sql_occasions = "CREATE TABLE $table_name_occasions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            event_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            invitation_image varchar(255) DEFAULT '' NOT NULL,
            welcome_message text,
            venue_details text,
            time_details text,
            location_details text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Table for Guests
        $table_name_guests = $wpdb->prefix . 'eim_guests';
        $sql_guests = "CREATE TABLE $table_name_guests (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            occasion_id mediumint(9) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) DEFAULT '' NOT NULL,
            unique_code varchar(100) NOT NULL,
            rsvp_status varchar(20) DEFAULT 'pending' NOT NULL,
            qr_code_url varchar(255) DEFAULT '' NOT NULL,
            check_in_status tinyint(1) DEFAULT 0 NOT NULL,
            plus_one_allowed tinyint(1) DEFAULT 0 NOT NULL,
            plus_one_attending tinyint(1) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_code (unique_code),
            KEY occasion_id (occasion_id)
        ) $charset_collate;";

        // Table for Greetings
        $table_name_greetings = $wpdb->prefix . 'eim_greetings';
        $sql_greetings = "CREATE TABLE $table_name_greetings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            guest_id bigint(20) NOT NULL,
            author_name varchar(255) NOT NULL,
            greeting_message text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY guest_id (guest_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_occasions );
        dbDelta( $sql_guests );
        dbDelta( $sql_greetings );
    }

}
