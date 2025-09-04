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
        SUM(CASE WHEN g.rsvp_status = 'attending' THEN 1 ELSE 0 END) AS attending_guests,
        SUM(g.plus_one_attending) AS plus_ones,
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

// Pass data to the stats script
wp_localize_script( 'event-invitations-manager-stats', 'eim_stats_data', $stats );
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <p>هنا ملخص استجابات المدعوين لكل مناسبة.</p>

    <div id="eim-charts-container" style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <?php foreach ( $stats as $index => $stat ) : ?>
            <div class="eim-chart-wrapper" style="flex: 1; min-width: 300px; max-width: 400px; padding: 15px; background: #fff; border: 1px solid #ccc;">
                <canvas id="eim-chart-<?php echo $index; ?>"></canvas>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>ملخص جدولي</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col">المناسبة</th>
                <th scope="col">إجمالي الدعوات</th>
                <th scope="col">الحضور المؤكد</th>
                <th scope="col">الضيوف الإضافيون (+1)</th>
                <th scope="col">إجمالي الحضور</th>
                <th scope="col">معتذر</th>
                <th scope="col">بانتظار الرد</th>
                <th scope="col">تم تسجيل دخولهم</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $stats ) ) : ?>
                <?php
                foreach ( $stats as $row ) :
                    $total_attending = absint($row['attending_guests']) + absint($row['plus_ones']);
                ?>
                    <tr>
                        <td><strong><?php echo esc_html( $row['occasion_name'] ); ?></strong></td>
                        <td><?php echo absint( $row['total_guests'] ); ?></td>
                        <td><?php echo absint( $row['attending_guests'] ); ?></td>
                        <td><?php echo absint( $row['plus_ones'] ); ?></td>
                        <td><strong><?php echo $total_attending; ?></strong></td>
                        <td><?php echo absint( $row['not_attending'] ); ?></td>
                        <td><?php echo absint( $row['pending'] ); ?></td>
                        <td><?php echo absint( $row['checked_in'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8">لم يتم العثور على مناسبات.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
