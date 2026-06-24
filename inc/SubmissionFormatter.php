<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class SubmissionFormatter {

    public static function format( $raw_value, array $field_config ): string {
        if ( ! isset( $field_config['attrs'] ) ) {
            return (string) $raw_value;
        }

        $attrs     = $field_config['attrs'];
        $block     = strtolower( $field_config['blockName'] ?? '' );
        $type      = strtolower( $attrs['type'] ?? '' );
        $block_slug = preg_replace('/^[^\/]+\//', '', $block); // strip namespace prefix

        switch ( $block_slug ) {
            case 'phone':
                return self::format_phone( (string) $raw_value, $attrs['mask'] ?? '' );

            case 'date':
                return self::format_date( (string) $raw_value, $attrs['dateFormat'] ?? '' );

            case 'select':
            case 'radio':
                return self::format_option( $raw_value, $attrs['options'] ?? array() );

            case 'multiselect':
                return self::format_multi_option( $raw_value, $attrs['options'] ?? array() );

            case 'checkbox':
                return self::format_checkbox( $raw_value, $attrs['checkLabel'] ?? '' );

            case 'disclosure':
                return ! empty( $raw_value ) ? __( 'Agreed', 'koalaforms' ) : __( 'Not agreed', 'koalaforms' );

            default:
                return is_array( $raw_value )
                    ? implode( ', ', array_map( 'strval', $raw_value ) )
                    : (string) $raw_value;
        }
    }

    private static function format_phone( string $raw, string $mask ): string {
        if ( empty( $mask ) ) {
            return $raw;
        }

        $digits = preg_replace( '/\D/', '', $raw );
        $output = '';
        $digit_index = 0;

        foreach ( str_split( $mask ) as $char ) {
            if ( $char === '#' ) {
                if ( $digit_index >= strlen( $digits ) ) break;
                $output .= $digits[ $digit_index++ ];
            } else {
                $output .= $char;
            }
        }

        return $output ?: $raw;
    }

    private static function format_date( string $raw, string $date_format ): string {
        if ( empty( $raw ) ) return $raw;

        $timestamp = strtotime( $raw );
        if ( ! $timestamp ) return $raw;

        // Map UI date format tokens to PHP date() tokens.
        // Order matters — replace longer tokens first to avoid partial matches.
        $format_map = array(
            'YYYY' => 'Y',
            'YY'   => 'y',
            'MM'   => 'm',
            'DD'   => 'd',
            'dd'   => 'd',
        );

        $php_format = str_replace(
            array_keys( $format_map ),
            array_values( $format_map ),
            $date_format ?: 'MM-dd-YYYY'
        );

        return date( $php_format, $timestamp ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    }

    private static function format_option( $raw, array $options ): string {
        foreach ( $options as $option ) {
            if ( isset( $option['value'] ) && (string) $option['value'] === (string) $raw ) {
                return $option['label'] ?? (string) $raw;
            }
        }
        return (string) $raw;
    }

    private static function format_multi_option( $raw, array $options ): string {
        if ( is_array( $raw ) ) {
            $values = $raw;
        } elseif ( is_string( $raw ) && strpos( $raw, '[' ) === 0 ) {
            $decoded = json_decode( $raw, true );
            $values  = is_array( $decoded ) ? $decoded : array( $raw );
        } else {
            $values = $raw ? explode( ',', $raw ) : array();
        }
        $labels = array();

        foreach ( $values as $val ) {
            $labels[] = self::format_option( trim( $val ), $options );
        }

        return implode( ', ', $labels );
    }

    private static function format_checkbox( $raw, string $check_label ): string {
        return ! empty( $raw )
            ? ( $check_label ?: __( 'Agreed', 'koalaforms' ) )
            : __( 'Not agreed', 'koalaforms' );
    }
}
