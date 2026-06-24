<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class Logger {

    const TABLE_BASE = 'koalaforms_logs';

    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_BASE;
    }

    public static function create_table(): void {
        global $wpdb;
        $table = self::table();

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            integration VARCHAR(64)     NOT NULL,
            type        VARCHAR(64)              DEFAULT NULL,
            level       VARCHAR(16)     NOT NULL DEFAULT 'info',
            message     TEXT            NOT NULL,
            context     LONGTEXT                 DEFAULT NULL,
            created_at  DATETIME        NOT NULL,
            PRIMARY KEY  (id),
            KEY integration (integration),
            KEY type        (type),
            KEY created_at  (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public static function log( string $integration, string $type, string $message, array $context = [], string $level = 'info' ): void {
        if ( (string) AppUtility::get_global_option( 'logging_enabled', '0' ) !== '1' ) {
            return;
        }

        $min_level = AppUtility::get_global_option( 'log_level', 'all' );
        if ( $min_level === 'error' && $level !== 'error' ) {
            return;
        }

        global $wpdb;

        if ( mb_strlen( $message ) > 200 ) {
            $message = mb_substr( $message, 0, 197 ) . '…';
        }

        $wpdb->insert(
            self::table(),
            [
                'integration' => $integration,
                'type'        => $type ?: null,
                'level'       => $level,
                'message'     => $message,
                'context'     => ! empty( $context ) ? wp_json_encode( $context ) : null,
                'created_at'  => current_time( 'mysql', true ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s' ]
        );
    }

    public static function get_logs(
        string $integration = '',
        int    $limit       = 50,
        int    $offset      = 0,
        string $orderby     = 'created_at',
        string $order       = 'DESC'
    ): array {
        global $wpdb;

        $table   = self::table();
        $order   = esc_sql( strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC' );
        $allowed = [ 'id', 'integration', 'type', 'level', 'created_at' ];
        $orderby = in_array( $orderby, $allowed, true ) ? $orderby : 'created_at';

        if ( $integration !== '' ) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM %i WHERE integration = %s ORDER BY %i {$order} LIMIT %d OFFSET %d",
                    $table, $integration, $orderby, $limit, $offset
                ),
                ARRAY_A
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM %i ORDER BY %i {$order} LIMIT %d OFFSET %d",
                    $table, $orderby, $limit, $offset
                ),
                ARRAY_A
            );
        }

        return $results ?: [];
    }

    public static function get_count( string $integration = '' ): int {
        global $wpdb;
        $table = self::table();

        if ( $integration !== '' ) {
            return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE integration = %s', $table, $integration ) );
        }

        return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table ) );
    }

    public static function delete_by_ids( array $ids ): void {
        if ( empty( $ids ) ) return;

        global $wpdb;
        $ids          = array_map( 'absint', $ids );
        $ids          = array_filter( $ids );
        if ( empty( $ids ) ) return;

        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $table        = self::table();

        $wpdb->query( $wpdb->prepare( "DELETE FROM %i WHERE id IN ({$placeholders})", $table, ...$ids ) );
    }

    public static function cleanup(): void {
        $days = (int) AppUtility::get_global_option( 'log_retention_days', '30' );
        if ( $days <= 0 ) {
            return;
        }

        global $wpdb;
        $table = self::table();
        $wpdb->query( $wpdb->prepare(
            'DELETE FROM %i WHERE created_at < DATE_SUB( UTC_TIMESTAMP(), INTERVAL %d DAY )',
            $table, $days
        ) );
    }

    public static function clear( string $integration = '' ): void {
        global $wpdb;
        $table = self::table();

        if ( $integration !== '' ) {
            $wpdb->delete( $table, [ 'integration' => $integration ], [ '%s' ] );
            return;
        }

        $wpdb->query( $wpdb->prepare( 'TRUNCATE TABLE %i', $table ) );
    }
}
