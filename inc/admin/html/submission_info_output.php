<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$user      = empty($submission['user_id']) ? (object) ['display_name' => 'Guest'] : get_userdata($submission['user_id']);
$unique_id = empty($submission['unique_id']) ? null : $submission['unique_id'];

// Parse browser + OS from user agent
$raw_ua  = !empty($submission['device']) ? $submission['device'] : '';
$browser = !empty($submission['browser']) ? $submission['browser'] : 'Unknown';

$os = 'Unknown';
if ( $raw_ua ) {
    if ( preg_match('/Windows NT/', $raw_ua) )          $os = 'Windows';
    elseif ( preg_match('/Mac OS X/', $raw_ua) )        $os = 'macOS';
    elseif ( preg_match('/Android/', $raw_ua) )         $os = 'Android';
    elseif ( preg_match('/iPhone|iPad/', $raw_ua) )     $os = 'iOS';
    elseif ( preg_match('/Linux/', $raw_ua) )           $os = 'Linux';
}

// Relative time
$submitted_raw  = $submission['submission_date'] ?? '';
$submitted_abs  = $submitted_raw ? date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime($submitted_raw) ) : '—';
$submitted_rel  = $submitted_raw ? human_time_diff( strtotime($submitted_raw), current_time('timestamp') ) . ' ' . __('ago', 'koalaforms') : '';
?>

<div class="kf-sub-info-panel">

    <div class="kf-sub-info-row">
        <span class="kf-sub-info-label"><?php esc_html_e('Form', 'koalaforms'); ?></span>
        <span class="kf-sub-info-value">
            <a href="<?php echo esc_url(admin_url('post.php?post=' . absint($submission['form_id'] ?? 0) . '&action=edit')); ?>" target="_blank">
                <?php echo esc_html($submission['form_name'] ?? '—'); ?>
            </a>
        </span>
    </div>

    <?php if ($unique_id): ?>
    <div class="kf-sub-info-row">
        <span class="kf-sub-info-label"><?php esc_html_e('Unique ID', 'koalaforms'); ?></span>
        <span class="kf-sub-info-value kf-sub-info-mono"><?php echo esc_html($unique_id); ?></span>
    </div>
    <?php endif; ?>

    <div class="kf-sub-info-row">
        <span class="kf-sub-info-label"><?php esc_html_e('Browser', 'koalaforms'); ?></span>
        <span class="kf-sub-info-value"><?php echo esc_html($browser); ?></span>
    </div>

    <div class="kf-sub-info-row">
        <span class="kf-sub-info-label"><?php esc_html_e('OS', 'koalaforms'); ?></span>
        <span class="kf-sub-info-value"><?php echo esc_html($os); ?></span>
    </div>

    <div class="kf-sub-info-row">
        <span class="kf-sub-info-label"><?php esc_html_e('User', 'koalaforms'); ?></span>
        <span class="kf-sub-info-value">
            <?php if ($user->display_name !== 'Guest'): ?>
                <a target="_blank" href="<?php echo esc_attr(get_edit_user_link($submission['user_id'] ?? 0)); ?>">
                    <?php echo esc_html($user->display_name); ?>
                </a>
            <?php else: ?>
                <?php esc_html_e('Guest', 'koalaforms'); ?>
            <?php endif; ?>
        </span>
    </div>

    <div class="kf-sub-info-row">
        <span class="kf-sub-info-label"><?php esc_html_e('Submitted', 'koalaforms'); ?></span>
        <span class="kf-sub-info-value">
            <span class="kf-sub-date-abs"><?php echo esc_html($submitted_abs); ?></span>
            <?php if ($submitted_rel): ?>
                <span class="kf-sub-date-rel"><?php echo esc_html($submitted_rel); ?></span>
            <?php endif; ?>
        </span>
    </div>

</div>

<?php if (!empty($stage_history)): ?>
<div class="kf-sub-stage-history">
    <h4 class="kf-sub-section-heading"><?php esc_html_e('Stage History', 'koalaforms'); ?></h4>
    <ul class="kf-sub-timeline">
        <?php foreach (array_reverse($stage_history) as $i => $entry):
            $changed_by_user = !empty($entry['changed_by']) ? get_userdata($entry['changed_by']) : null;
            $by_name         = $changed_by_user ? $changed_by_user->display_name : __('Unknown', 'koalaforms');
            $by_link         = $changed_by_user ? get_edit_user_link($entry['changed_by']) : '';
            $date            = !empty($entry['changed_at'])
                                ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($entry['changed_at']))
                                : '—';
        ?>
        <li class="kf-sub-timeline-item<?php echo $i === 0 ? ' is-latest' : ''; ?>">
            <span class="kf-sub-timeline-dot"></span>
            <div class="kf-sub-timeline-body">
                <span class="kf-sub-stage-badge"><?php echo esc_html($entry['stage']); ?></span>
                <span class="kf-sub-timeline-meta">
                    <?php if ($by_link): ?>
                        <a href="<?php echo esc_attr($by_link); ?>" target="_blank"><?php echo esc_html($by_name); ?></a>
                    <?php else: ?>
                        <?php echo esc_html($by_name); ?>
                    <?php endif; ?>
                    &middot; <?php echo esc_html($date); ?>
                </span>
                <?php if (!empty($entry['note'])): ?>
                    <span class="kf-sub-timeline-note">&ldquo;<?php echo esc_html($entry['note']); ?>&rdquo;</span>
                <?php endif; ?>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
