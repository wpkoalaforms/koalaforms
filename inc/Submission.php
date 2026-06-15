<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @description: Class to support form related operations throughout the plugin.
 */
class Submission extends Post{

    public $post_type = null;
    private static $instance = null;
    

    private function __construct() {
        $this->post_type = AppUtility::SUBMISSION_POST_TYPE;
        add_action('pre_get_posts',array($this,'sub_content_search'),10,1);
        add_filter('posts_search', array($this,'submission_filter_by_string'), 10, 2);
        add_filter('koalaforms_post_submission', array($this, 'post_submission'), 1, 3);
        $this->admin_submission_table();
    }

    public function admin_submission_table(){
        add_filter('manage_'.AppUtility::SUBMISSION_POST_TYPE.'_posts_columns', array($this,'admin_submission_columns'));
        add_action('manage_'.AppUtility::SUBMISSION_POST_TYPE.'_posts_custom_column', array($this,'admin_submission_column_data'), 10, 2);
        add_filter('the_title', array($this,'sub_title_for_admin_list'), 10, 2);
        add_filter('manage_edit-'.AppUtility::SUBMISSION_POST_TYPE.'_sortable_columns', array($this,'make_sub_columns_sortable'));
    }

    // Creates and returns instance
    public static function create_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Inserts/Updates submission
    public function upsert_submission($data, $form_id, $client_info = true){
        $meta = array();
        foreach($data as $field_id => $field_value){
            $meta[$field_id] = $field_value;
        }
        $meta[AppUtility::meta_key('form_id')] = $form_id;
        $meta[AppUtility::meta_key('user_id')] = get_current_user_id();
        $meta[AppUtility::meta_key('submission_status')] = 'unread';
        
        if($client_info){
            $db_info = $this->device_browser_info();
            $meta[AppUtility::meta_key('browser')] = $db_info['browser'];
            $meta[AppUtility::meta_key('device')]  = $db_info['device'];
        }
        
        $args = array(
            'post_title' => wp_strip_all_tags('Submission for : #' . $form_id),
            'post_type' => $this->post_type,
            'post_status' => 'publish',
            'meta_input' => $meta
        );
        
        $id = $this->add($args);
        return $id;

    }

    public function device_browser_info() {
        if(!isset($_SERVER['HTTP_USER_AGENT'])){
            return [
                'device' => '',
                'browser' => '',
                'version' => ''
            ];
        }
        $userAgent = sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']));
    
        // Browser detection
        $browser = "Unknown Browser";
        $version = "";
        $browsers = [
            'Edge' => 'Edg',
            'Opera' => 'OPR',
            'Chrome' => 'Chrome',
            'Safari' => 'Safari',
            'Firefox' => 'Firefox',
            'Internet Explorer' => 'MSIE',
        ];
    
        foreach ($browsers as $name => $identifier) {
            if (strpos($userAgent, $identifier) !== false) {
                $browser = $name;
                preg_match("/" . $identifier . "[\/ ]([0-9\.]+)/", $userAgent, $matches);
                $version = $matches[1] ?? '';
                break;
            }
        }
    
        $device = $userAgent;
    
        return [
            'device' => $device,
            'browser' => $browser,
            'version' => $version
        ];
    }
    

    public function get_submission($post_id, $with_meta = true) {
        $submission = $this->get($post_id);
        if(empty($submission)) return null;

        return $with_meta
            ? $this->get_submission_with_meta($post_id)
            : $this->get_submission_without_meta($post_id);
    }

    private function get_submission_with_meta($post_id) {
        $submission_data = array();
        $all_meta = $this->get_submission_meta($post_id);
    
        $form_id_key = AppUtility::meta_key('form_id');
        if (empty($all_meta[$form_id_key][0])) {
            return array('error' => 'Form ID not found.');
        }

        $form_id    = $all_meta[$form_id_key][0];
        $form_model = Form::create_instance();
        $form       = $form_model->get_form($form_id);

        $fields_data = array();

        foreach ($all_meta as $key => $value) {
            if ($form && isset($form->fields[$key])) {
                // Handle Address Fields
                if (stristr($form->fields[$key]['blockName'], 'address')) {
                    $all_meta[$key][0] = $this->get_address_values(
                        $all_meta[$key][0],
                        $form_model->parseBlockElements($form->fields[$key]['innerBlocks'])
                    );
                }
    
                $fields_data[$key] = array(
                    'field' => $form->fields[$key],
                    'value' => $all_meta[$key][0],
                );
            } else {
                $unprefixed_key = strpos($key, AppUtility::PLUGIN_PREFIX) === 0
                    ? substr($key, strlen(AppUtility::PLUGIN_PREFIX))
                    : $key;
                $submission_data[$unprefixed_key] = $all_meta[$key][0];
            }
        }
    
        $submission_data['form_name']        = $form ? $form->post_title : '';
        $submission_data['form_id']          = $form_id;
        $submission_data['ID']               = $post_id;
        $submission_data['form_fields']      = $fields_data;
        $submission_data['submission_date']  = get_the_date('', $this->get($post_id));
    
        return $submission_data;
    }
    
    private function get_submission_without_meta($post_id) {
        $form_id = AppUtility::get_meta( $post_id, 'form_id', true );
        $form_model = Form::create_instance();
        $form       = $form_model->get_form($form_id);

        return array(
            'ID'              => $post_id,
            'form_name'       => $form->post_title,
            'form_id'         => $form_id,
            'submission_date' => get_the_date('', $this->get($post_id)),
        );
    }
    
    private function get_submission_meta($post_id) {
        return get_post_meta($post_id);
    }

    public function get_address_values($address_values, $address_blocks){
        $address_items = array();
        if (empty($address_values) || empty($address_blocks)){
            return "";
        }
        $address_values = json_decode($address_values, true);
        foreach($address_values as $add_field_key => $add_field_value){
            if(!isset($address_blocks[$add_field_key])) { // If field is deleted then continuel
                continue;
            }
            $add_field_config = $address_blocks[$add_field_key];
            $address_items[$add_field_config['attrs']['inputLabel']] = $add_field_value;
        }

        $address_string = "";
        foreach ($address_items as $key => $value) {
            $address_string .= "$key: $value\n";
        }
        return $address_string;
    }

    public function no_of_submissions_by_user($user_id, $form_id){
        $args = array(
            'post_type' => $this->post_type,
            'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key' => AppUtility::meta_key('form_id'),
                    'value' => $form_id,
                    'compare' => '='
                ),
                array(
                    'key' => AppUtility::meta_key('user_id'),
                    'value' => $user_id,
                    'compare' => '='
                )
            )
        );
        $posts = $this->get('', $args);
        return count($posts);
    }

    public function total_submissions($form_id){
        $args = array(
            'post_type' => $this->post_type,
            'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key' => AppUtility::meta_key('form_id'),
                    'value' => $form_id,
                    'compare' => '='
                )
            )
        );
        $posts = $this->get('', $args);
        return count($posts);
    }

    public function sub_content_search($query){
        if ( ! $query->is_main_query() ) {
            return;
        }

        if(!isset($_GET['submission_filter_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['submission_filter_nonce'])), 'koalaforms_filter_submission_action')){
            return;
        }
        
        $post_type = $query->get('post_type');
        if ( $query->is_search() || $post_type === AppUtility::SUBMISSION_POST_TYPE ) {
            $query->set( 'post_type',AppUtility::SUBMISSION_POST_TYPE );
        }

        // Check if the form filter parameter exists in the URL
        if (isset($_GET['form_filter']) && !empty($_GET['form_filter'])) {
            // Sanitize the input
            $form_value = sanitize_text_field(wp_unslash($_GET['form_filter']));
            
            // Get existing meta query (if any)
            $meta_query = $query->get('meta_query', array());
            
            // Add our form filter to the meta query
            $meta_query[] = array(
                'key' => AppUtility::meta_key('form_id'),
                'value' => $form_value,
                'compare' => '=',
            );
            
            // Update the query with our new meta query
            $query->set('meta_query', $meta_query);
        }
    }

    public function submission_filter_by_string($search, $wp_query ){
        global $wpdb;

        if ( ! is_search() || ! $wp_query->is_main_query() ) {
            return $search;
        }

        if ( $wp_query->get( 'post_type' ) !== AppUtility::SUBMISSION_POST_TYPE ) {
            return $search;
        }
    
        $search_term = $wp_query->get( 's' );
        if ( empty( $search_term ) ) {
            return $search;
        }
    
        $like = '%' . $wpdb->esc_like( $search_term ) . '%';
    
        // Search in post meta values too
        $meta_search = $wpdb->prepare(
            " OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta}
                WHERE post_id = {$wpdb->posts}.ID
                AND meta_value LIKE %s
            )",
            $like
        );
    
        return $search . $meta_search;
    }

    public function admin_submission_columns($columns){
        $cb_column = $columns['cb'];
        $new_columns = array('cb' => $cb_column);
        $new_columns['title'] = 'Submission ID';
        $new_columns['form_name'] = 'Form Name';
        $new_columns['submission_date'] = 'Submission Date';
        return array_merge($new_columns, array());
    }

    public function admin_submission_column_data($column, $post_id){
        $form_id   = AppUtility::get_meta($post_id, 'form_id', true);
        $post      = get_post($post_id);
        switch ($column) {
            case 'title':
                echo esc_html($post_id);
                break;
            case 'submission_date':
                    echo esc_html(gmdate('F j, Y', strtotime($post->post_date)));
                    break;
            case 'form_name':
                $form_name = ucwords(get_the_title($form_id));
                $link = sprintf(
                    '<a target="_blank" href="%s">%s</a>',
                    esc_url( admin_url( 'post.php?post=' . intval( $post_id ) . '&action=edit' ) ),
                    esc_html( $form_name )
                );
                echo wp_kses_post( $link );
                break;
        }
    }

    public function sub_title_for_admin_list($title, $post_id) {
        if (is_admin() && get_post_type($post_id) === AppUtility::SUBMISSION_POST_TYPE) {
            return esc_html('Submission: ' . absint($post_id));
        }
        return esc_html($title);
    }

    public function make_sub_columns_sortable($columns) {
        $columns['title'] = 'ID'; // key used in pre_get_posts
        $columns['submission_date'] = 'post_date';
        return $columns;
    }

    public function latest_submissions($limit = 5){
        $limit = is_numeric($limit) ? intval($limit) : 5;
        $args = array(
            'orderby'        => 'date',
            'order'          => 'DESC',
            'nopaging'       => false,
        );

        if ($limit < 0) {
            $args['posts_per_page'] = -1;
            $args['nopaging'] = true;
        } else {
            $args['posts_per_page'] = $limit > 0 ? $limit : 5;
        }

        $posts = $this->get('', $args);
        $submissions = array();
        foreach ($posts as $post) {
            $submissions[] = $this->get_submission($post->ID);
        }
        return $submissions;
    }

    public function mark_submission_as_read($post_id){
        if (empty($post_id)) {
            return false;
        }

        return AppUtility::update_meta(absint($post_id), 'submission_status', 'read');
    }

    /*
     * Checks for unique values for submissions related to a single form
     */

     public function is_unique_value($value, $field_name, $form_id, $submission_id = 0) {
        $meta_query_args = array(
            'relation' => 'AND', // Optional, defaults to "AND"
            array(
                'key' => $field_name,
                'value' => $value,
                'compare' => '='
            ),
            array(
                'key' => AppUtility::meta_key('form_id'),
                'value' => $form_id,
                'compare' => '='
            )
        );

        $args = array(
            'meta_query' => $meta_query_args // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        );

        if (!empty($submission_id)) {
            $args['exclude'] = array($submission_id); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
        }
        $submissions = $this->get('', $args);

        if (empty($submissions))
            return true;

        return false;
    }

    public function post_submission($response, $form_id,$sub_id){
        $form_model = Form::create_instance();
        $form       = $form_model->get_form($form_id);

        $submission_model = Submission::create_instance();
        $submission       = $submission_model->get_submission($sub_id);
        $sub_deleted      = false;
        
        if(empty($submission)) $response;

        $id = 0;
        $errors = array();
        if ($form->settings['type'] == "registration"){
            // Avoid user registration process if user already logged in
            if (is_user_logged_in()) {
                $id = get_current_user_id();
            } else {
                $sub_fields             = $submission['form_fields'];
                $username_field         = $form->settings['username_field'];
                $primary_email_field    = $form->settings['primary_email_field'];

                $should_create_user     = !empty($username_field) && !empty($sub_fields[$username_field]) 
                                          && !empty($primary_email_field) && !empty($sub_fields[$primary_email_field]['value']);
                
                if($should_create_user ){
                    $id = register_new_user($sub_fields[$username_field]['value'],$sub_fields[$primary_email_field]['value']);
                }

                if (is_wp_error($id)) {
                    // In case something goes wrong delete the submission
                    wp_delete_post($sub_id, true);
                    array_push($errors, $id->get_error_message($id->get_error_code()));
                    $sub_deleted = true;
                }
            }

            if (count($errors) == 0 ){
                $this->update_meta($sub_id, 'user_id', $id);

                foreach ($submission['form_fields'] as $item) {
                    $attrs = $item['field']['attrs'];
                    if(!empty($attrs['usermeta'])){
                        update_user_meta($id, $attrs['usermeta'], $item['value']);
                    }
                    
                }
            }
            
        }

        if(!empty($errors)){
            $response['form_errors']  = isset($response['form_errors']) ? array_merge($response['form_errors'], $errors) : $errors;
        }

        if($sub_deleted ) return $response;

       
        if($form->settings['unique_id']){
            $this->update_unique_id($form, $sub_id, );
        }

        return $response;
    }

   
    // Unique Submission ID
    public function update_unique_id($form, $sub_id) {
        
        $form_model = Form::create_instance();
        $unique_id = '';
        $unique_seq='';

        $unique_id_offset =  empty($form->settings['unique_id_offset']) ? 1 : $form->settings['unique_id_offset'];
        $unique_id_index  =  empty($form->settings['unique_id_index']) ? 1 : $form->settings['unique_id_index'];

        if (!empty($form->settings['unique_id_padding'])){
            $unique_id= str_pad($unique_id_index+$unique_id_offset,$form->settings['unique_id_padding'],'0',STR_PAD_LEFT);
        }
        else
        {
            $unique_id = $unique_id_index + $unique_id_offset;
        }

        $unique_id_prefix = $form->settings['unique_id_prefix'];
        $unique_seq = empty($unique_id_prefix) ?  $unique_id : $unique_id_prefix.$unique_id;

        $settings_meta = $this->get_meta($form->ID, 'form_settings');
        $settings_meta['unique_id_index'] =  $form->settings['unique_id_index'] + $unique_id_offset;

        $this->update_meta($form->ID, 'form_settings', $settings_meta);
        $this->update_meta($sub_id, 'unique_id', $unique_seq); // Updating Submission
    }
}
