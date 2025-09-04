<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EIM_Guests_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => 'مدعو',
            'plural'   => 'المدعوون',
            'ajax'     => false
        ] );
    }

    public static function get_guests( $per_page = 20, $page_number = 1, $occasion_id = 0 ) {
        global $wpdb;

        $table_guests = $wpdb->prefix . 'eim_guests';
        $table_occasions = $wpdb->prefix . 'eim_occasions';

        $sql = "SELECT g.*, o.name as occasion_name FROM {$table_guests} g LEFT JOIN {$table_occasions} o ON g.occasion_id = o.id";

        if ( ! empty( $occasion_id ) ) {
            $sql .= $wpdb->prepare( " WHERE g.occasion_id = %d", $occasion_id );
        }

        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        } else {
             $sql .= ' ORDER BY g.created_at DESC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    public static function delete_guest( $id ) {
        global $wpdb;
        $wpdb->delete( "{$wpdb->prefix}eim_guests", [ 'id' => $id ], [ '%d' ] );
    }

    public static function record_count( $occasion_id = 0 ) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}eim_guests";
        if ( ! empty( $occasion_id ) ) {
            $sql .= $wpdb->prepare( " WHERE occasion_id = %d", $occasion_id );
        }
        return $wpdb->get_var( $sql );
    }

    public function no_items() {
        echo 'لم يتم العثور على مدعوين.';
    }

    function column_name( $item ) {
        $title = '<strong>' . $item['name'] . '</strong>';
        $actions = [
            'edit'   => sprintf( '<a href="?page=%s&action=%s&guest=%d">تعديل</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['id'] ) ),
            'delete' => sprintf( '<a href="?page=%s&action=%s&guest=%d&_wpnonce=%s">حذف</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), wp_create_nonce( 'eim_delete_guest' ) ),
        ];
        return $title . $this->row_actions( $actions );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'occasion_name':
            case 'rsvp_status':
            case 'check_in_status':
                return $item[ $column_name ];
            case 'unique_code':
                $invitation_url = home_url( '/invitation/' . $item['unique_code'] . '/' );
                $input_id = 'invitation-url-' . $item['id'];
                $html = '<input type="text" id="' . $input_id . '" value="' . esc_url($invitation_url) . '" readonly style="width: 100%;">';
                $html .= '<button type="button" class="button button-secondary eim-copy-button" data-target="#' . $input_id . '">نسخ</button>';
                $html .= ' <a href="' . esc_url($invitation_url) . '" target="_blank" class="button button-secondary">عرض</a>';
                return $html;
            case 'created_at':
                return date( 'Y-m-d H:i', strtotime($item[ $column_name ]) );
            default:
                return print_r( $item, true );
        }
    }

    function column_cb( $item ) {
        return sprintf( '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id'] );
    }

    function get_columns() {
        return [
            'cb'      => '<input type="checkbox" />',
            'name'    => 'الاسم',
            'occasion_name' => 'المناسبة',
            'unique_code' => 'رابط الدعوة',
            'rsvp_status' => 'حالة الحضور',
            'check_in_status' => 'تم تسجيل الدخول',
            'created_at' => 'تاريخ الإنشاء'
        ];
    }

    public function get_sortable_columns() {
        return [
            'name' => [ 'name', true ],
            'occasion_name' => [ 'occasion_name', false ],
            'created_at' => [ 'created_at', false ]
        ];
    }

    public function get_bulk_actions() {
        return [ 'bulk-delete' => 'حذف' ];
    }

    protected function extra_tablenav( $which ) {
        if ( $which == "top" ){
            global $wpdb;
            $occasions = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}eim_occasions ORDER BY name ASC" );
            $current_occasion = isset( $_REQUEST['occasion_id'] ) ? $_REQUEST['occasion_id'] : '';
            ?>
            <div class="alignleft actions">
                <select name="occasion_id">
                    <option value="">كل المناسبات</option>
                    <?php foreach ( $occasions as $occasion ) : ?>
                        <option value="<?php echo $occasion->id; ?>" <?php selected( $current_occasion, $occasion->id ); ?>><?php echo esc_html( $occasion->name ); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button( 'فرز', 'secondary', 'filter_action', false ); ?>
            </div>
            <?php
        }
    }

    public function prepare_items() {
        $this->_column_headers = $this->get_column_info();
        $this->process_bulk_action();

        $per_page     = $this->get_items_per_page( 'guests_per_page', 20 );
        $current_page = $this->get_pagenum();
        $occasion_id  = isset( $_REQUEST['occasion_id'] ) ? absint( $_REQUEST['occasion_id'] ) : 0;

        $total_items  = self::record_count( $occasion_id );

        $this->set_pagination_args( [
            'total_items' => $total_items,
            'per_page'    => $per_page
        ] );

        $this->items = self::get_guests( $per_page, $current_page, $occasion_id );
    }

    public function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( ! wp_verify_nonce( $nonce, 'eim_delete_guest' ) ) {
                die( 'Action failed.' );
            } else {
                self::delete_guest( absint( $_GET['guest'] ) );
                // Re-direct to the same page
                wp_redirect( remove_query_arg( [ 'action', 'guest', '_wpnonce' ] ) );
                exit;
            }
        }

        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' ) ) {
            $delete_ids = esc_sql( $_POST['bulk-delete'] );
            foreach ( $delete_ids as $id ) {
                self::delete_guest( $id );
            }
            wp_redirect( remove_query_arg( [ 'action', 'guest', '_wpnonce' ] ) );
            exit;
        }
    }
}
