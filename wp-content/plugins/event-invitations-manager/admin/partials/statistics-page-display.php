<?php
/**
 * Provide an admin area view for the statistics page.
 */

global $wpdb;
$table_guests = $wpdb->prefix . 'eim_guests';
$table_occasions = $wpdb->prefix . 'eim_occasions';

$stats = $wpdb->get_results( "
    SELECT
        o.name AS occasion_name,
        COUNT(g.id) AS total_guests,
        SUM(CASE WHEN g.rsvp_status = 'attending' THEN 1 ELSE 0 END) AS attending,
        SUM(CASE WHEN g.rsvp_status = 'not_attending' THEN 1 ELSE 0 END) AS not_attending,
        SUM(CASE WHEN g.rsvp_status = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(g.check_in_status) AS checked_in
    FROM
        {$table_occasions} o
    LEFT JOIN
        {$table_guests} g ON o.id = g.occasion_id
    GROUP BY
        o.id
", ARRAY_A );

?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <p><?php _e( 'Here is a summary of the guest responses for each occasion.', 'eim' ); ?></p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col"><?php _e('Occasion', 'eim'); ?></th>
                <th scope="col"><?php _e('Total Guests', 'eim'); ?></th>
                <th scope="col"><?php _e('Attending', 'eim'); ?></th>
                <th scope="col"><?php _e('Not Attending', 'eim'); ?></th>
                <th scope="col"><?php _e('Pending Response', 'eim'); ?></th>
                <th scope="col"><?php _e('Guests Checked In', 'eim'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $stats ) ) : ?>
                <?php foreach ( $stats as $row ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $row['occasion_name'] ); ?></strong></td>
                        <td><?php echo absint( $row['total_guests'] ); ?></td>
                        <td><?php echo absint( $row['attending'] ); ?></td>
                        <td><?php echo absint( $row['not_attending'] ); ?></td>
                        <td><?php echo absint( $row['pending'] ); ?></td>
                        <td><?php echo absint( $row['checked_in'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6"><?php _e('No occasions found.', 'eim'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
