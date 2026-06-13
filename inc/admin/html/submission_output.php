<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template partial included from a class method. Variables are local to the included scope, not globals.
?>
<table class="widefat striped">
    <tbody>
        <tr>
            <th><b>Fields</b></th>
            <th><b>Values</b></th>
        </tr>

        <?php foreach ($submission['form_fields'] as $item) {
            $attrs = $item['field']['attrs'];
            $field_value = $item['value'];
        ?>
        <tr>
            <td><b><?php echo esc_html($attrs['inputLabel'] ?? ''); ?></b></td>
            <td><?php echo nl2br(esc_html($field_value)); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
