<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$sub_model = Submission::create_instance();
$submissions = $sub_model->latest_submissions(-1);

$search_term = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$form_filter = isset($_GET['form_filter']) ? absint($_GET['form_filter']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$status_filter = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$orderby = isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'submitted_on'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order = isset($_GET['order']) ? strtolower(sanitize_key(wp_unslash($_GET['order']))) : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order = 'asc' === $order ? 'asc' : 'desc';

$filtered_submissions = array();
foreach ($submissions as $submission) {
    if ($form_filter && absint($submission['form_id']) !== $form_filter) {
        continue;
    }

    $status = !empty($submission['submission_status']) ? sanitize_key($submission['submission_status']) : 'unread';
    if ($status_filter && $status_filter !== $status) {
        continue;
    }

    if ('' !== $search_term) {
        $haystack = array(
            (string) (!empty($submission['ID']) ? $submission['ID'] : ''),
            (string) (!empty($submission['form_name']) ? $submission['form_name'] : ''),
            (string) (!empty($submission['browser']) ? $submission['browser'] : ''),
            (string) (!empty($submission['device']) ? $submission['device'] : ''),
            (string) (!empty($submission['unique_id']) ? $submission['unique_id'] : ''),
            (string) (!empty($submission['submission_date']) ? $submission['submission_date'] : ''),
        );

        if (!empty($submission['user_id'])) {
            $user = get_userdata(absint($submission['user_id']));
            if (!empty($user)) {
                $haystack[] = $user->display_name;
                $haystack[] = $user->user_email;
            }
        }

        if (!empty($submission['form_fields']) && is_array($submission['form_fields'])) {
            foreach ($submission['form_fields'] as $field) {
                if (!empty($field['field']['attrs']['inputLabel'])) {
                    $haystack[] = $field['field']['attrs']['inputLabel'];
                }
                if (isset($field['value'])) {
                    $field_value = is_scalar($field['value']) ? (string) $field['value'] : wp_json_encode($field['value']);
                    $haystack[] = $field_value;
                }
            }
        }

        $matched = false;
        foreach ($haystack as $value) {
            if ('' !== $value && false !== stripos($value, $search_term)) {
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            continue;
        }
    }

    $filtered_submissions[] = $submission;
}

$sorters = array(
    'id' => function($a, $b) use ($order) {
        $result = absint($a['ID']) <=> absint($b['ID']);
        return 'asc' === $order ? $result : -$result;
    },
    'form_name' => function($a, $b) use ($order) {
        $result = strcasecmp((string) $a['form_name'], (string) $b['form_name']);
        return 'asc' === $order ? $result : -$result;
    },
    'status' => function($a, $b) use ($order) {
        $a_status = !empty($a['submission_status']) ? sanitize_key($a['submission_status']) : 'unread';
        $b_status = !empty($b['submission_status']) ? sanitize_key($b['submission_status']) : 'unread';
        $result = strcasecmp($a_status, $b_status);
        return 'asc' === $order ? $result : -$result;
    },
    'first_field' => function($a, $b) use ($order) {
        $a_first = '';
        $b_first = '';
        foreach (array($a, $b) as $index => $submission_row) {
            $preview = '';
            if (!empty($submission_row['form_fields']) && is_array($submission_row['form_fields'])) {
                $first_field = reset($submission_row['form_fields']);
                if (is_array($first_field)) {
                    $first_field_label = !empty($first_field['field']['attrs']['inputLabel']) ? $first_field['field']['attrs']['inputLabel'] : '';
                    $first_field_value = isset($first_field['value']) ? (is_scalar($first_field['value']) ? (string) $first_field['value'] : wp_json_encode($first_field['value'])) : '';
                    $preview = trim($first_field_label . ' ' . wp_trim_words(wp_strip_all_tags($first_field_value), 10, '…'));
                }
            }
            if (0 === $index) {
                $a_first = $preview;
            } else {
                $b_first = $preview;
            }
        }
        $result = strcasecmp($a_first, $b_first);
        return 'asc' === $order ? $result : -$result;
    },
    'submitted_on' => function($a, $b) use ($order) {
        $a_time = !empty($a['submission_date']) ? strtotime($a['submission_date']) : 0;
        $b_time = !empty($b['submission_date']) ? strtotime($b['submission_date']) : 0;
        $result = $a_time <=> $b_time;
        return 'asc' === $order ? $result : -$result;
    },
);

if (isset($sorters[$orderby])) {
    usort($filtered_submissions, $sorters[$orderby]);
}

$sort_url = function($column) use ($search_term, $form_filter, $status_filter, $orderby, $order) {
    $next_order = ($orderby === $column && 'asc' === $order) ? 'desc' : 'asc';
    return add_query_arg(array_filter(array(
        'page' => 'koalaforms-submissions',
        's' => '' !== $search_term ? $search_term : null,
        'form_filter' => $form_filter ? $form_filter : null,
        'status' => '' !== $status_filter ? $status_filter : null,
        'orderby' => $column,
        'order' => $next_order,
    ), static function($value) {
        return null !== $value && '' !== $value;
    }), admin_url('admin.php'));
};

$submission_count = count($submissions);
$unread_count = 0;
foreach ($submissions as $submission) {
    if (empty($submission['submission_status']) || 'read' !== $submission['submission_status']) {
        $unread_count++;
    }
}

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
            <a href="<?php echo esc_url(admin_url('admin.php?page=koalaforms-submissions')); ?>" class="<?php echo empty($search_term) && empty($form_filter) && empty($status_filter) ? 'current' : ''; ?>" aria-current="<?php echo empty($search_term) && empty($form_filter) && empty($status_filter) ? 'page' : 'false'; ?>">
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

    <form method="get">
        <input type="hidden" name="page" value="koalaforms-submissions" />

        <div class="tablenav top">
            <div class="alignleft actions">
                <label for="filter-by-form" class="screen-reader-text"><?php echo esc_html__('Filter by form', 'koalaforms'); ?></label>
                <select name="form_filter" id="filter-by-form">
                    <option value=""><?php echo esc_html__('All Form Entries', 'koalaforms'); ?></option>
                    <?php foreach ($forms as $form) : ?>
                        <option value="<?php echo esc_attr($form->ID); ?>" <?php selected($form_filter, $form->ID); ?>>
                            <?php echo esc_html($form->post_title ? $form->post_title : __('Untitled form', 'koalaforms')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button(__('Filter', 'koalaforms'), 'secondary', '', false); ?>
            </div>

            <h2 class="screen-reader-text"><?php echo esc_html__('Search Submissions', 'koalaforms'); ?></h2>
            <p class="search-box">
                <label class="screen-reader-text" for="submission-search-input"><?php echo esc_html__('Search Submissions', 'koalaforms'); ?></label>
                <input type="search" id="submission-search-input" name="s" value="<?php echo esc_attr($search_term); ?>" />
                <?php submit_button(__('Search', 'koalaforms'), 'button', '', false, array('id' => 'search-submit')); ?>
            </p>
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
                    <th scope="col" class="manage-column <?php echo $is_sorted_first_field ? 'sorted ' . esc_attr($order) : 'sortable'; ?>" <?php echo $is_sorted_first_field ? 'aria-sort="' . esc_attr('asc' === $order ? 'ascending' : 'descending') . '"' : ''; ?>>
                        <a href="<?php echo esc_url($sort_url('first_field')); ?>">
                            <span><?php echo esc_html__('First Field', 'koalaforms'); ?></span>
                            <span class="sorting-indicator <?php echo $is_sorted_first_field ? esc_attr($order) : ''; ?>"></span>
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
                                $first_field_label = !empty($first_field['field']['attrs']['inputLabel']) ? $first_field['field']['attrs']['inputLabel'] : '';
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
                            <td data-colname="<?php echo esc_attr__('First Field', 'koalaforms'); ?>">
                                <span class="kf-first-field-preview">
                                    <?php echo !empty($first_field_preview) ? esc_html($first_field_preview) : esc_html__('No field preview available', 'koalaforms'); ?>
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

        
    </form>
</div>
