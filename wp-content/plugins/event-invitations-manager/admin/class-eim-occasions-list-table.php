<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EIM_Occasions_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => 'مناسبة',
            'plural'   => 'المناسبات',
            'ajax'     => false
        ] );
    }

    public static function get_occasions( $per_page = 5, $page_number = 1 ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}eim_occasions";

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        } else {
             $sql .= ' ORDER BY created_at DESC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    public static function delete_occasion( $id ) {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}eim_occasions",
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    public static function record_count() {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}eim_occasions";
        return $wpdb->get_var( $sql );
    }

    public function no_items() {
        echo 'لم يتم العثور على مناسبات.';
    }

    function column_name( $item ) {
        $title = '<strong>' . $item['name'] . '</strong>';

        $actions = [
            'edit'   => sprintf( '<a href="?page=%s&action=%s&occasion=%d">تعديل</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ) ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&occasion=%d&_wpnonce=%s">حذف</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), wp_create_nonce( 'eim_delete_occasion' ) ),
        ];

        return $title . $this->row_actions( $actions );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'event_date':
                return date( 'Y-m-d H:i', strtotime($item[ $column_name ]) );
            case 'created_at':
                return date( 'Y-m-d H:i', strtotime($item[ $column_name ]) );
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'name'    => 'الاسم',
            'event_date' => 'تاريخ المناسبة',
            'created_at' => 'تاريخ الإنشاء'
        ];

        return $columns;
    }

    public function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array( 'name', true ),
            'event_date' => array('event_date', false),
            'created_at' => array( 'created_at', false )
        );

        return $sortable_columns;
    }

    public function get_bulk_actions() {
        $actions = [
            'bulk-delete' => 'حذف'
        ];

        return $actions;
    }

    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();

        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'occasions_per_page', 5 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );

        $this->items = self::get_occasions( $per_page, $current_page );
    }

    public function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( ! wp_verify_nonce( $nonce, 'eim_delete_occasion' ) ) {
                die( 'Security check failed.' );
            }
            else {
                self::delete_occasion( absint( $_GET['occasion'] ) );
                wp_redirect( esc_url_raw(add_query_arg()) );
                exit;
            }
        }

        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {
            $delete_ids = esc_sql( $_POST['bulk-delete'] );
            foreach ( $delete_ids as $id ) {
                self::delete_occasion( $id );
            }
            wp_redirect( esc_url_raw(add_query_arg()) );
            exit;
        }
    }
}
