<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class IM_QR {

    public function __construct(){
        add_action('wp_ajax_im_verify_qr', array($this, 'ajax_verify_qr'));
    }

    public function ajax_verify_qr(){
        check_ajax_referer('im_nonce');
        $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
        if( empty($token) ){
            wp_send_json_error(array('message'=>'الرمز فارغ'));
        }
        $result = IM_Database::verify_token($token);
        if( $result['success'] ){
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}
