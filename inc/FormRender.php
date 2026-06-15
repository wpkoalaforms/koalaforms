<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @description: The Blocks class provides functionality for managing Gutenberg block categories
 * 				 and registering custom Gutenberg blocks.
 */

class FormRender {

    private static $instance = null;

    private function __construct() {
        // Register shortcode.
        add_shortcode('KoalaForms', array($this, 'form_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'load_scripts'));
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Shortcode wrapper for the outputting a form.
     *
     * @since 1.0.0
     *
     * @param array $atts
     *
     * @return string
     */
    public function form_shortcode($atts) {
        $atts = shortcode_atts(array(
                    'form_id' => false,
                    'title' => false,
                    'layout_options' => 1,
                    'description' => false
                    ), $atts, 'output');
                
        ob_start();
            $this->render_form($atts);
            wp_enqueue_script('koalaforms-form-bundle');
            wp_enqueue_script('google-recaptcha');
            wp_enqueue_style('koalaforms-front-style');
        return ob_get_clean();
    }

    public function render_form($atts) {
        $result = $this->form_submission_allowed($atts['form_id']);
        if (!$result['show']) {
            include('client/form_unavailable.php');
            return;
        }

        $form_model = Form::create_instance();
        $form = $form_model->get_form($atts['form_id']);

        $options      = AppUtility::get_global_options();
        $form_styling = isset($options['form_styling']) ? $options['form_styling'] : 'classic';
        include('client/form_output.php');
    }

    public static function form_submission_allowed($id){
        $result = array('show' => true, 'error' => '');

        if (empty($id)) {
            $result['show'] = false;
            $result['error'] = esc_html('No such form exists in Database.', 'koalaforms');
            return $result;
        }

        $form_model = Form::create_instance();
        $form = $form_model->get_form($id);

        if (empty($form)) {
            $result['show']  =  false;
            $result['error'] = esc_html('No such form exists in Database.', 'koalaforms');
            return $result;
        }

        if (empty($form->settings['is_active'])){
            $result['show']  =  false;
            $result['error'] = $form->settings['inactive_message'];
            return $result;
        }

        // check for no of submissions by this user 
        $submission_model = Submission::create_instance();
        $current_user     = get_current_user_id();
        $logged_in_user_restriction = $form->settings['logged_in_user_restriction'] ?? false;
        $access_denied_msg          = $form->settings['access_denied_msg'] ?? '';

        if (!empty($logged_in_user_restriction)) {
            if ($current_user == 0) {
                $result['show']  = false;
                $result['error'] = esc_html($access_denied_msg);
                return $result;
            }
           
            $limit_per_user = $form->settings['submission_limit_per_user'];
            if(!empty($limit_per_user)){
                $count = $submission_model->no_of_submissions_by_user(get_current_user_id(), $id);
                if($limit_per_user <= $count){
                    $result['show']  =  false;
                    $result['error'] = esc_html('You have reached the limit of submissions for this form.', 'koalaforms');
                    return $result;
                }
            }

            $current_user = wp_get_current_user();
            $current_user_roles = (array) $current_user->roles;

            
            if (!empty($form->settings['allowed_user_roles'])) {
                $allowed = false;
                $allowed_roles = explode(';', $form->settings['allowed_user_roles']);
                
                foreach ($current_user_roles as $role) {
                    if (in_array($role, $allowed_roles)) {
                        $allowed = true;
                        break;
                    }
                }
                
                if ($allowed == false) {
                    $result['show']  = false;
                    $result['error'] = esc_html($access_denied_msg);
                }
            }
            
        }

        $submission_limits = $form->settings['total_submission_limit'];
        if(!empty($submission_limits)){
            $total_submissions = $submission_model->total_submissions($id);
            if($total_submissions >= $submission_limits){
                $result['show']  =  false;
                $result['error'] = esc_html('This form has reached the limit of submissions.', 'koalaforms');
                return $result;
            }
        }
        
        
        if(!empty($logged_in_user_restriction) && $current_user == 0){
            $result['show']  =  false;
            $result['error'] = esc_html('Only logged in users\' are allowed to access the form.', 'koalaforms');
        }
        return $result;
    }

    public function load_scripts() {
        $this->register_scripts();
        $this->register_styles();
    }

    private function register_scripts() {
        wp_register_script('koalaforms-form-bundle',KOALAFORMS_PLUGIN_URL.'assets/client/js/form-bundle.js',array('jquery'), KOALAFORMS_VERSION, true);

        // Only register reCAPTCHA script if site key is configured
        $options = AppUtility::get_global_options();
        if ( !empty($options['google_recaptcha_site_key']) ) {
            wp_register_script(
                'google-recaptcha',
                'https://www.google.com/recaptcha/api.js',
                [],
                null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- external CDN script, version param not applicable
                true
            );
        }
        wp_localize_script('koalaforms-form-bundle', 'koalaformsAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'load_form_nonce'    => wp_create_nonce('koalaforms_load_form_nonce'),
        ]);
    }

    public function register_styles() {
        wp_register_style('koalaforms-front-style', KOALAFORMS_PLUGIN_URL . 'assets/client/css/form.css','', KOALAFORMS_VERSION);
    }
}