<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$sub_model = Submission::create_instance();

$form_filter   = isset($_GET['kf_form_id']) ? absint($_GET['kf_form_id']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$status_filter = isset($_GET['status'])      ? sanitize_key(wp_unslash($_GET['status'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$orderby       = isset($_GET['orderby'])     ? sanitize_key(wp_unslash($_GET['orderby'])) : 'submitted_on'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order         = isset($_GET['order'])       ? strtolower(sanitize_key(wp_unslash($_GET['order']))) : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order         = 'asc' === $order ? 'asc' : 'desc';
$current_page  = isset($_GET['paged'])       ? max(1, absint($_GET['paged'])) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$per_page = 20;

$result               = $sub_model->latest_submissions(array(
    'per_page' => $per_page,
    'paged'    => $current_page,
    'form_id'  => $form_filter,
    'status'   => $status_filter,
    'orderby'  => $orderby,
    'order'    => $order,
));
$filtered_submissions = $result['items'];
$total_submissions    = $result['total'];

$total_pages = (int) ceil($total_submissions / $per_page);

// Counts for the status tabs — lightweight query, no meta hydration.
$all_result    = $sub_model->latest_submissions(array('per_page' => 1, 'paged' => 1));
$submission_count = $all_result['total'];
$unread_result    = $sub_model->latest_submissions(array('per_page' => 1, 'paged' => 1, 'status' => 'unread'));
$unread_count     = $unread_result['total'];

$sort_url = function($column) use ($form_filter, $status_filter, $orderby, $order) {
    $next_order = ($orderby === $column && 'asc' === $order) ? 'desc' : 'asc';
    return add_query_arg(array_filter(array(
        'page'        => 'koalaforms-submissions',
        'kf_form_id' => $form_filter ? $form_filter : null,
        'status'      => '' !== $status_filter ? $status_filter : null,
        'orderby'     => $column,
        'order'       => $next_order,
    ), static function($value) {
        return null !== $value && '' !== $value;
    }), admin_url('admin.php'));
};

$form_model = Form::create_instance();
$forms = $form_model->get('', array(
    'post_type'        => AppUtility::FORM_POST_TYPE,
    'post_status'      => 'any',
    'orderby'          => 'date',
    'order'            => 'DESC',
    'posts_per_page'   => -1,
    'no_found_rows'    => true,
    'suppress_filters' => false,
));

$is_sorted_id = 'id' === $orderby;
$is_sorted_form_name = 'form_name' === $orderby;
$is_sorted_status = 'status' === $orderby;
$is_sorted_first_field = 'first_field' === $orderby;
$is_sorted_submitted_on = 'submitted_on' === $orderby;
?>

<div class="wrap kf-submissions-page">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Submissions', 'koalaforms'); ?></h1>
    <hr class="wp-header-end">

    <ul class="subsubsub">
        <li class="all">
            <a href="<?php echo esc_url(admin_url('admin.php?page=koalaforms-submissions')); ?>" class="<?php echo empty($form_filter) && empty($status_filter) ? 'current' : ''; ?>" aria-current="<?php echo empty($form_filter) && empty($status_filter) ? 'page' : 'false'; ?>">
                <?php echo esc_html__('All', 'koalaforms'); ?> <span class="count">(<?php echo esc_html($submission_count); ?>)</span>
            </a>
            |
        </li>
        <li class="unread">
            <a href="<?php echo esc_url(add_query_arg(array('status' => 'unread'), admin_url('admin.php?page=koalaforms-submissions'))); ?>" class="<?php echo 'unread' === $status_filter ? 'current' : ''; ?>" aria-current="<?php echo 'unread' === $status_filter ? 'page' : 'false'; ?>">
                <?php echo esc_html__('Unread', 'koalaforms'); ?> <span class="count">(<?php echo esc_html($unread_count); ?>)</span>
            </a>
        </li>
    </ul>

    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
        <input type="hidden" name="page" value="koalaforms-submissions" />

        <div class="tablenav top">
            <div class="alignleft actions">
                <label for="filter-by-form" class="screen-reader-text"><?php echo esc_html__('Filter by form', 'koalaforms'); ?></label>
                <select name="kf_form_id" id="filter-by-form">
                    <option value=""><?php echo esc_html__('All Form Entries', 'koalaforms'); ?></option>
                    <?php foreach ($forms as $form) : ?>
                        <option value="<?php echo esc_attr($form->ID); ?>" <?php selected($form_filter, $form->ID); ?>>
                            <?php echo esc_html($form->post_title ? $form->post_title : __('Untitled form', 'koalaforms')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button(__('Filter', 'koalaforms'), 'secondary', '', false); ?>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input id="cb-select-all-1" type="checkbox" />
                    </td>
                    <th scope="col" class="manage-column column-primary column-title <?php echo $is_sorted_id ? 'sorted ' . esc_attr($order) : 'sortable'; ?>" <?php echo $is_sorted_id ? 'aria-sort="' . esc_attr('asc' === $order ? 'ascending' : 'descending') . '"' : ''; ?>>
                        <a href="<?php echo esc_url($sort_url('id')); ?>">
                            <span><?php echo esc_html__('ID', 'koalaforms'); ?></span>
                            <span class="sorting-indicator <?php echo $is_sorted_id ? esc_attr($order) : ''; ?>"></span>
                        </a>
                    </th>
                    <th scope="col" class="manage-column <?php echo $is_sorted_form_name ? 'sorted ' . esc_attr($order) : 'sortable'; ?>" <?php echo $is_sorted_form_name ? 'aria-sort="' . esc_attr('asc' === $order ? 'ascending' : 'descending') . '"' : ''; ?>>
                        <a href="<?php echo esc_url($sort_url('form_name')); ?>">
                            <span><?php echo esc_html__('Form Name', 'koalaforms'); ?></span>
                            <span class="sorting-indicator <?php echo $is_sorted_form_name ? esc_attr($order) : ''; ?>"></span>
                        </a>
                    </th>
                    <th scope="col" class="manage-column <?php echo $is_sorted_status ? 'sorted ' . esc_attr($order) : 'sortable'; ?>" <?php echo $is_sorted_status ? 'aria-sort="' . esc_attr('asc' === $order ? 'ascending' : 'descending') . '"' : ''; ?>>
                        <a href="<?php echo esc_url($sort_url('status')); ?>">
                            <span><?php echo esc_html__('Status', 'koalaforms'); ?></span>
                            <span class="sorting-indicator <?php echo $is_sorted_status ? esc_attr($order) : ''; ?>"></span>
                        </a>
                    </th>
                    
                    <th scope="col" class="manage-column <?php echo $is_sorted_submitted_on ? 'sorted ' . esc_attr($order) : 'sortable'; ?>" <?php echo $is_sorted_submitted_on ? 'aria-sort="' . esc_attr('asc' === $order ? 'ascending' : 'descending') . '"' : ''; ?>>
                        <a href="<?php echo esc_url($sort_url('submitted_on')); ?>">
                            <span><?php echo esc_html__('Submitted On', 'koalaforms'); ?></span>
                            <span class="sorting-indicator <?php echo $is_sorted_submitted_on ? esc_attr($order) : ''; ?>"></span>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php if (!empty($filtered_submissions)) : ?>
                    <?php foreach ($filtered_submissions as $submission) : ?>
                        <?php
                        $row_status   = !empty($submission['submission_status']) ? sanitize_key($submission['submission_status']) : 'unread';
                        $status_label = 'read' === $row_status ? __('Read', 'koalaforms') : __('Unread', 'koalaforms');
                        $first_field_label = '';
                        $first_field_value = '';
                        if (!empty($submission['form_fields']) && is_array($submission['form_fields'])) {
                            $first_field = reset($submission['form_fields']);
                            if (is_array($first_field)) {
                                $first_field_label = $first_field['field']['attrs']['displayLabel'] ?? '';
                                $first_field_value = isset($first_field['value']) ? (is_scalar($first_field['value']) ? (string) $first_field['value'] : wp_json_encode($first_field['value'])) : '';
                            }
                        }
                        $first_field_preview = trim($first_field_label . ' ' . wp_trim_words(wp_strip_all_tags($first_field_value), 10, '…'));
                        ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="submission_ids[]" value="<?php echo esc_attr($submission['ID']); ?>" />
                            </th>
                            <td class="title column-title has-row-actions column-primary" data-colname="<?php echo esc_attr__('ID', 'koalaforms'); ?>">
                                <strong>
                                    <a class="row-title" href="<?php echo esc_url(admin_url('post.php?post=' . absint($submission['ID']) . '&action=edit')); ?>">
                                        <?php echo esc_html('Entry #' . $submission['ID']); ?>
                                    </a>
                                </strong>
                                <button type="button" class="toggle-row">
                                    <span class="screen-reader-text"><?php echo esc_html__('Show more details', 'koalaforms'); ?></span>
                                </button>
                            </td>
                            <td data-colname="<?php echo esc_attr__('Form Name', 'koalaforms'); ?>">
                                <a href="<?php echo esc_url(admin_url('post.php?post=' . absint($submission['form_id'] ?? 0) . '&action=edit')); ?>">
                                    <?php echo esc_html($submission['form_name']); ?>
                                </a>
                            </td>
                            <td data-colname="<?php echo esc_attr__('Status', 'koalaforms'); ?>">
                                <span class="kf-submission-status-badge kf-submission-status-<?php echo esc_attr($row_status); ?>">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                            </td>
                            
                            <td data-colname="<?php echo esc_attr__('Submitted On', 'koalaforms'); ?>">
                                <?php echo !empty($submission['submission_date']) ? esc_html($submission['submission_date']) : esc_html__('—', 'koalaforms'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="no-items">
                        <td class="colspanchange" colspan="6">
                            <?php echo esc_html__('No submissions found.', 'koalaforms'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php
                    printf(
                        /* translators: %s: number of submissions */
                        esc_html(_n('%s item', '%s items', $total_submissions, 'koalaforms')),
                        esc_html(number_format_i18n($total_submissions))
                    );
                    ?>
                </span>
                <?php if ($total_pages > 1) : ?>
                    <span class="pagination-links">
                        <?php
                        echo wp_kses_post(paginate_links(array(
                            'base'      => add_query_arg('paged', '%#%'),
                            'format'    => '',
                            'prev_text' => __('&laquo;', 'koalaforms'),
                            'next_text' => __('&raquo;', 'koalaforms'),
                            'total'     => $total_pages,
                            'current'   => $current_page,
                        )));
                        ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>
