<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$browser = !empty($submission['browser']) ? $submission['browser'] : 'Unknown';
$device  = !empty($submission['device'])  ? $submission['device']  : 'Unknown';
$user    = empty($submission['user_id'])  ? (object) ['display_name' => 'Guest'] : get_userdata($submission['user_id']);
$unique_id = empty($submission['unique_id']) ? null : $submission['unique_id'];

?>
<table class="widefat striped">
    <tbody>
        <tr>
            <td><b>Form Name</b></th>
            <td><b><?php echo esc_html($submission['form_name']); ?></b></td>
        </tr>

        <?php if(!empty($unique_id )): ?>
            <tr>
                <td><b>Unique ID</b></th>
                <td><b><?php echo esc_html($unique_id); ?></b></td>
            </tr>
        <?php endif; ?>

        <tr>
            <td><b>Browser</b></th>
            <td><b><?php echo esc_html($browser); ?></b></td>
        </tr>

        <tr>
            <td><b>Device</b></th>
            <td><b><?php echo esc_html($device); ?></b></td>
        </tr>

        <tr>
            <td><b>User</b></th>
            <?php if($user->display_name == 'Guest'): ?>
                <td><?php echo esc_html($user->display_name); ?></td>
            <?php endif; ?>

            <?php if($user->display_name != 'Guest'): ?>
                <td>
                    <a target="_blank" href="<?php echo esc_attr(get_edit_user_link($submission['user_id'] ?? 0)); ?>"><?php echo esc_html($user->display_name); ?></a>
                </td>
            <?php endif; ?>
        </tr>

        <tr>
            <td><b>Submitted On</b></th>
            <td><b><?php echo esc_html($submission['submission_date']); ?></b></td>
        </tr>
    </tbody>
</table>

<?php if (!empty($stage_history)): ?>
<h4 style="margin:16px 0 8px;font-size:12px;text-transform:uppercase;color:#888;letter-spacing:.5px;">
    <?php esc_html_e('Stage History', 'koalaforms'); ?>
</h4>
<ul style="margin:0;padding:0;list-style:none;">
    <?php foreach (array_reverse($stage_history) as $entry):
        $changed_by_user = !empty($entry['changed_by']) ? get_userdata($entry['changed_by']) : null;
        $by_name         = $changed_by_user ? $changed_by_user->display_name : __('Unknown', 'koalaforms');
        $by_link         = $changed_by_user ? get_edit_user_link($entry['changed_by']) : '';
        $date            = !empty($entry['changed_at'])
                            ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($entry['changed_at']))
                            : '—';
    ?>
    <li style="padding:8px 0;border-bottom:1px solid #f0f0f0;font-size:12px;">
        <span style="display:inline-block;background:#0176d3;color:#fff;font-size:10px;font-weight:600;padding:2px 7px;border-radius:3px;margin-bottom:4px;">
            <?php echo esc_html($entry['stage']); ?>
        </span>
        <span style="display:block;color:#555;">
            <?php if ($by_link): ?>
                <a href="<?php echo esc_attr($by_link); ?>" target="_blank"><?php echo esc_html($by_name); ?></a>
            <?php else: ?>
                <?php echo esc_html($by_name); ?>
            <?php endif; ?>
            &middot; <?php echo esc_html($date); ?>
        </span>
        <?php if (!empty($entry['note'])): ?>
            <span style="display:block;color:#777;margin-top:3px;font-style:italic;">
                &ldquo;<?php echo esc_html($entry['note']); ?>&rdquo;
            </span>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>