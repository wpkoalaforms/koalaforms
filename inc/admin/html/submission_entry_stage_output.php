<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
if (empty($stage)) return;

wp_nonce_field('koalaforms_submission_stage_nonce_action', 'koalaforms_submission_stage_nonce');

$stage_list = [];
foreach ($stage as $s) {
    $label = is_string($s) ? trim($s) : (isset($s['label']) ? trim($s['label']) : '');
    if ($label !== '') {
        $stage_list[] = $label;
    }
}

if (empty($stage_list)) return;

$current_index    = array_search($current_stage, $stage_list, true);
$stage_is_removed = !empty($current_stage) && $current_index === false;
?>

<input type="hidden" name="koalaforms_submission_stage" id="koalaforms_submission_stage_value"
       value="<?php echo esc_attr($current_stage); ?>">
<textarea name="koalaforms_stage_note" id="koalaforms_stage_note" style="display:none;"></textarea>

<?php if ($stage_is_removed): ?>
<div style="margin-bottom:10px;padding:6px 10px;background:#fff8e1;border:1px solid #ffe082;border-radius:3px;font-size:12px;color:#7a5c00;">
    <?php printf(
        /* translators: %s: name of the removed stage */
        esc_html__('The stage "%s" has been removed from this form\'s settings. This submission will remain unaffected but you may want to reassign it to an active stage.', 'koalaforms'),
        esc_html($current_stage)
    ); ?>
</div>
<?php endif; ?>

<div id="kf-stage-row">
    <div id="kf-stage-path">
    <?php foreach ($stage_list as $i => $label):
        $is_active   = ($current_stage === $label);
        $is_complete = ($current_index !== false && $i < $current_index);
        $classes = 'kf-step';
        if ($is_active)       $classes .= ' is-active';
        elseif ($is_complete) $classes .= ' is-complete';
    ?>
        <div class="<?php echo esc_attr($classes); ?>"
             data-stage="<?php echo esc_attr($label); ?>"
             title="<?php echo esc_attr($label); ?>"
             onclick="kfSelectStage(this)">
            <span class="kf-step-label"><?php echo esc_html($label); ?></span>
        </div>
    <?php endforeach; ?>
    </div>

</div>

<!-- Stage note modal -->
<div id="kf-stage-note-modal" class="kf-modal-view" style="display:none;">
    <div class="kf-modal-overlay kf-modal-overlay-fade-in" id="kf-stage-modal-overlay"></div>
    <div class="popup-content kf-modal-wrap kf-modal-sm kf-modal-out">
        <div class="kf-modal-body kf-p-4">
            <div>
                <h3 class="kf-modal-title kf-m-0 kf-pb-2">
                    <?php esc_html_e("Moving to Stage", 'koalaforms'); ?>
                </h3>
                <span class="kf-modal-close kf-position-absolute kf-cursor"
                      id="kf-stage-modal-close">&times;</span>
            </div>
            <div class="kf-modal-content-wrap kf-mt-4">
                <label for="koalaforms_stage_note_input"
                       style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;">
                    <?php esc_html_e("Note", 'koalaforms'); ?>
                    <span style="font-weight:400;color:#888;">
                        <?php esc_html_e("(optional)", 'koalaforms'); ?>
                    </span>
                </label>
                <textarea id="koalaforms_stage_note_input"
                          rows="3"
                          style="width:100%;font-size:12px;resize:vertical;box-sizing:border-box;"
                          placeholder="<?php echo esc_attr(__("Why is this stage changing?", 'koalaforms')); ?>"></textarea>
                <div style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" class="button" id="kf-stage-modal-cancel">
                        <?php esc_html_e("Cancel", 'koalaforms'); ?>
                    </button>
                    <button type="button" class="button button-primary" id="kf-stage-modal-apply">
                        &#10003; <?php esc_html_e("Apply Changes", 'koalaforms'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

