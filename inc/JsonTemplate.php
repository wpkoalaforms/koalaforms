<?php
/**
 * Builds a form post from a plain JSON/array schema.
 *
 * Generates raw Gutenberg block markup (same as static template classes),
 * saves it via wp_insert_post, and lets the save_post → Form::save_form
 * hook derive _field_config automatically.
 *
 * Supported field types: text, email, textarea, number, select, radio,
 *                        checkbox, date, url, multiselect,
 *                        disclosure, address
 *
 * JSON shape:
 * {
 *   "title":    "My Form",
 *   "settings": { "is_active": true },   // optional
 *   "steps": [
 *     {
 *       "label": "Step Label",
 *       "name":  "optional_step_name",   // auto-generated if omitted
 *       "next_label":  "Continue",       // optional
 *       "prev_label":  "Back",           // optional
 *       "columns": [                     // optional: pairs of fields side-by-side
 *         [ <field>, <field> ]
 *       ],
 *       "fields": [ <field>, ... ]       // flat field list (appended after columns)
 *     }
 *   ]
 * }
 *
 * Field shape:
 * {
 *   "type":        "text",
 *   "label":       "First Name",
 *   "name":        "first_name",         // optional, auto-generated if omitted
 *   "placeholder": "John",               // optional
 *   "required":    true,                 // optional, default false
 *   "options":     ["A","B"],            // select / radio / multiselect
 *   "rows":        5,                    // textarea
 *   "min":         0,                    // number
 *   "max":         100,                  // number
 *   "content":     "HTML...",            // disclosure
 *   "check_label": "I agree...",         // disclosure
 *   "required_error": "Custom message"   // overrides default required error
 * }
 *
 * @see inc/Form.php          Form::save_form() — derives _field_config from post_content
 * @see inc/examples/         Sample JSON files
 */

namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class JsonTemplate {

    private array  $schema;
    private string $prefix = 'kf';

    public function __construct( array $schema ) {
        $this->schema = $schema;
    }

    /**
     * Creates a form post from a JSON file path, JSON string, or decoded array.
     *
     * @param  string|array $input  File path, raw JSON string, or array.
     * @return int|\WP_Error  Post ID on success.
     */
    public static function create( $input ) {
        if ( is_string( $input ) && file_exists( $input ) ) {
            $input = file_get_contents( $input );
            if ( $input === false ) {
                return new \WP_Error( 'file_read_error', 'Could not read JSON file.' );
            }
        }

        $schema = is_string( $input ) ? json_decode( $input, true ) : $input;

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new \WP_Error( 'invalid_json', 'Invalid JSON: ' . json_last_error_msg() );
        }

        if ( empty( $schema['title'] ) ) {
            return new \WP_Error( 'missing_title', 'Form title is required.' );
        }

        if ( empty( $schema['steps'] ) ) {
            return new \WP_Error( 'missing_steps', 'At least one step is required.' );
        }

        $instance = new self( $schema );
        return $instance->save();
    }

    /**
     * Creates multiple forms from an array of file paths, JSON strings, or arrays.
     *
     * @param  array $inputs  Array of file paths, JSON strings, or decoded arrays.
     * @return array          [ 'created' => [post_id, ...], 'errors' => [input => message, ...] ]
     */
    public static function create_many( array $inputs ): array {
        $results = [ 'created' => [], 'errors' => [] ];

        foreach ( $inputs as $input ) {
            $post_id = self::create( $input );
            $key     = is_string( $input ) ? basename( $input ) : ( $input['title'] ?? 'unknown' );

            if ( is_wp_error( $post_id ) ) {
                $results['errors'][ $key ] = $post_id->get_error_message();
            } else {
                $results['created'][ $key ] = $post_id;
            }
        }

        return $results;
    }

    // -------------------------------------------------------------------------
    // Save
    // -------------------------------------------------------------------------

    private function save() {
        $content = $this->build_content();

        $post_id = wp_insert_post( [
            'post_title'   => sanitize_text_field( $this->schema['title'] ),
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => AppUtility::FORM_POST_TYPE,
        ], true );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Write _field_config directly — save_post hook relies on current_user_can()
        // which returns false in CLI/cron context, so we can't depend on it here.
        AppUtility::update_meta( $post_id, '_field_config', parse_blocks( $content ) );

        $settings = $this->schema['settings'] ?? [];

        // Ensure stage is always present so the submission stage UI renders.
        if ( empty( $settings['stage'] ) ) {
            $settings['stage'] = RegisterTypes::DEFAULT_STAGES;
        }
        if ( ! isset( $settings['default_stage'] ) ) {
            $settings['default_stage'] = RegisterTypes::DEFAULT_STAGES[0];
        }

        AppUtility::update_meta( $post_id, 'form_settings', $settings );

        return $post_id;
    }

    // -------------------------------------------------------------------------
    // Content builder
    // -------------------------------------------------------------------------

    private function build_content(): string {
        $content = '';
        foreach ( $this->schema['steps'] as $step ) {
            $content .= $this->build_step( $step );
        }
        return $content;
    }

    private function build_step( array $step ): string {
        $inner = '';

        // Side-by-side column pairs
        foreach ( $step['columns'] ?? [] as $pair ) {
            if ( count( $pair ) === 2 ) {
                $inner .= $this->columns(
                    $this->build_field( $pair[0] ),
                    $this->build_field( $pair[1] )
                );
            }
        }

        // Flat fields
        foreach ( $step['fields'] ?? [] as $field ) {
            $inner .= $this->build_field( $field );
        }

        $label      = $step['label']      ?? 'Step';
        $name       = $step['name']       ?? wp_generate_uuid4();
        $next_label = $step['next_label'] ?? 'Next';
        $prev_label = $step['prev_label'] ?? 'Previous';

        return $this->step( $label, $name, $next_label, $prev_label, $inner );
    }

    private function build_field( array $field ): string {
        $type = strtolower( $field['type'] ?? 'text' );

        switch ( $type ) {
            case 'text':
                return $this->text( $field );
            case 'email':
                return $this->email( $field );
            case 'textarea':
                return $this->textarea( $field );
            case 'number':
                return $this->number( $field );
            case 'select':
                return $this->select( $field );
            case 'radio':
                return $this->radio( $field );
            case 'multiselect':
                return $this->multiselect( $field );
            case 'checkbox':
                return $this->checkbox( $field );
            case 'date':
                return $this->date( $field );
            case 'url':
                return $this->url( $field );
            case 'disclosure':
                return $this->disclosure( $field );
            case 'address':
                return $this->address( $field );
            default:
                return $this->text( $field );
        }
    }

    // -------------------------------------------------------------------------
    // Block markup helpers
    // -------------------------------------------------------------------------

    private function step( string $label, string $name, string $next, string $prev, string $inner ): string {
        $attrs = $this->encode( [
            'inputLabel'    => $label,
            'name'          => $name,
            'nextBtnLabel'  => $next,
            'prevBtnLabel'  => $prev,
            'nextWidth'     => '3',
            'previousWidth' => '3',
        ] );
        return "<!-- wp:{$this->prefix}/step {$attrs} --><div>{$inner}</div><!-- /wp:{$this->prefix}/step -->";
    }

    private function columns( string $left, string $right ): string {
        return '<!-- wp:columns --><div class="wp-block-columns">'
            . '<!-- wp:column --><div class="wp-block-column">' . $left . '</div><!-- /wp:column -->'
            . '<!-- wp:column --><div class="wp-block-column">' . $right . '</div><!-- /wp:column -->'
            . '</div><!-- /wp:columns -->';
    }

    private function text( array $f ): string {
        $attrs = $this->base_attrs( $f ) + [ 'patternError' => 'Value does not match the given pattern.' ];
        if ( ! empty( $f['placeholder'] ) ) $attrs['placeholder'] = $f['placeholder'];
        return $this->self_closing_block( 'text', $attrs );
    }

    private function email( array $f ): string {
        $attrs = $this->base_attrs( $f ) + [
            'placeholder'  => $f['placeholder'] ?? 'eg. john@example.com',
            'patternError' => 'Value does not match the given pattern.',
        ];
        return $this->self_closing_block( 'email', $attrs );
    }

    private function textarea( array $f ): string {
        $attrs = $this->base_attrs( $f ) + [ 'rows' => (int) ( $f['rows'] ?? 5 ) ];
        if ( ! empty( $f['placeholder'] ) ) $attrs['placeholder'] = $f['placeholder'];
        return $this->self_closing_block( 'textarea', $attrs );
    }

    private function number( array $f ): string {
        $attrs = $this->base_attrs( $f );
        if ( ! empty( $f['placeholder'] ) ) $attrs['placeholder'] = $f['placeholder'];
        if ( isset( $f['min'] ) ) $attrs['min'] = $f['min'];
        if ( isset( $f['max'] ) ) $attrs['max'] = $f['max'];
        return $this->self_closing_block( 'number', $attrs );
    }

    private function select( array $f ): string {
        $attrs = $this->base_attrs( $f ) + [ 'options' => $this->build_options( $f['options'] ?? [] ) ];
        return $this->self_closing_block( 'select', $attrs );
    }

    private function radio( array $f ): string {
        $attrs = $this->base_attrs( $f ) + [ 'options' => $this->build_options( $f['options'] ?? [] ) ];
        return $this->self_closing_block( 'radio', $attrs );
    }

    private function multiselect( array $f ): string {
        $attrs = $this->base_attrs( $f ) + [ 'options' => $this->build_options( $f['options'] ?? [] ) ];
        return $this->self_closing_block( 'multiselect', $attrs );
    }

    private function checkbox( array $f ): string {
        return $this->self_closing_block( 'checkbox', $this->base_attrs( $f ) );
    }

    private function date( array $f ): string {
        $attrs = $this->base_attrs( $f ) + [ 'dateFormat' => $f['date_format'] ?? 'MM/DD/YYYY' ];
        return $this->self_closing_block( 'date', $attrs );
    }

    private function url( array $f ): string {
        $attrs = $this->base_attrs( $f ) + [ 'patternError' => 'Please enter a valid URL.' ];
        if ( ! empty( $f['placeholder'] ) ) $attrs['placeholder'] = $f['placeholder'];
        return $this->self_closing_block( 'url', $attrs );
    }

    private function disclosure( array $f ): string {
        $content    = $f['content']     ?? '';
        $check      = $f['check_label'] ?? 'I have read and understood the disclosure statement.';
        $attrs      = $this->base_attrs( $f ) + [
            'content'    => $content,
            'checkLabel' => $check,
        ];
        $html = '<p>' . $content . '</p>';
        return "<!-- wp:{$this->prefix}/disclosure " . $this->encode( $attrs ) . " -->{$html}<!-- /wp:{$this->prefix}/disclosure -->";
    }

    private function address( array $f ): string {
        $attrs = $this->base_attrs( $f );
        if ( ! empty( $f['hidden_fields'] ) ) $attrs['hiddenAddressFields'] = $f['hidden_fields'];
        // Address uses InnerBlocks — WordPress will populate them from the template
        // when the block is rendered; we emit the outer wrapper only.
        return "<!-- wp:{$this->prefix}/address " . $this->encode( $attrs ) . " --><div></div><!-- /wp:{$this->prefix}/address -->";
    }

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    /** Common attributes every field block carries. */
    private function base_attrs( array $f ): array {
        $attrs = [
            'inputLabel' => $f['label'] ?? '',
            'name'       => $f['name']  ?? wp_generate_uuid4(),
        ];

        if ( ! empty( $f['required'] ) ) {
            $attrs['required']      = true;
            $attrs['requiredError'] = $f['required_error'] ?? 'This is a required field.';
        }

        return $attrs;
    }

    /** Converts a flat string array to the block options format. */
    private function build_options( array $options ): array {
        return array_map( fn( $o ) => [
            'label'       => $o,
            'value'       => $o,
            'description' => '',
            'default'     => false,
        ], $options );
    }

    /** Emits a self-closing block comment tag. */
    private function self_closing_block( string $type, array $attrs ): string {
        return "<!-- wp:{$this->prefix}/{$type} " . $this->encode( $attrs ) . " /-->";
    }

    /** JSON-encodes attrs for embedding in a block comment. */
    private function encode( array $attrs ): string {
        return wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    }
}
