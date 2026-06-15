<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @description: Class to support form related operations throughout the plugin.
 */
class Form extends Post{

    public $post_type = null;
    private static $instance = null;
    private $form_submission_count_cache = array();
    

    private function __construct() {
        $this->post_type = AppUtility::FORM_POST_TYPE;
        add_filter('manage_'.$this->post_type.'_posts_columns', array($this, 'form_columns'));
        add_action('manage_'.$this->post_type.'_posts_custom_column' , array($this, 'form_columns_data'), 10, 2 );
        add_filter('post_row_actions', array($this, 'form_row_actions'), 10, 2);
        add_action('admin_post_koalaforms_duplicate_form', array($this, 'duplicate_form'));
        add_action('save_post', array($this, 'save_form'));
        add_filter('default_content', array($this, 'create_sample_step'), 10, 2);
    }

    // Adding custom columns for forms page.
    public function form_columns($columns){
        unset($columns['date']); // Removing the date column to place it in the end.
        $columns['title']     = __( 'Form Name', 'koalaforms');
        $columns['status']     = __( 'Status', 'koalaforms');
        $columns['entries']    = __( 'Entries', 'koalaforms');
        $columns['shortcode']  = __( 'Shortcode', 'koalaforms');
        $columns['date']      = __( 'Last Updated', 'koalaforms');
        return $columns;
    }

    // Populate custom columns data
    public function form_columns_data($column, $post_id){
        switch ($column) {
            case 'title':
                echo esc_html(get_the_title($post_id));
                break;
            case 'status':
                $status = $this->get_form_status_data($post_id);
                printf(
                    '<span class="kf-form-status kf-form-status-%1$s">%2$s</span>',
                    esc_attr($status['class']),
                    esc_html($status['label'])
                );
                break;
            case 'entries':
                echo esc_html(number_format_i18n($this->get_submission_count($post_id)));
                break;
            case 'shortcode':
                printf(
                    '<code class="kf-form-shortcode">[KoalaForms form_id="%d"]</code>',
                    absint($post_id)
                );
                break;
            case 'date':
                break;
        }
    }

    public function form_row_actions($actions, $post){
        if (empty($post) || $post->post_type !== $this->post_type) {
            return $actions;
        }

        $ordered_actions = array();

        if (isset($actions['edit'])) {
            $ordered_actions['edit'] = $actions['edit'];
        }

        $preview_url = get_preview_post_link($post);
        if (!empty($preview_url)) {
            $ordered_actions['preview'] = sprintf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url($preview_url),
                esc_html__('Preview', 'koalaforms')
            );
        }

        $entries_url = wp_nonce_url(
            admin_url('edit.php?post_type=' . AppUtility::SUBMISSION_POST_TYPE . '&form_filter=' . absint($post->ID)),
            'koalaforms_filter_submission_action',
            'submission_filter_nonce'
        );
        $ordered_actions['entries'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($entries_url),
            esc_html__('Entries', 'koalaforms')
        );

        $duplicate_url = wp_nonce_url(
            admin_url('admin-post.php?action=koalaforms_duplicate_form&form_id=' . absint($post->ID)),
            'koalaforms_duplicate_form_' . absint($post->ID)
        );
        $ordered_actions['duplicate'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($duplicate_url),
            esc_html__('Duplicate', 'koalaforms')
        );

        if (isset($actions['trash'])) {
            $ordered_actions['trash'] = $actions['trash'];
        }

        foreach ($actions as $key => $action) {
            if (!isset($ordered_actions[$key])) {
                $ordered_actions[$key] = $action;
            }
        }

        return $ordered_actions;
    }

    public function duplicate_form(){
        $source_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;

        if (empty($source_id) || !current_user_can('edit_post', $source_id)) {
            wp_safe_redirect(admin_url('edit.php?post_type=' . $this->post_type));
            exit;
        }

        check_admin_referer('koalaforms_duplicate_form_' . $source_id);

        $source_post = get_post($source_id);
        if (empty($source_post) || $source_post->post_type !== $this->post_type) {
            wp_safe_redirect(admin_url('edit.php?post_type=' . $this->post_type));
            exit;
        }

        $new_post = array(
            /* translators: %s: original form title */
            'post_title'   => sprintf(__('%s (Copy)', 'koalaforms'), $source_post->post_title),
            'post_content' => $source_post->post_content,
            'post_status'  => 'draft',
            'post_type'    => $this->post_type,
            'post_author'  => get_current_user_id(),
        );

        $new_post_id = wp_insert_post($new_post, true);
        if (is_wp_error($new_post_id) || empty($new_post_id)) {
            wp_safe_redirect(admin_url('edit.php?post_type=' . $this->post_type));
            exit;
        }

        $meta = get_post_meta($source_id);
        $skip_meta = array('_edit_lock', '_edit_last');
        foreach ($meta as $meta_key => $meta_values) {
            if (in_array($meta_key, $skip_meta, true)) {
                continue;
            }

            delete_post_meta($new_post_id, $meta_key);
            foreach ($meta_values as $meta_value) {
                add_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value));
            }
        }

        wp_safe_redirect(admin_url('post.php?post=' . absint($new_post_id) . '&action=edit'));
        exit;
    }

    private function get_submission_count($form_id){
        $form_id = absint($form_id);
        if (isset($this->form_submission_count_cache[$form_id])) {
            return $this->form_submission_count_cache[$form_id];
        }

        if (empty($form_id)) {
            return 0;
        }

        $query = new \WP_Query(array(
            'post_type'      => AppUtility::SUBMISSION_POST_TYPE,
            'post_status'    => 'any',
            'fields'         => 'ids',
            'posts_per_page' => 1,
            'no_found_rows'  => false,
            'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key'     => AppUtility::meta_key('form_id'),
                    'value'   => $form_id,
                    'compare' => '=',
                ),
            ),
        ));

        $count = absint($query->found_posts);
        $this->form_submission_count_cache[$form_id] = $count;
        return $count;
    }

    private function get_form_status_data($post_id){
        $post_status = get_post_status($post_id);
        $settings = AppUtility::get_meta($post_id, 'form_settings', true);
        $is_active = !empty($settings['is_active']);

        if (in_array($post_status, array('draft', 'pending'), true)) {
            return array(
                'label' => ucfirst($post_status),
                'class' => sanitize_html_class($post_status),
            );
        }

        if (!$is_active) {
            return array(
                'label' => __('Inactive', 'koalaforms'),
                'class' => 'inactive',
            );
        }

        return array(
            'label' => __('Active', 'koalaforms'),
            'class' => 'active',
        );
    }

    // Creates and returns instance
    public static function create_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_forms() {
        $forms = $this->get();
        return $forms;
    }

    // Returns form with field configurations
    public function get_form($id) {
        $form = $this->get($id);

        // Return null if form does not exist.
        if(empty($form)){
            return null;
        }

        // Getting field configuration
        $form->_field_config        = AppUtility::get_meta($form->ID, '_field_config', true);
        $form->settings             = AppUtility::get_meta($form->ID, 'form_settings', true);

        foreach (AppUtility::FORM_PROPS as $key => $prop) {
            if (!isset($form->settings[$key])) {
                switch ($prop['type']) {
                    case 'string':
                        $form->settings[$key] = '';
                        break;
                    case 'boolean':
                        $form->settings[$key] = false;
                        break;
                    case 'date':
                        $form->settings[$key] = null; // or '' depending on your needs
                        break;
                    default:
                        $form->settings[$key] = null;
                        break;
                }
            }
        }

        if(!empty($form->settings['captcha'])){
            $form->settings['captcha_details'] = Captcha::get_form_captcha_details($form->settings['captcha']);
        }
        
        // Creating a field map for easy access of field configuration
        $fields = [];
        foreach ((array) $form->_field_config as $step) {
            $fields = array_merge($fields, $this->parseBlockElements(array($step)));
        }
        $form->fields = $fields;

        return $form;
    }

    // Save field configuration for the form.
    public function save_form($post_id) {
        // Validate the post save using the reusable function
        if (!AppUtility::validatePostSave($post_id, $this->post_type)) {
            return;
        }
    
        // Parse and sanitize blocks from the post content
        $post_content = get_post_field('post_content', $post_id);
        $blocks = parse_blocks($post_content);
        //wp_trigger_error( __FUNCTION__, print_r($blocks, true), E_USER_NOTICE ); 
        if (empty($blocks)) {
            AppUtility::update_meta($post_id, '_field_config', array());
            return;
        }
        //print_r($blocks);
        // Save the sanitized block data to post meta
        AppUtility::update_meta($post_id, '_field_config', $blocks);
    }
    

    public function create_sample_step($content, $post){
        if ($post->post_type == AppUtility::FORM_POST_TYPE) {
            $content = AppUtility::SAMPLE_FORM_CONTENT;
        }
        return $content;
    }

    // Validates submission data 
    public function validate_form($data, $block_map, $form_id){
        $errors     = array();
        $form_data  = array();
        $form_model = Form::create_instance();
        $form       = $form_model->get_form($form_id); 
        $sub_model  = Submission::create_instance();

        //wp_trigger_error( __FUNCTION__, print_r($form->settings, true), E_USER_NOTICE );
        
        foreach($block_map as $field_name => $field_config){
            $attrs          = $field_config['attrs'];
            $type           = str_replace('kf/','',$field_config['blockName']);

            
            if(!isset($data[$field_name])){
                $data[$field_name] = null;
            } 
            
            $sanitized_data = $this->sanitize_data($data[$field_name], $field_config);
            
            if(isset($attrs['hidden']) && !empty($attrs['hidden'])){
                continue;
            }

            // if block is read only then skip it. 
            $read_only = isset($attrs['readOnly']) ? $attrs['readOnly'] : false;
            if (!empty($readOnly)) 
                continue;
            

            if(is_user_logged_in() && $form->settings['type'] == 'registration'){

                if(!empty($form->settings['primary_email_field']) && $form->settings['primary_email_field'] == $attrs['name']){
                    $sanitized_data = wp_get_current_user()->user_email;
                }

                if(!empty($form->settings['username_field']) && $form->settings['username_field'] == $attrs['name']){
                    $sanitized_data = wp_get_current_user()->user_login;
                }
            }
            

            if(isset($attrs['unique']) && !empty($attrs['unique'])){
                $does_not_exist = $sub_model->is_unique_value($sanitized_data,$field_name, $form_id);
                if(!$does_not_exist){
                    $errors[$field_name] = isset($attrs['uniqueErr']) ? $attrs['uniqueErr'] : 'Value already exists in the database.';
                }
            }
            
            // Checking for required fields.
            if(!empty($attrs['required'])){
                if(empty($sanitized_data)){
                    $errors[$field_name] = isset($attrs['requiredError']) ? $attrs['requiredError'] : 'This is a required field.';
                }
            }
            
            $max_length = isset($attrs['maxLength']) ? $attrs['maxLength'] : false;
            if (!empty($max_length) && !empty($sanitized_data)){
                $length = AppUtility::text_length($sanitized_data);
                if($length > $max_length){
                    /* translators: %d: maximum number of characters */
                    $errors[$field_name] = sprintf(__('Length cannot be greater than %d characters', 'koalaforms'),$max_length);
                }
            }

            $min_length = isset($attrs['minLength']) ? $attrs['minLength'] : false;
            if (!empty($min_length) && !empty($sanitized_data)){
                $length = AppUtility::text_length($sanitized_data);
                if($length < $min_length){
                    /* translators: %d: minimum number of characters */
                    $errors[$field_name] = sprintf(__('Length cannot be less than %d characters', 'koalaforms'), $min_length);
                }
            }

            $max = isset($attrs['max']) ? $attrs['max'] : false;
            if (!empty($max) && !empty($sanitized_data)){
                if($sanitized_data > $max){
                    /* translators: %d: maximum number of characters */
                    $errors[$field_name] = sprintf(__('Value cannot be greater than %d', 'koalaforms'),$max);
                }
            }

            $min = isset($attrs['min']) ? $attrs['min'] : false;
            if (!empty($min) && !empty($sanitized_data)){
                if($sanitized_data < $min){
                    /* translators: %d: minimum number of characters */
                    $errors[$field_name] = sprintf(__('Value cannot be less than %d', 'koalaforms'), $min);
                }
            }

            $pattern  = isset($attrs['pattern']) ? $attrs['pattern'] : false;
            if(!empty($pattern) && !empty($sanitized_data)){
                if (!preg_match("/".$pattern."/", $sanitized_data)) {
                    $patternError  = $attrs['patternError'];
                    $errors[$field_name] = $patternError;
                }
            }

            if ($type == 'email'){
                if(!empty($data[$field_name]) && !is_email($sanitized_data)){
                    $errors[$field_name] = __('Invalid email value.', 'koalaforms');
                }
            }

            if ($type == 'date' && !empty($data[$field_name])){
                if(isset($attrs['isAge']) && !empty($attrs['isAge'])){
                    $min_age = $attrs['minAge'] ?? null;
                    $max_age = $attrs['maxAge'] ?? null;
                    
                    $validation_message = !empty($attrs['ageValidationMessage']) ? $attrs['ageValidationMessage'] : 'Invalid age.';
                    $age = AppUtility::calculate_age_from_date($sanitized_data);
                    if ($age === null) {
                        $errors[$field_name] = $validation_message;
                    } else {
                        if (!empty($min_age) && $age < $min_age) {
                            $errors[$field_name] = $validation_message;
                        } elseif (!empty($max_age) && $age > $max_age) {
                            $errors[$field_name] = $validation_message;
                        }
                        else if(!AppUtility::is_past_date($sanitized_data)){
                            $errors[$field_name] = $validation_message;
                        }
                    }
                }
                
            }

            if ($type == 'url'){
                if(!empty($data[$field_name]) && !filter_var($data[$field_name],FILTER_VALIDATE_URL)){
                    $errors[$field_name] = __('Invalid URL.', 'koalaforms');
                }
            }
            $form_data[$field_name] = $sanitized_data;

            /****** Handling of nested fields. Example: Address  */
            if($type == 'address'){
                // Since it's a stringified version, first decode it. 
                $address_data = json_decode(stripslashes($sanitized_data ?? ''), true);
                $inner_blocks = $field_config['innerBlocks'];
                
                if(count($inner_blocks)>0){
                    $parsed_blocks = $this->parseBlockElements($field_config['innerBlocks']);
                    $address_errors = $this->validate_form($address_data, $parsed_blocks,$form_id)['errors'];
                    if(count($address_errors)>0){
                        $errors[$field_name] = $address_errors;
                    }
                }
            }
        }

        $result = array('errors'=> $errors, 'form_data' => $form_data);
        return $result;
    }

    public function verify_captcha($response, $form_id){
        $form_model = Form::create_instance();
        $form       = $form_model->get_form($form_id);

        $options = AppUtility::get_global_options();
        $captcha_options = array();

        if($form->settings['captcha'] == 'google_recaptcha'){
            $secret      = $options['google_recaptcha_secret_key'];
            $api_url     = add_query_arg(
                array(
                    'secret'   => $secret,
                    'response' => $response,
                ),
                'https://www.google.com/recaptcha/api/siteverify'
            );
            $api_response    = wp_remote_get( $api_url, array( 'timeout' => 10 ) );
            if ( is_wp_error( $api_response ) ) {
                wp_trigger_error( __FUNCTION__,  'KoalaForms reCAPTCHA verify failed: ' . $api_response->get_error_message() , E_USER_NOTICE );
                return false;
            }
            $body            = wp_remote_retrieve_body( $api_response );
            $captcha_success = json_decode( $body );
            return isset( $captcha_success->success ) ? $captcha_success->success : false;
        }

        return true;
    }

    // Flats all the Gutenberg blocks after recursively traversing the inner nodes. 
    public function parseBlockElements($blocks){
        $result = [];
        // Recursive function to process each block
        $processBlocks = function (array $blocks) use (&$result, &$processBlocks) {
            foreach ($blocks as $block) {
                // Check if the block contains 'kf' and exclude specific block names
                if (isset($block['blockName']) &&
                    strpos($block['blockName'], 'kf') !== false &&
                    !in_array($block['blockName'], ['core/columns', 'core/column', 'kf/step'], true)
                ) {
                    // Ensure the block has 'attrs' and 'name' for the map
                    if (isset($block['attrs']['name'])) {
                        $blockName = $block['attrs']['name'];
                        // Add the block to the map using the name as key
                        $result[$blockName] = $block;
                    }
                }
    
                // Continue recursion if innerBlocks exist
                if (!empty($block['innerBlocks']) && !in_array($block['blockName'], ['kf/address'])) {
                    $processBlocks($block['innerBlocks']);
                }
            }
        };
        // Start processing
        $processBlocks($blocks);
    
        return $result;

    }

    private function sanitize_data($value, $field_config){
        if(!isset($value)){
            return null;
        }
        $value = wp_unslash($value);
        $type  = str_replace('kf/','',$field_config['blockName']);

        if($type == 'email'){
            return sanitize_email($value);
        }
        if($type == 'url'){
            return sanitize_url($value);
        }
        if($type == 'number'){
            return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }
        return sanitize_text_field($value);
    }

    // Searches a step block by name
    public function step_config($blocks, $stepName){
        foreach ($blocks as $block) {
            // Check if the block contains 'kf' and exclude specific block names
            if (isset($block['blockName']) && $block['blockName'] == 'kf/step' && $block['attrs']['name'] == $stepName) {
                $block_map =  $this->parseBlockElements(array($block));
                return $block_map;
            }
        }
        return null;
    }

    // Get sanitized data from $_POST as per the form configuration
    public function posted_data($data, $form_id){
        $form                   = $this->get_form($form_id);
        $sanitized_posted_data  = array();
        foreach ($form->fields as $key => $field) {
            if(isset($field['attrs']) && isset($field['attrs']['name'])){
                $field_name = $field['attrs']['name'];
                $posted_value = isset($data[$field_name]) ? $data[$field_name] : null;
                $sanitized_posted_data[$field_name] = $this->sanitize_data($posted_value, $field);

                if(is_user_logged_in() && $form->settings['type'] == 'registration'){

                    if(!empty($form->settings['primary_email_field']) && $form->settings['primary_email_field'] == $field_name){
                        $sanitized_posted_data[$field_name] = wp_get_current_user()->user_email;
                    }
    
                    if(!empty($form->settings['username_field']) && $form->settings['username_field'] == $field_name){
                        $sanitized_posted_data[$field_name]= wp_get_current_user()->user_login;
                    }
                }

            }
        }
        return $sanitized_posted_data;
    }

    public static function add_extra_tablenav($post_type){
        global $wpdb;
        // Getting the rows as get_posts does not work reliably here
        $forms = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->prepare(
                "
                SELECT ID, post_title
                FROM {$wpdb->posts}
                WHERE post_type = %s
                AND post_status = 'publish'
                ORDER BY post_title ASC
                ",
                AppUtility::FORM_POST_TYPE
            )
        );

        $selected_form = null;
        if(isset($_GET['form_filter'])){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $selected_form = sanitize_text_field( wp_unslash( $_GET['form_filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }
        
        /** Grab all of the options that should be shown */
        $options[] = sprintf(
            '<option value="">%s</option>',
            esc_html__( 'All Forms', 'koalaforms')
        );

        foreach ( $forms as $form ) {
            $selected = selected( $selected_form, $form->ID, false );
            $value = esc_attr( $form->ID );
            $label = esc_html( $form->post_title );

            $options[] = sprintf(
                '<option %s value="%s">%s</option>',
                $selected,
                $value,
                $label
            );
        }

        /** Output the dropdown menu */
        echo '<select class="" id="form_filter" name="form_filter">';
        echo wp_kses_post( join( "\n", $options ) );
        echo '</select>';

        wp_nonce_field('koalaforms_filter_submission_action', 'submission_filter_nonce');
    }
}
