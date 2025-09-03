<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class IM_Admin {

    public function __construct(){
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_enqueue_scripts', array($this, 'assets'));
        add_action('wp_ajax_im_get_stats', array($this, 'ajax_get_stats'));
        add_action('wp_ajax_im_get_activity', array($this, 'ajax_get_activity'));
    }

    public function menu(){
        add_menu_page(
            __('Invitation Manager','invitation-manager'),
            __('دعوات','invitation-manager'),
            'manage_options',
            'im-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-tickets-alt',
            26
        );

        add_submenu_page('im-dashboard', __('ماسح QR','invitation-manager'), __('ماسح QR','invitation-manager'), 'manage_options', 'im-qr', array($this,'render_qr'));
    }

    public function assets($hook){
        if( strpos($hook, 'im-dashboard') === false && strpos($hook, 'im-qr') === false ){
            return;
        }
        // خطوط وتنسيقات
        wp_enqueue_style('im-google-fonts', 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Tajawal:wght@400;700&display=swap', array(), IM_VER);
        wp_enqueue_style('im-admin', IM_URL . 'assets/css/admin.css', array(), IM_VER);
        // جافاسكربت للوحة
        wp_enqueue_script('im-dashboard', IM_URL . 'assets/js/admin-dashboard.js', array('jquery'), IM_VER, true);
        wp_localize_script('im-dashboard', 'IM_AJAX', array('url'=>admin_url('admin-ajax.php'), 'nonce'=>wp_create_nonce('im_nonce')));

        // لمحرر ماسح QR
        if( isset($_GET['page']) && $_GET['page'] === 'im-qr' ){
            wp_enqueue_script('html5-qrcode', 'https://unpkg.com/html5-qrcode', array(), IM_VER, true);
            wp_enqueue_script('im-qr', IM_URL . 'assets/js/qr.js', array('html5-qrcode'), IM_VER, true);
            wp_localize_script('im-qr', 'IM_QR', array('url'=>admin_url('admin-ajax.php'), 'nonce'=>wp_create_nonce('im_nonce')));
        }
    }

    public function render_dashboard(){
        include IM_PATH . 'admin/dashboard.php';
    }

    public function render_qr(){
        include IM_PATH . 'admin/qr-scanner.php';
    }

    public function ajax_get_stats(){
        check_ajax_referer('im_nonce');
        wp_send_json_success( IM_Database::stats() );
    }

    public function ajax_get_activity(){
        check_ajax_referer('im_nonce');
        $rows = IM_Database::recent_activity(15);
        wp_send_json_success($rows);
    }
}
