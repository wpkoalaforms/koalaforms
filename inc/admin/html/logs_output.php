<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Logs', 'koalaforms' ); ?></h1>

    <?php if ( ! empty( $notice ) ) : ?>
        <div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
            <p><?php echo esc_html( $notice['message'] ); ?></p>
        </div>
    <?php endif; ?>

    <?php if ( count( $integrations ) > 1 ) : ?>
    <form method="get" style="margin-bottom:12px">
        <input type="hidden" name="page" value="koalaforms-logs">
        <label for="kf-log-integration" style="font-weight:600;margin-right:6px">
            <?php esc_html_e( 'Integration:', 'koalaforms' ); ?>
        </label>
        <select id="kf-log-integration" name="integration" onchange="this.form.submit()">
            <?php foreach ( $integrations as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $integration, $key ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>

    <form method="post" id="kf-logs-form">
        <?php
        wp_nonce_field( 'bulk-logs' );
        $list_table->display();
        ?>
    </form>

    <?php if ( Logger::get_count( $integration ) > 0 ) : ?>
    <form method="post" style="margin-top:16px">
        <?php wp_nonce_field( 'kf_clear_logs', 'kf_clear_logs_nonce' ); ?>
        <input type="hidden" name="integration" value="<?php echo esc_attr( $integration ); ?>">
        <button type="submit" name="kf_clear_logs" class="button button-secondary"
                onclick="return confirm('<?php esc_attr_e( 'Clear all logs for this integration?', 'koalaforms' ); ?>')">
            <?php esc_html_e( 'Clear All Logs', 'koalaforms' ); ?>
        </button>
    </form>
    <?php endif; ?>
</div>
