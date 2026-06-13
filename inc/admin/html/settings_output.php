<?php
namespace KoalaForms;

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap kf-settings-page">
    <form method="post" class="kf-settings-form">
        <?php wp_nonce_field('koalaforms_settings_nonce', 'koalaforms_nonce'); ?>
        <?php if (current_user_can('manage_options')) : ?>
            <div id="kf-settings-app"></div>
        <?php endif; ?>
    </form>
</div>
