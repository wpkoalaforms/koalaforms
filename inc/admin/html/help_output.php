<?php
namespace KoalaForms;

if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$help_page_url = admin_url('admin.php?page=koalaforms-help');
$video_url = $help_page_url . '#video';
$docs_url = $help_page_url . '#docs';
$addons_url = $help_page_url . '#addons';
$support_url = $help_page_url . '#support';
?>
<div class="wrap">
    <h1><?php echo esc_html__('KoalaForms Help Center', 'koalaforms'); ?></h1>
    <p><?php echo esc_html__('Find the fastest path to set up forms, learn the block-editor workflow, and discover premium addons without leaving WordPress.', 'koalaforms'); ?></p>

    <div id="video" class="postbox" style="padding: 16px; margin-top: 20px;">
        <h2><?php echo esc_html__('Onboarding Video', 'koalaforms'); ?></h2>
        <p><?php echo esc_html__('Start with the walkthrough to learn how KoalaForms fits inside the Gutenberg workflow.', 'koalaforms'); ?></p>
        <p><a class="button button-primary" href="<?php echo esc_url($video_url); ?>"><?php echo esc_html__('Open video section', 'koalaforms'); ?></a></p>
    </div>

    <div id="docs" class="postbox" style="padding: 16px; margin-top: 20px;">
        <h2><?php echo esc_html__('Docs and Guides', 'koalaforms'); ?></h2>
        <p><?php echo esc_html__('Use this section for setup instructions, form builder guidance, captcha setup, and submission tips.', 'koalaforms'); ?></p>
        <p><a class="button button-primary" href="<?php echo esc_url($docs_url); ?>"><?php echo esc_html__('Open docs section', 'koalaforms'); ?></a></p>
    </div>

    <div id="addons" class="postbox" style="padding: 16px; margin-top: 20px;">
        <h2><?php echo esc_html__('Premium Addons', 'koalaforms'); ?></h2>
        <p><?php echo esc_html__('CRM integrations, automation tools, and premium support are positioned here so the core plugin can stay focused.', 'koalaforms'); ?></p>
        <p><a class="button button-primary" href="<?php echo esc_url($addons_url); ?>"><?php echo esc_html__('Open addon section', 'koalaforms'); ?></a></p>
    </div>

    <div id="support" class="postbox" style="padding: 16px; margin-top: 20px;">
        <h2><?php echo esc_html__('Support', 'koalaforms'); ?></h2>
        <p><?php echo esc_html__('Use this section to direct users to troubleshooting, setup help, or a support contact path.', 'koalaforms'); ?></p>
        <p><a class="button button-primary" href="<?php echo esc_url($support_url); ?>"><?php echo esc_html__('Open support section', 'koalaforms'); ?></a></p>
    </div>
</div>
