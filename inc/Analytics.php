<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class Analytics {

    private static $instance = null;

    private const OPTION_PREFIX  = 'koalaforms_analytics_config_';
    private const ALLOWED_DAYS   = [ 7, 30, 60, 90 ];
    private const ALLOWED_TYPES  = [ 'submissionsOverTime', 'stageDistribution', 'browserBreakdown', 'fieldDistribution' ];
    private const KNOWN_BROWSERS = [ 'Chrome', 'Firefox', 'Safari', 'Edge', 'Internet Explorer', 'Opera' ];

    private function __construct() {
        add_action( 'wp_ajax_koalaforms_get_analytics_data',    [ $this, 'get_analytics_data' ] );
        add_action( 'wp_ajax_koalaforms_save_analytics_config', [ $this, 'save_analytics_config' ] );
    }

    public static function create_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ── AJAX: batch-fetch chart data ──────────────────────────────────────────

    public function get_analytics_data() {
        check_ajax_referer( 'koalaforms_analytics_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
        }

        $form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
        if ( ! $form_id ) {
            wp_send_json_error( [ 'message' => 'Invalid form ID' ] );
        }

        $charts_raw = isset( $_POST['charts'] ) ? wp_unslash( $_POST['charts'] ) : '[]'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $charts     = json_decode( $charts_raw, true );
        if ( ! is_array( $charts ) ) {
            wp_send_json_error( [ 'message' => 'Invalid charts payload' ] );
        }

        $form_model = Form::create_instance();
        $form       = $form_model->get_form( $form_id );
        if ( ! $form ) {
            wp_send_json_error( [ 'message' => 'Form not found' ] );
        }

        $results = [];
        foreach ( $charts as $chart ) {
            $id   = isset( $chart['id'] )   ? sanitize_text_field( $chart['id'] )   : '';
            $type = isset( $chart['type'] ) ? sanitize_text_field( $chart['type'] ) : '';
            $days = isset( $chart['days'] ) ? absint( $chart['days'] )              : 30;

            if ( ! $id || ! in_array( $type, self::ALLOWED_TYPES, true ) ) {
                continue;
            }
            if ( ! in_array( $days, self::ALLOWED_DAYS, true ) ) {
                $days = 30;
            }

            switch ( $type ) {
                case 'submissionsOverTime':
                    $results[ $id ] = $this->query_timeline( $form_id, $days );
                    break;

                case 'stageDistribution':
                    $results[ $id ] = $this->query_stage_distribution( $form_id, $days );
                    break;

                case 'browserBreakdown':
                    $results[ $id ] = $this->query_browser_breakdown( $form_id, $days );
                    break;

                case 'fieldDistribution':
                    $field_key = isset( $chart['field_key'] ) ? sanitize_text_field( $chart['field_key'] ) : '';
                    if ( $field_key && isset( $form->fields[ $field_key ] ) ) {
                        $results[ $id ] = $this->query_field_distribution( $form_id, $field_key, $days );
                    } else {
                        $results[ $id ] = [];
                    }
                    break;
            }
        }

        wp_send_json_success( $results );
    }

    // ── AJAX: save chart layout to wp_options ─────────────────────────────────

    public function save_analytics_config() {
        check_ajax_referer( 'koalaforms_analytics_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized' ], 403 );
        }

        $form_id    = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
        $charts_raw = isset( $_POST['charts'] )  ? wp_unslash( $_POST['charts'] ) : '[]'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $charts     = json_decode( $charts_raw, true );

        if ( ! $form_id || ! is_array( $charts ) ) {
            wp_send_json_error( [ 'message' => 'Invalid payload' ] );
        }

        $clean = [];
        foreach ( $charts as $chart ) {
            $type = isset( $chart['type'] ) ? sanitize_text_field( $chart['type'] ) : '';
            if ( ! in_array( $type, self::ALLOWED_TYPES, true ) ) {
                continue;
            }
            $days = isset( $chart['days'] ) ? absint( $chart['days'] ) : 30;
            if ( ! in_array( $days, self::ALLOWED_DAYS, true ) ) {
                $days = 30;
            }
            $entry = [
                'id'   => isset( $chart['id'] ) ? sanitize_text_field( $chart['id'] ) : '',
                'type' => $type,
                'days' => $days,
            ];
            if ( $type === 'fieldDistribution' ) {
                $entry['field_key']   = isset( $chart['field_key'] )   ? sanitize_text_field( $chart['field_key'] )   : '';
                $entry['field_label'] = isset( $chart['field_label'] ) ? sanitize_text_field( $chart['field_label'] ) : '';
            }
            $clean[] = $entry;
        }

        update_option( self::OPTION_PREFIX . $form_id, wp_json_encode( $clean ), false );
        wp_send_json_success();
    }

    // ── Public helpers ────────────────────────────────────────────────────────

    public static function get_saved_config( $form_id ) {
        $raw = get_option( self::OPTION_PREFIX . $form_id, null );
        if ( $raw === null ) {
            return self::default_config();
        }
        $decoded = json_decode( $raw, true );
        return is_array( $decoded ) ? $decoded : self::default_config();
    }

    private static function default_config() {
        return [
            [ 'id' => 'c-default-1', 'type' => 'submissionsOverTime', 'days' => 30 ],
            [ 'id' => 'c-default-2', 'type' => 'stageDistribution',   'days' => 30 ],
            [ 'id' => 'c-default-3', 'type' => 'browserBreakdown',    'days' => 30 ],
        ];
    }

    // ── SQL queries ───────────────────────────────────────────────────────────

    private function query_timeline( $form_id, $days ) {
        global $wpdb;

        $rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT DATE(p.post_date) AS sub_date, COUNT(*) AS total
                 FROM {$wpdb->posts} p
                 WHERE p.post_type   = %s
                   AND p.post_status = 'publish'
                   AND p.post_date  >= DATE_SUB(NOW(), INTERVAL %d DAY)
                   AND EXISTS (
                       SELECT 1 FROM {$wpdb->postmeta} fm
                       WHERE fm.post_id   = p.ID
                         AND fm.meta_key   = %s
                         AND fm.meta_value = %s
                   )
                 GROUP BY DATE(p.post_date)
                 ORDER BY sub_date ASC",
                AppUtility::SUBMISSION_POST_TYPE,
                $days,
                AppUtility::meta_key( 'form_id' ),
                (string) $form_id
            ),
            ARRAY_A
        );

        return array_map( fn( $r ) => [ 'date' => $r['sub_date'], 'count' => (int) $r['total'] ], $rows ?: [] );
    }

    private function query_stage_distribution( $form_id, $days ) {
        global $wpdb;

        $rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT pm.meta_value AS stage, COUNT(*) AS total
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm
                         ON pm.post_id  = p.ID
                        AND pm.meta_key = 'koalaforms_submission_stage'
                 WHERE p.post_type   = %s
                   AND p.post_status = 'publish'
                   AND p.post_date  >= DATE_SUB(NOW(), INTERVAL %d DAY)
                   AND EXISTS (
                       SELECT 1 FROM {$wpdb->postmeta} fm
                       WHERE fm.post_id   = p.ID
                         AND fm.meta_key   = %s
                         AND fm.meta_value = %s
                   )
                 GROUP BY pm.meta_value
                 ORDER BY total DESC",
                AppUtility::SUBMISSION_POST_TYPE,
                $days,
                AppUtility::meta_key( 'form_id' ),
                (string) $form_id
            ),
            ARRAY_A
        );

        return array_map( fn( $r ) => [ 'stage' => $r['stage'], 'count' => (int) $r['total'] ], $rows ?: [] );
    }

    private function query_browser_breakdown( $form_id, $days ) {
        global $wpdb;

        $rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT pm.meta_value AS browser, COUNT(*) AS total
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm
                         ON pm.post_id  = p.ID
                        AND pm.meta_key = 'koalaforms_browser'
                 WHERE p.post_type   = %s
                   AND p.post_status = 'publish'
                   AND p.post_date  >= DATE_SUB(NOW(), INTERVAL %d DAY)
                   AND EXISTS (
                       SELECT 1 FROM {$wpdb->postmeta} fm
                       WHERE fm.post_id   = p.ID
                         AND fm.meta_key   = %s
                         AND fm.meta_value = %s
                   )
                 GROUP BY pm.meta_value
                 ORDER BY total DESC",
                AppUtility::SUBMISSION_POST_TYPE,
                $days,
                AppUtility::meta_key( 'form_id' ),
                (string) $form_id
            ),
            ARRAY_A
        );

        $known  = self::KNOWN_BROWSERS;
        $merged = [];
        $other  = 0;
        foreach ( $rows ?: [] as $r ) {
            if ( in_array( $r['browser'], $known, true ) ) {
                $merged[] = [ 'browser' => $r['browser'], 'count' => (int) $r['total'] ];
            } else {
                $other += (int) $r['total'];
            }
        }
        if ( $other > 0 ) {
            $merged[] = [ 'browser' => 'Other', 'count' => $other ];
        }
        return $merged;
    }

    private function query_field_distribution( $form_id, $field_key, $days ) {
        global $wpdb;

        $rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "SELECT pm.meta_value AS field_value, COUNT(*) AS total
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm
                         ON pm.post_id  = p.ID
                        AND pm.meta_key = %s
                 WHERE p.post_type   = %s
                   AND p.post_status = 'publish'
                   AND p.post_date  >= DATE_SUB(NOW(), INTERVAL %d DAY)
                   AND EXISTS (
                       SELECT 1 FROM {$wpdb->postmeta} fm
                       WHERE fm.post_id   = p.ID
                         AND fm.meta_key   = %s
                         AND fm.meta_value = %s
                   )
                 GROUP BY pm.meta_value
                 ORDER BY total DESC
                 LIMIT 50",
                $field_key,
                AppUtility::SUBMISSION_POST_TYPE,
                $days,
                AppUtility::meta_key( 'form_id' ),
                (string) $form_id
            ),
            ARRAY_A
        );

        return array_map( fn( $r ) => [ 'value' => $r['field_value'], 'count' => (int) $r['total'] ], $rows ?: [] );
    }
}
