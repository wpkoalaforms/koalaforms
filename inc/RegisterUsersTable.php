<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class RegisterUsersTable extends \WP_List_Table {
    private $current_role = '';

    public function __construct() {
        parent::__construct(array(
            'singular' => 'user',
            'plural'   => 'users',
            'ajax'     => false,
        ));
    }

    public function get_columns() {
        return array(
            'username'   => __('Username', 'koalaforms'),
            'name'       => __('Name', 'koalaforms'),
            'email'      => __('Email', 'koalaforms'),
            'role'       => __('Role', 'koalaforms'),
            'registered' => __('Registered', 'koalaforms'),
        );
    }

    public function no_items() {
        echo esc_html__('No users found.', 'koalaforms');
    }

    public function prepare_items() {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $role = isset($_REQUEST['role']) ? sanitize_key(wp_unslash($_REQUEST['role'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $this->current_role = $role;

        $args = array(
            'number'      => $per_page,
            'offset'      => ($current_page - 1) * $per_page,
            'orderby'     => 'registered',
            'order'       => 'DESC',
            'count_total' => true,
        );

        if (!empty($search)) {
            $args['search'] = '*' . $search . '*';
        }

        if (!empty($role) && 'all' !== $role) {
            if ('none' === $role) {
                $args['role__not_in'] = array_keys(!empty($GLOBALS['wp_roles']->role_names) ? $GLOBALS['wp_roles']->role_names : array());
            } else {
                $args['role'] = $role;
            }
        }

        $query = new \WP_User_Query($args);
        $this->items = $query->get_results();
        $this->_column_headers = array($this->get_columns(), array(), array(), 'username');

        $this->set_pagination_args(array(
            'total_items' => (int) $query->get_total(),
            'per_page'    => $per_page,
        ));
    }

    public function get_views() {
        $counts = count_users();
        $avail_roles = !empty($counts['avail_roles']) && is_array($counts['avail_roles']) ? $counts['avail_roles'] : array();
        $total_users = !empty($counts['total_users']) ? absint($counts['total_users']) : 0;
        $no_role_users = $total_users;
        foreach ($avail_roles as $count) {
            $no_role_users -= absint($count);
        }
        $no_role_users = max(0, $no_role_users);

        $base_url = admin_url('admin.php?page=koalaforms-users');
        $views = array();
        $views['all'] = sprintf(
            '<a href="%s"%s>%s <span class="count">(%s)</span></a>',
            esc_url($base_url),
            empty($this->current_role) ? ' class="current"' : '',
            esc_html__('All', 'koalaforms'),
            esc_html(number_format_i18n($total_users))
        );

        $roles = !empty($GLOBALS['wp_roles']->role_names) ? $GLOBALS['wp_roles']->role_names : array();
        foreach ($roles as $role_key => $role_label) {
            $role_count = !empty($avail_roles[$role_key]) ? absint($avail_roles[$role_key]) : 0;
            if (0 === $role_count) {
                continue;
            }

            $views[$role_key] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%s)</span></a>',
                esc_url(add_query_arg('role', $role_key, $base_url)),
                $this->current_role === $role_key ? ' class="current"' : '',
                esc_html($role_label),
                esc_html(number_format_i18n($role_count))
            );
        }

        if ($no_role_users > 0) {
            $views['none'] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%s)</span></a>',
                esc_url(add_query_arg('role', 'none', $base_url)),
                $this->current_role === 'none' ? ' class="current"' : '',
                esc_html__('No role', 'koalaforms'),
                esc_html(number_format_i18n($no_role_users))
            );
        }

        return $views;
    }

    public function extra_tablenav($which) {
        if ('top' !== $which) {
            return;
        }

        $roles = !empty($GLOBALS['wp_roles']->role_names) ? $GLOBALS['wp_roles']->role_names : array();
        ?>
        <div class="alignleft actions">
            <label class="screen-reader-text" for="kf-user-role-filter"><?php echo esc_html__('Filter by role', 'koalaforms'); ?></label>
            <select name="role" id="kf-user-role-filter">
                <option value="all"><?php echo esc_html__('All roles', 'koalaforms'); ?></option>
                <?php foreach ($roles as $role_key => $role_label) : ?>
                    <option value="<?php echo esc_attr($role_key); ?>" <?php selected($this->current_role, $role_key); ?>>
                        <?php echo esc_html($role_label); ?>
                    </option>
                <?php endforeach; ?>
                <option value="none" <?php selected($this->current_role, 'none'); ?>>
                    <?php echo esc_html__('No role', 'koalaforms'); ?>
                </option>
            </select>
            <?php submit_button(__('Filter', 'koalaforms'), '', 'filter_action', false); ?>
        </div>
        <?php
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'name':
                return !empty($item->display_name) ? esc_html($item->display_name) : esc_html__('Not set', 'koalaforms');
            case 'email':
                /* translators: 1: user email address (used as href), 2: user email address (display text) */
                return !empty($item->user_email) ? sprintf('<a href="mailto:%1$s">%2$s</a>', esc_attr($item->user_email), esc_html($item->user_email)) : esc_html__('Not set', 'koalaforms');
            case 'role':
                $roles = array();
                if (!empty($item->roles) && is_array($item->roles)) {
                    foreach ($item->roles as $role) {
                        $roles[] = !empty($GLOBALS['wp_roles']->role_names[$role]) ? $GLOBALS['wp_roles']->role_names[$role] : $role;
                    }
                }

                return !empty($roles) ? esc_html(implode(', ', $roles)) : esc_html__('No role', 'koalaforms');
            case 'registered':
                return !empty($item->user_registered) ? esc_html(mysql2date(get_option('date_format'), $item->user_registered)) : esc_html__('Unknown', 'koalaforms');
            default:
                return '';
        }
    }

    public function column_username($item) {
        $edit_url = admin_url('user-edit.php?user_id=' . absint($item->ID));
        $user_login = !empty($item->user_login) ? $item->user_login : __('Unknown', 'koalaforms');

        return sprintf(
            '<strong><a href="%1$s">%2$s</a></strong><div class="row-actions"><span class="edit"><a href="%1$s">%3$s</a></span></div>',
            esc_url($edit_url),
            esc_html($user_login),
            esc_html__('Edit', 'koalaforms')
        );
    }

    public function display() {
        $this->display_tablenav('top');
        ?>
        <table class="wp-list-table widefat fixed striped table-view-list users">
            <thead>
                <tr>
                    <?php $this->print_column_headers(); ?>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php $this->display_rows_or_placeholder(); ?>
            </tbody>
        </table>
        <?php
        $this->display_tablenav('bottom');
    }
}
