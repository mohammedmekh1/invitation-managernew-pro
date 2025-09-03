<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class IM_Database {

    public static function guests_table(){ global $wpdb; return $wpdb->prefix . 'im_guests'; }
    public static function invitations_table(){ global $wpdb; return $wpdb->prefix . 'im_invitations'; }
    public static function events_table(){ global $wpdb; return $wpdb->prefix . 'im_events'; }
    public static function logs_table(){ global $wpdb; return $wpdb->prefix . 'im_qr_logs'; }

    public static function install(){
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $guests = "CREATE TABLE " . self::guests_table() . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NULL,
            phone VARCHAR(50) NULL,
            qr_token VARCHAR(255) NOT NULL UNIQUE,
            invited_event_id BIGINT(20) UNSIGNED NULL,
            status VARCHAR(50) DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NULL,
            PRIMARY KEY  (id)
        ) $charset;";

        $invitations = "CREATE TABLE " . self::invitations_table() . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            event_id BIGINT(20) UNSIGNED NULL,
            link_slug VARCHAR(255) NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";

        $events = "CREATE TABLE " . self::events_table() . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            event_date DATETIME NULL,
            location VARCHAR(255) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";

        $logs = "CREATE TABLE " . self::logs_table() . " (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            guest_id BIGINT(20) UNSIGNED NULL,
            qr_token VARCHAR(255) NOT NULL,
            scan_result VARCHAR(50) NOT NULL,
            user_agent TEXT NULL,
            ip_address VARCHAR(100) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($guests);
        dbDelta($invitations);
        dbDelta($events);
        dbDelta($logs);
    }

    public static function stats(){
        global $wpdb;
        $g = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . self::guests_table());
        $i = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . self::invitations_table());
        $e = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . self::events_table());
        $s = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . self::logs_table());
        return array(
            'guests' => $g,
            'invitations' => $i,
            'events' => $e,
            'scans' => $s
        );
    }

    public static function recent_activity($limit = 10){
        global $wpdb;
        $logs_table = self::logs_table();
        $guests_table = self::guests_table();
        $rows = $wpdb->get_results($wpdb->prepare("
            SELECT l.*, g.name as guest_name 
            FROM $logs_table l
            LEFT JOIN $guests_table g ON g.id = l.guest_id
            ORDER BY l.id DESC
            LIMIT %d
        ", $limit), ARRAY_A);
        return $rows ?: array();
    }

    public static function verify_token($token){
        global $wpdb;
        $guest = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::guests_table() . " WHERE qr_token=%s", $token));
        if(!$guest){ return array('success'=>false,'message'=>'رمز غير معروف'); }
        // تحديث الحالة
        $wpdb->update(self::guests_table(), array('status'=>'checked','updated_at'=>current_time('mysql')), array('id'=>$guest->id));
        // سجل
        $wpdb->insert(self::logs_table(), array(
            'guest_id'=>$guest->id,
            'qr_token'=>$token,
            'scan_result'=>'success',
            'user_agent'=>isset($_SERVER['HTTP_USER_AGENT'])?sanitize_text_field($_SERVER['HTTP_USER_AGENT']):'',
            'ip_address'=>isset($_SERVER['REMOTE_ADDR'])?sanitize_text_field($_SERVER['REMOTE_ADDR']):'',
        ));
        return array('success'=>true,'message'=>'تم التأكيد','guest'=>array('id'=>$guest->id,'name'=>$guest->name));
    }
}
