<?php
namespace KoalaForms;

class RegisterTypes{

    const DEFAULT_STAGES = array('Submitted', 'Under Review', 'Approved', 'Rejected');

    const DEFAULT_SUCCESS_MESSAGE = '<div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;"><h3 style="margin-top: 0; color: #155724;">Thank You!</h3><p style="margin-bottom: 0;">Your form has been submitted successfully. We appreciate your interest and will get back to you soon.</p></div>';

    const DEFAULT_ADMIN_EMAIL_BODY = 'Hello Admin, <br><p>You have received a new submission. Here are the details:</p>{{REGISTRATION_DATA}}';

    public function __construct() {
        $this->register_form_cpt();
        $this->register_submission_cpt();
        //$this->register_email_template_cpt();
        add_action('restrict_manage_posts', array($this, 'add_extra_tablenav'), 10, 1);
        add_action('wp_after_insert_post', array($this, 'set_form_defaults'), 10, 3);
    }

    public function set_form_defaults($post_id, $post, $update) {
        if ($update || $post->post_type !== AppUtility::FORM_POST_TYPE) {
            return;
        }

        $existing = AppUtility::get_meta($post_id, 'form_settings', true);
        $settings = is_array($existing) ? $existing : array();

        $defaults = array(
            'stage'             => self::DEFAULT_STAGES,
            'default_stage'  => self::DEFAULT_STAGES[0],
            'success_message'   => self::DEFAULT_SUCCESS_MESSAGE,
            'admin_email_body'  => self::DEFAULT_ADMIN_EMAIL_BODY,
            'is_active'         => true,
            'inactive_message'  => 'This form is currently unavailable. Please check back later.',
            'access_denied_msg' => 'You are not authorised to access this form.',
        );

        $settings = array_merge($defaults, $settings);
        AppUtility::update_meta($post_id, 'form_settings', $settings);
    }

    private function register_form_cpt(){
        // Custom post type arguments, which can be filtered if needed
        $labels = array(
            'name'                  => __( 'Forms', 'koalaforms'),
            'singular_name'         => __( 'Form', 'koalaforms'),
            'add_new'               => __( 'Add New', 'koalaforms'),
            'add_new_item'          => __( 'Add New Form', 'koalaforms'),
            'edit_item'             => __( 'Edit Form', 'koalaforms'),
            'new_item'              => __( 'New Form', 'koalaforms'),
            'view_item'             => __( 'View Form', 'koalaforms'),
            'all_items'             => __( 'All Forms', 'koalaforms'),
            'search_items'          => __( 'Search Forms', 'koalaforms'),
            'not_found'             => __( 'No forms found.', 'koalaforms'),
            'not_found_in_trash'    => __( 'No forms found in Trash.', 'koalaforms'),
        );

        $args = array(
                    'labels' => $labels,
                    'public' => false,
                    'exclude_from_search' => true,
                    'show_ui' => true,
                    'show_in_admin_bar' => false,
                    'rewrite' => false,
                    'query_var' => false,
                    'can_export' => false,
                    'show_in_rest'=>true,
                    'show_in_menu'=>'koalaforms-overview',
                    'supports' => array('title', 'editor', 'custom-fields'),
                );

        // Register the post type for Forms
        register_post_type(AppUtility::FORM_POST_TYPE, $args);

        register_post_meta(AppUtility::FORM_POST_TYPE, AppUtility::meta_key('form_settings'), array(
            'show_in_rest' => array(
                'schema' => array(
                    'type'       => 'object',  // Allow object type
                    'properties' => AppUtility::FORM_PROPS,
                ),
            ),
            'type'         => 'object',
            'single'       => true,
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            }
        ));
    }

    private function register_email_template_cpt() {
        // Custom post type arguments, which can be filtered if needed
        $labels = array(
            'name'                  => __( 'Email Templates', 'koalaforms'),
            'singular_name'         => __( 'Email Template', 'koalaforms'),
            'add_new'               => __( 'Add New', 'koalaforms'),
            'add_new_item'          => __( 'Add New Template', 'koalaforms'),
            'edit_item'             => __( 'Edit Template', 'koalaforms'),
            'new_item'              => __( 'New Template', 'koalaforms'),
            'view_item'             => __( 'View Template', 'koalaforms'),
            'all_items'             => __( 'All Templates', 'koalaforms'),
            'search_items'          => __( 'Search Templates', 'koalaforms'),
            'not_found'             => __( 'No templates found.', 'koalaforms'),
            'not_found_in_trash'    => __( 'No templates found in Trash.', 'koalaforms'),
        );

        $args = array(
                    'labels' => $labels,
                    'public' => false,
                    'exclude_from_search' => true,
                    'show_ui' => true,
                    'show_in_admin_bar' => false,
                    'rewrite' => false,
                    'query_var' => false,
                    'capability_type' => 'post',
                    'can_export' => false,
                    'show_in_rest'=>true,
                    'show_in_menu'=>'koalaforms-overview',
                    'supports' => array('title', 'editor', 'thumbnail'),
                );

        // Register the post type for email templates
        register_post_type(AppUtility::EMAIL_POST_TYPE, $args);
    }

    private function register_submission_cpt(){
        $labels = array(
            'name'                  => __( 'Submission', 'koalaforms'),
            'singular_name'         => __( 'Submission', 'koalaforms'),
            'add_new'               => __( 'Add New', 'koalaforms'),
            'add_new_item'          => __( 'Add New Submission', 'koalaforms'),
            'edit_item'             => __( 'Edit Submission', 'koalaforms'),
            'new_item'              => __( 'New Submission', 'koalaforms'),
            'view_item'             => __( 'View Submission', 'koalaforms'),
            'all_items'             => __( 'Submissions', 'koalaforms'),
            'search_items'          => __( 'Search Submission', 'koalaforms'),
            'not_found'             => __( 'No Submissions found.', 'koalaforms'),
            'not_found_in_trash'    => __( 'No Submissions found in Trash.', 'koalaforms'),
        );

        $args = array(
                    'labels' => $labels,
                    'public' => false,
                    'exclude_from_search' => true,
                    'show_ui' => true,
                    'show_in_admin_bar' => false,
                    'rewrite' => false,
                    'query_var' => false,
                    'can_export' => false,
                    'show_in_rest'=>true,
                    'capabilities' => array('create_posts' => false),
                    'show_in_menu'=>false,
                    'supports' => array('custom-fields'),
                    'map_meta_cap' => true
                );

        // Register the post type for Forms
        register_post_type(AppUtility::SUBMISSION_POST_TYPE, $args);
    }
    public function add_extra_tablenav($post_type){
        // Adding a Form dropdown for submissions
        if($post_type == AppUtility::SUBMISSION_POST_TYPE){
            Form::add_extra_tablenav($post_type);
            return;
        }
    }
}
