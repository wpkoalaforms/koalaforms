<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$submissions_url = admin_url( 'admin.php?' . http_build_query( array(
    'page'       => 'koalaforms-submissions',
    'kf_form_id' => absint( $submission['form_id'] ?? 0 ),
) ) );
?>

<div class="kf-sub-toolbar">
    <a href="<?php echo esc_url( $submissions_url ); ?>" class="kf-sub-back-btn">
        <span aria-hidden="true">&#8592;</span> <?php esc_html_e( 'Back to Submissions', 'koalaforms' ); ?>
    </a>
</div>

<table class="kf-sub-fields-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Field', 'koalaforms' ); ?></th>
            <th><?php esc_html_e( 'Value', 'koalaforms' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $submission['form_fields'] as $item ) :
            $attrs       = $item['field']['attrs'];
            $field_value = $item['display_value'] ?? $item['value'];
        ?>
        <tr>
            <td class="kf-sub-field-label"><?php echo esc_html( $attrs['displayLabel'] ?? '' ); ?></td>
            <td><?php echo nl2br( esc_html( $field_value ) ); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
