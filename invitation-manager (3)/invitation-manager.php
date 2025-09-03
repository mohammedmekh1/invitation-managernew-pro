<?php
/*
Plugin Name: Invitation Manager
Description: إدارة الدعوات والمدعوين والمناسبات مع لوحة تحكم عصرية ونظام مسح QR.
Version: 1.0.0
Author: Mohammed Almakhlafi
Text Domain: invitation-manager
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

define('IM_PATH', plugin_dir_path(__FILE__));
define('IM_URL', plugin_dir_url(__FILE__));
define('IM_VER', '1.0.0');

require_once IM_PATH . 'includes/class-im-database.php';
require_once IM_PATH . 'includes/class-im-admin.php';
require_once IM_PATH . 'includes/class-im-qr.php';

/**
 * التفعيل: إنشاء الجداول
 */
function im_activate_plugin(){
    IM_Database::install();
}
register_activation_hook(__FILE__, 'im_activate_plugin');

/**
 * التحميل
 */
function im_boot(){
    new IM_Admin();
    new IM_QR();
}
add_action('plugins_loaded', 'im_boot');
