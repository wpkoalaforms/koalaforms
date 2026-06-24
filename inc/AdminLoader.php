<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminLoader{

    private static $instance = null;

    private function __construct() {
        add_action('admin_menu', array($this, 'register_menus'));
        add_action('admin_enqueue_scripts', array($this,'load_scripts'));
        add_action('admin_notices', array($this, 'create_form'));
        add_filter('allowed_block_types_all', array($this, 'form_block_types'), 10, 2 );
        add_action('pre_get_posts', array($this, 'filter_posts'));
        add_action('add_meta_boxes', array($this, 'submission_meta_boxes'));
        add_action('save_post_' . AppUtility::SUBMISSION_POST_TYPE, array($this, 'save_submission_meta'));
        $this->global_hooks();
        $this->includes();
    }

    private function global_hooks(){
        // Placeholdder for future.
    }

    // Creates and returns instance
    public static function create_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // registering admin menus
    public function register_menus() {
        add_menu_page(
            __( 'Koala Forms', 'koalaforms'),
            __( 'Koala Forms', 'koalaforms'),
            'manage_options',
            'koalaforms-overview',
            array( $this, 'admin_page' ),
            'dashicons-feedback'
        );

        add_submenu_page(
            'koalaforms-overview',
            __( 'Dashboard', 'koalaforms'),
            __( 'Dashboard', 'koalaforms'),
            'manage_options',
            'koalaforms-dashboard',
            array( $this, 'admin_page' )
        );

        add_submenu_page(
            'koalaforms-overview',
            __( 'Submissions', 'koalaforms'),
            __( 'Submissions', 'koalaforms'),
            'manage_options',
            'koalaforms-submissions',
            array( $this, 'admin_page' )
        );

        add_submenu_page(
            'koalaforms-overview',
            __( 'Global Settings', 'koalaforms'),
            __( 'Global Settings', 'koalaforms'),
            'manage_options',
            'koalaforms-settings',
            array( $this, 'admin_page' )
        );

        add_submenu_page(
            'koalaforms-overview',
            __( 'Analytics', 'koalaforms'),
            __( 'Analytics', 'koalaforms'),
            'manage_options',
            'koalaforms-analytics',
            array( $this, 'admin_page' )
        );

        add_submenu_page(
            'koalaforms-overview',
            __( 'Logs', 'koalaforms' ),
            __( 'Logs', 'koalaforms' ),
            'manage_options',
            'koalaforms-logs',
            array( $this, 'admin_page' )
        );

        remove_submenu_page('koalaforms-overview', 'koalaforms-overview');

        remove_meta_box('postcustom', AppUtility::SUBMISSION_POST_TYPE, 'normal');
        remove_meta_box( 'submitdiv', AppUtility::SUBMISSION_POST_TYPE, 'side' );
    }

    public function admin_page() {
        do_action( 'koalaforms_admin_page' );
    }

    private function includes(){
        require_once 'admin/admin_page.php';
    }

    public function create_form(){
        $current_screen = get_current_screen();
		if ($current_screen && ($current_screen->post_type == AppUtility::FORM_POST_TYPE)){
            wp_enqueue_script('koalaforms-admin-js');
            wp_enqueue_style('koalaforms-admin-style');
            include('admin/html/create_form.php');
        }
    }

    // Loading javascript and style assests. 
    public function load_scripts(){
        global $wp_roles;
        wp_register_script('koalaforms-admin-js', KOALAFORMS_PLUGIN_URL.'assets/admin/js/admin.js', array('jquery'), KOALAFORMS_VERSION, true);
        wp_register_script('koalaforms-admin-dashboard', KOALAFORMS_PLUGIN_URL . 'assets/admin/js/dashboard-bundle.js', array(), KOALAFORMS_VERSION, true);
        wp_register_script('koalaforms-admin-settings', KOALAFORMS_PLUGIN_URL . 'assets/admin/js/settings-bundle.js', array(), KOALAFORMS_VERSION, true);
        wp_register_script('koalaforms-admin-analytics', KOALAFORMS_PLUGIN_URL . 'assets/admin/js/analytics-bundle.js', array(), KOALAFORMS_VERSION, true);
        wp_register_style('koalaforms-admin-style',KOALAFORMS_PLUGIN_URL.'assets/admin/css/style.css', array(), KOALAFORMS_VERSION);
        
        $screen = get_current_screen();
        if ($screen && $screen->post_type === AppUtility::SUBMISSION_POST_TYPE) {
            wp_enqueue_script('koalaforms-admin-js');
            wp_enqueue_style('koalaforms-admin-style');

            // Allow Pro add-ons to enqueue their own scripts on the submission screen.
            do_action( 'koalaforms_submission_enqueue_scripts' );
        }

        $roles = $wp_roles->get_names();
        // Localize the script with AJAX URL and nonce
        wp_localize_script('koalaforms-admin-js', 'koalaforms_ajax_object',
                            array
                            (
                                'ajax_url' => admin_url('admin-ajax.php'),
                                'admin_nonce' => wp_create_nonce('koalaforms_admin_ajax_nonce'),
                                'roles' => $roles,
                            )
                          );
    }

    public function form_block_types($allowed_blocks, $block_editor_context){
        // Check if editing a custom post type
			if ( isset( $block_editor_context->post ) && $block_editor_context->post->post_type === AppUtility::FORM_POST_TYPE ) {
				// Define the allowed blocks
				return AppUtility::allowedBlocks();
			}
			// Return all blocks for other post types
			return $allowed_blocks;
    }
  
    public function filter_posts($query){
        if(isset($_GET['submission_filter_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['submission_filter_nonce'])), 'koalaforms_filter_submission_action')){
            // Make sure we're only modifying the query on the correct page
            if (is_admin() && $query->is_main_query() && $query->get('post_type') === AppUtility::SUBMISSION_POST_TYPE) {
                $submission_model = Submission::create_instance();
                $submission_model->sub_content_search($query);
            }
        }
        
    }

    public function submission_meta_boxes(){
        add_meta_box(
            'submission_meta_box',
            'Submission Details',
            array($this, 'submission_meta_box_callback'),
            AppUtility::SUBMISSION_POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'submission_info_meta_box',
            'Submission Information',
            array($this, 'submission_info_meta_box_callback'),
            AppUtility::SUBMISSION_POST_TYPE,
            'side',
            'high'
        );

    }

    public function submission_meta_box_callback( $post ) {
        ( new SubmissionMetaBox( $post ) )->render();
    }

    public function submission_info_meta_box_callback($post){
        $submission_model = Submission::create_instance();
        $submission_model->mark_submission_as_read($post->ID);
        $submission    = $submission_model->get_submission($post->ID);
        $raw_history   = AppUtility::get_meta($post->ID, 'submission_stage_history', true);
        $stage_history = !empty($raw_history) ? json_decode($raw_history, true) : [];
        include_once('admin/html/submission_info_output.php');
    }

    public function save_submission_meta($post_id){
        if (!isset($_POST['koalaforms_submission_stage_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['koalaforms_submission_stage_nonce'])), 'koalaforms_submission_stage_nonce_action')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $new_stage = isset($_POST['koalaforms_submission_stage']) ? sanitize_text_field(wp_unslash($_POST['koalaforms_submission_stage'])) : '';
        $old_stage = AppUtility::get_meta($post_id, 'submission_stage', true);

        AppUtility::update_meta($post_id, 'submission_stage', $new_stage);

        if ($new_stage !== $old_stage && $new_stage !== '') {
            $note    = isset($_POST['koalaforms_stage_note']) ? sanitize_textarea_field(wp_unslash($_POST['koalaforms_stage_note'])) : '';
            $history = AppUtility::get_meta($post_id, 'submission_stage_history', true);
            $history = !empty($history) ? json_decode($history, true) : [];
            $history[] = [
                'stage'      => $new_stage,
                'changed_at' => current_time('mysql'),
                'changed_by' => get_current_user_id(),
                'note'       => $note,
            ];
            AppUtility::update_meta($post_id, 'submission_stage_history', wp_json_encode($history));
        }
    }
}
