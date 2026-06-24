<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class LogsListTable extends \WP_List_Table {

    private string $integration;

    public function __construct( string $integration ) {
        parent::__construct( [
            'singular' => 'log',
            'plural'   => 'logs',
            'ajax'     => false,
        ] );
        $this->integration = $integration;
    }

    public function get_columns(): array {
        return [
            'cb'          => '<input type="checkbox">',
            'created_at'  => __( 'Date / Time', 'koalaforms' ),
            'type'        => __( 'Provider', 'koalaforms' ),
            'level'       => __( 'Level', 'koalaforms' ),
            'message'     => __( 'Message', 'koalaforms' ),
            'context'     => __( 'Context', 'koalaforms' ),
        ];
    }

    protected function get_sortable_columns(): array {
        return [
            'created_at' => [ 'created_at', true ],
            'type'       => [ 'type', false ],
            'level'      => [ 'level', false ],
        ];
    }

    protected function get_bulk_actions(): array {
        return [ 'delete' => __( 'Delete', 'koalaforms' ) ];
    }

    protected function column_default( $item, $column_name ): string {
        return esc_html( $item[ $column_name ] ?? '' );
    }

    protected function column_cb( $item ): string {
        return '<input type="checkbox" name="log_ids[]" value="' . absint( $item['id'] ) . '">';
    }

    protected function column_created_at( $item ): string {
        $ts = strtotime( $item['created_at'] );
        return esc_html( wp_date( 'd M Y, H:i:s', $ts ) );
    }

    protected function column_level( $item ): string {
        $level  = $item['level'] ?? 'info';
        $colors = [
            'info'    => [ 'bg' => '#e8f0fe', 'text' => '#1a56db' ],
            'warning' => [ 'bg' => '#fef3c7', 'text' => '#92400e' ],
            'error'   => [ 'bg' => '#fee2e2', 'text' => '#991b1b' ],
        ];
        $c = $colors[ $level ] ?? $colors['info'];
        return sprintf(
            '<span style="background:%s;color:%s;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:600;text-transform:uppercase;">%s</span>',
            esc_attr( $c['bg'] ),
            esc_attr( $c['text'] ),
            esc_html( $level )
        );
    }

    protected function column_message( $item ): string {
        $textarea    = '<textarea readonly rows="2" style="width:100%;min-width:220px;max-width:420px;font-size:11px;line-height:1.5;background:#f6f7f7;border:1px solid #ddd;border-radius:3px;padding:4px 6px;resize:vertical;color:#1d2327">'
            . esc_textarea( $item['message'] )
            . '</textarea>';
        $delete_url  = wp_nonce_url(
            add_query_arg( [ 'page' => 'koalaforms-logs', 'kf_log_action' => 'delete_row', 'log_id' => absint( $item['id'] ), 'integration' => $this->integration ] , admin_url( 'admin.php' ) ),
            'kf_delete_log_' . absint( $item['id'] )
        );
        $row_actions = $this->row_actions( [
            'delete' => '<a href="' . esc_url( $delete_url ) . '" style="color:#cc1818">' . __( 'Delete', 'koalaforms' ) . '</a>',
        ] );
        return $textarea . $row_actions;
    }

    protected function column_context( $item ): string {
        if ( empty( $item['context'] ) ) {
            return '<span style="color:#aaa">—</span>';
        }
        $decoded = json_decode( $item['context'], true );
        $pretty  = is_array( $decoded )
            ? wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
            : $item['context'];

        return '<details><summary style="cursor:pointer;color:#2271b1">View</summary>'
            . '<pre style="max-width:420px;overflow:auto;font-size:11px;line-height:1.5;white-space:pre-wrap;background:#f6f7f7;padding:8px;border-radius:3px;margin-top:4px">'
            . esc_html( $pretty )
            . '</pre></details>';
    }

    public function prepare_items(): void {
        $per_page    = 50;
        $current     = $this->get_pagenum();
        $offset      = ( $current - 1 ) * $per_page;

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'created_at';
        $order   = isset( $_GET['order'] ) && strtoupper( sanitize_key( $_GET['order'] ) ) === 'ASC' ? 'ASC' : 'DESC';
        // phpcs:enable

        $this->items              = Logger::get_logs( $this->integration, $per_page, $offset, $orderby, $order );
        $total                    = Logger::get_count( $this->integration );
        $this->_column_headers    = [ $this->get_columns(), [], $this->get_sortable_columns() ];

        $this->set_pagination_args( [
            'total_items' => $total,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil( $total / $per_page ),
        ] );
    }

    public function no_items(): void {
        echo esc_html__( 'No log entries found.', 'koalaforms' );
    }
}
