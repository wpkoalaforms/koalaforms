<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminPage{
    
    public function __construct() {
        add_action('koalaforms_admin_page', array($this, 'init'));
    }

    public function init(){
        $this->assets();
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        switch($page){
            case 'koalaforms-dashboard': return $this->dashboard();
            case 'koalaforms-help': return $this->help();
            case 'koalaforms-intro': return $this->intro();
            case 'koalaforms-overview': return $this->overview();
            case 'koalaforms-settings': return $this->settings();
            case 'koalaforms-tools': return $this->tools();
            case 'koalaforms-submissions': return $this->submissions();
        }
    }

    public function assets(){
        $page = isset($_GET['page']) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ('koalaforms-dashboard' !== $page && 'koalaforms-settings' !== $page) {
            wp_enqueue_script('koalaforms-admin-js');
        }
        wp_enqueue_style('koalaforms-admin-style');
    }

    public function dashboard(){
        $this->enqueue_dashboard_app();
        include 'html/dashboard_output.php';
    }

    public function help(){
        include 'html/help_output.php';
    }

    public function intro(){
        include 'html/intro_output.php';
    }

    public function overview(){
        include 'html/overview_output.php';
    }

    public function settings(){
        // Check if form is submitted
        if (isset($_POST['koalaforms_save_settings']) && check_admin_referer('koalaforms_settings_nonce', 'koalaforms_nonce')) {
            // Process form submission
            $this->save_global_settings();
        }
        $this->enqueue_settings_app();
        include 'html/settings_output.php';
    }

    private function enqueue_settings_app() {
        $options = AppUtility::get_global_options();
        $payload = array(
            'pageTitle' => __('Global Settings', 'koalaforms'),
            'pageDescription' => __('Set up KoalaForms branding, storage, and spam protection defaults for the plugin.', 'koalaforms'),
            'saveLabel' => __('Save Changes', 'koalaforms'),
            'settings' => array(
                'form_styling' => $this->normalize_setting_value($options['form_styling'] ?? 'classic'),
                'email_template' => $this->normalize_setting_value($options['email_template'] ?? 'classic'),
                'ip_logging' => !empty($options['ip_logging']),
                'enable_gdpr_consent' => !empty($options['enable_gdpr_consent']),
                'google_recaptcha_type' => $this->normalize_setting_value($options['google_recaptcha_type'] ?? 'checkbox'),
                'google_recaptcha_site_key' => $options['google_recaptcha_site_key'] ?? '',
                'google_recaptcha_secret_key' => $options['google_recaptcha_secret_key'] ?? '',
                'hcaptcha_type' => $this->normalize_setting_value($options['hcaptcha_type'] ?? 'checkbox'),
                'hcaptcha_site_key' => $options['hcaptcha_site_key'] ?? '',
                'hcaptcha_secret_key' => $options['hcaptcha_secret_key'] ?? '',
                'hcaptcha_threshold' => $options['hcaptcha_threshold'] ?? '0.5',
                'cloudfare_site_key' => $options['cloudfare_site_key'] ?? '',
                'cloudfare_secret_key' => $options['cloudfare_secret_key'] ?? '',
            ),
            'captchaOptions' => Captcha::active_captcha_options(),
        );

        wp_enqueue_script('koalaforms-admin-settings');
        wp_localize_script('koalaforms-admin-settings', 'koalaformsSettingsData', $payload);
    }

    private function enqueue_dashboard_app() {
        $payload = $this->prepare_dashboard_payload();

        wp_enqueue_script('koalaforms-admin-dashboard');
        wp_localize_script('koalaforms-admin-dashboard', 'kfDashboardPageData', $payload);
    }

    private function prepare_dashboard_payload() {
        $form_model = Form::create_instance();
        $submission_model = Submission::create_instance();

        $recent_forms = $form_model->get('', array(
            'post_type'        => AppUtility::FORM_POST_TYPE,
            'post_status'      => 'any',
            'orderby'          => 'date',
            'order'            => 'DESC',
            'posts_per_page'   => 5,
            'no_found_rows'    => true,
            'suppress_filters' => false,
        ));

        $all_form_ids = get_posts(array(
            'post_type'        => AppUtility::FORM_POST_TYPE,
            'post_status'      => 'any',
            'fields'           => 'ids',
            'nopaging'         => true,
            'no_found_rows'    => true,
            'suppress_filters' => false,
        ));

        $total_forms = is_array($all_form_ids) ? count($all_form_ids) : 0;
        $active_forms = 0;

        if (!empty($all_form_ids)) {
            foreach ($all_form_ids as $form_id) {
                $post_status = get_post_status($form_id);
                $settings = AppUtility::get_meta($form_id, 'form_settings', true);
                if ('publish' === $post_status && !empty($settings['is_active'])) {
                    $active_forms++;
                }
            }
        }

        $submission_counts = wp_count_posts(AppUtility::SUBMISSION_POST_TYPE);
        $total_submissions = !empty($submission_counts->publish) ? absint($submission_counts->publish) : 0;
        $recent_submissions = $submission_model->latest_submissions();
        $last_submission_date = !empty($recent_submissions[0]['submission_date']) ? $recent_submissions[0]['submission_date'] : '';
        $recent_submission_week_query = new \WP_Query(array(
            'post_type'        => AppUtility::SUBMISSION_POST_TYPE,
            'post_status'      => 'publish',
            'fields'           => 'ids',
            'posts_per_page'   => 1,
            'no_found_rows'    => false,
            'date_query'       => array(
                array(
                    'after' => '7 days ago',
                ),
            ),
            'suppress_filters' => false,
        ));
        $submissions_this_week = !empty($recent_submission_week_query->found_posts) ? absint($recent_submission_week_query->found_posts) : 0;

        $recent_forms_payload = array();
        $forms_needing_attention = 0;
        $forms_with_entries = 0;
        if (!empty($recent_forms)) {
            foreach ($recent_forms as $form) {
                $form_settings = AppUtility::get_meta($form->ID, 'form_settings', true);
                $is_active = !empty($form_settings['is_active']);
                $status_label = 'publish' !== $form->post_status ? ucfirst($form->post_status) : ($is_active ? __('Active', 'koalaforms') : __('Inactive', 'koalaforms'));
                $status_class = 'publish' !== $form->post_status ? $form->post_status : ($is_active ? 'active' : 'inactive');
                $entries = $submission_model->total_submissions($form->ID);

                if ('publish' === $form->post_status) {
                    if (!$is_active) {
                        $forms_needing_attention++;
                    }
                } else {
                    $forms_needing_attention++;
                }

                if (!empty($entries)) {
                    $forms_with_entries++;
                }

                $recent_forms_payload[] = array(
                    'id' => absint($form->ID),
                    'title' => $form->post_title ? $form->post_title : __('Untitled form', 'koalaforms'),
                    'statusLabel' => $status_label,
                    'statusClass' => $status_class,
                    'date' => get_the_date('', $form),
                    'entries' => number_format_i18n($entries),
                    /* translators: %d: form ID number */
                    'shortcode' => sprintf(__('Shortcode: [KoalaForms form_id="%d"]', 'koalaforms'), absint($form->ID)),
                );
            }
        }
        $summary_title = __('Healthy workspace', 'koalaforms');
        $summary_copy = __('Your forms are live and collecting data.', 'koalaforms');
        $summary_label = __('Healthy', 'koalaforms');
        $summary_tone = 'healthy';
        $focus_points = array(
            array(
                'title' => __('Review inactive forms', 'koalaforms'),
                'copy' => __('Keep the live set healthy and remove anything that no longer needs to collect entries.', 'koalaforms'),
            ),
            array(
                'title' => __('Watch recent submissions', 'koalaforms'),
                'copy' => __('A quick scan of the latest entries helps you spot patterns, gaps, or setup issues early.', 'koalaforms'),
            ),
            array(
                'title' => __('Use the dashboard as a status board', 'koalaforms'),
                'copy' => __('Start here before opening the editor so you know exactly what needs attention.', 'koalaforms'),
            ),
        );

        if (0 === $total_forms) {
            $summary_title = __('Create your first form', 'koalaforms');
            $summary_copy = __('Set up a simple form to start collecting submissions.', 'koalaforms');
            $summary_label = __('Getting started', 'koalaforms');
            $summary_tone = 'warm';
            $focus_points = array(
                array(
                    'title' => __('Start with a simple form', 'koalaforms'),
                    'copy' => __('A short contact form is the fastest way to get comfortable with the workflow.', 'koalaforms'),
                ),
                array(
                    'title' => __('Publish it when the layout is ready', 'koalaforms'),
                    'copy' => __('Once the fields and message look good, make the form live and begin collecting entries.', 'koalaforms'),
                ),
                array(
                    'title' => __('Return after the first response', 'koalaforms'),
                    'copy' => __('The dashboard will immediately start showing activity and help you confirm the capture flow.', 'koalaforms'),
                ),
            );
        } elseif (0 === $active_forms) {
            $summary_title = __('Activate at least one form', 'koalaforms');
            $summary_copy = __('Your forms exist, but none are actively collecting entries yet.', 'koalaforms');
            $summary_label = __('Attention needed', 'koalaforms');
            $summary_tone = 'warning';
            $focus_points = array(
                array(
                    'title' => __('Activate one form', 'koalaforms'),
                    'copy' => __('Open the forms list and turn on at least one form that should collect entries.', 'koalaforms'),
                ),
                array(
                    'title' => __('Send a test submission', 'koalaforms'),
                    'copy' => __('A quick test confirms that the fields, notifications, and storage are working together.', 'koalaforms'),
                ),
                array(
                    'title' => __('Review the submission list', 'koalaforms'),
                    'copy' => __('Once the test entry appears, you’ll know the pipeline is working as expected.', 'koalaforms'),
                ),
            );
        } elseif (0 === $total_submissions) {
            $summary_title = __('Forms are ready for submissions', 'koalaforms');
            $summary_copy = __('The next step is to publish a test entry and confirm the flow.', 'koalaforms');
            $summary_label = __('Ready', 'koalaforms');
            $summary_tone = 'info';
            $focus_points = array(
                array(
                    'title' => __('Submit a test entry', 'koalaforms'),
                    'copy' => __('Use a live form to confirm the visitor experience from start to finish.', 'koalaforms'),
                ),
                array(
                    'title' => __('Review the capture details', 'koalaforms'),
                    'copy' => __('Open the submissions screen to verify the stored data and metadata.', 'koalaforms'),
                ),
                array(
                    'title' => __('Adjust the workflow if needed', 'koalaforms'),
                    'copy' => __('Tweak notifications, fields, or defaults before you start sending real traffic.', 'koalaforms'),
                ),
            );
        }

        $hero_points = array(
            array(
                'label' => __('Live forms', 'koalaforms'),
                'value' => number_format_i18n($active_forms),
            ),
            array(
                'label' => __('This week', 'koalaforms'),
                'value' => number_format_i18n($submissions_this_week),
            ),
            array(
                'label' => __('Last submission', 'koalaforms'),
                'value' => !empty($last_submission_date) ? $last_submission_date : __('No submissions yet', 'koalaforms'),
            ),
        );

        $recent_submissions_payload = array();
        if (!empty($recent_submissions)) {
            foreach ($recent_submissions as $submission) {
                $user_id = !empty($submission['user_id']) ? absint($submission['user_id']) : 0;
                $user = $user_id ? get_userdata($user_id) : null;

                $recent_submissions_payload[] = array(
                    'id' => !empty($submission['ID']) ? absint($submission['ID']) : 0,
                    'formName' => !empty($submission['form_name']) ? $submission['form_name'] : __('Unknown form', 'koalaforms'),
                    'userName' => !empty($user) ? $user->display_name : __('Guest', 'koalaforms'),
                    'browser' => !empty($submission[AppUtility::meta_key('browser')]) ? $submission[AppUtility::meta_key('browser')] : __('Unknown browser', 'koalaforms'),
                    'date' => !empty($submission['submission_date']) ? $submission['submission_date'] : '',
                );
            }
        }

        $summary_cards = array(
            array(
                'label' => __('Total forms', 'koalaforms'),
                'value' => number_format_i18n($total_forms),
                'note' => __('All forms in your workspace.', 'koalaforms'),
            ),
            array(
                'label' => __('Active forms', 'koalaforms'),
                'value' => number_format_i18n($active_forms),
                'note' => __('Forms currently accepting submissions.', 'koalaforms'),
            ),
            array(
                'label' => __('Needs attention', 'koalaforms'),
                'value' => number_format_i18n($forms_needing_attention),
                'note' => __('Drafts or inactive forms to review.', 'koalaforms'),
            ),
            array(
                'label' => __('Total entries', 'koalaforms'),
                'value' => number_format_i18n($total_submissions),
                'note' => __('All submissions captured so far.', 'koalaforms'),
            ),
        );

        $hero_actions = array(
            array('label' => __('Create New Form', 'koalaforms'), 'url' => admin_url('post-new.php?post_type=' . AppUtility::FORM_POST_TYPE), 'type' => 'primary'),
            array('label' => __('View All Forms', 'koalaforms'), 'url' => admin_url('edit.php?post_type=' . AppUtility::FORM_POST_TYPE), 'type' => 'secondary'),
            array('label' => __('Open Submissions', 'koalaforms'), 'url' => admin_url('edit.php?post_type=' . AppUtility::SUBMISSION_POST_TYPE), 'type' => 'secondary'),
            array('label' => __('Global Settings', 'koalaforms'), 'url' => admin_url('admin.php?page=koalaforms-settings'), 'type' => 'secondary'),
        );

        return array(
            'pageTitle' => __('Dashboard', 'koalaforms'),
            'pageSubtitle' => __('A polished home screen for building, publishing, and tracking forms.', 'koalaforms'),
            'introKicker' => __('KoalaForms Dashboard', 'koalaforms'),
            'heading' => __('Build, publish, and track forms from one place', 'koalaforms'),
            'intro' => __('Use this dashboard as your command center for forms, submissions, and setup decisions. It keeps the important signals visible, the next actions obvious, and the page calm enough to scan in a few seconds.', 'koalaforms'),
            'hero' => array(
                'badge' => $summary_label,
                'title' => $summary_title,
                'copy' => $summary_copy,
                'tone' => $summary_tone,
                'actions' => $hero_actions,
                'points' => $hero_points,
            ),
            'summaryCards' => $summary_cards,
            'statusCard' => array(
                'title' => __('What to do next', 'koalaforms'),
                'copy' => __('Follow these steps to keep your forms clean, active, and ready to capture submissions.', 'koalaforms'),
                'points' => $focus_points,
            ),
            'overview' => array(
                'title' => __('Latest activity at a glance', 'koalaforms'),
                'stats' => array(
                    array('label' => __('Forms with entries', 'koalaforms'), 'value' => number_format_i18n($forms_with_entries)),
                    array('label' => __('Submissions this week', 'koalaforms'), 'value' => number_format_i18n($submissions_this_week)),
                    array('label' => __('Last submission', 'koalaforms'), 'value' => !empty($last_submission_date) ? $last_submission_date : __('No submissions yet', 'koalaforms')),
                ),
                'spotlightTitle' => __('Recent activity is visible here.', 'koalaforms'),
                'spotlightCopy' => __('Recent forms and recent submissions sit side by side so you can spot trends without opening another screen.', 'koalaforms'),
            ),
            'recentForms' => $recent_forms_payload,
            'recentSubmissions' => $recent_submissions_payload,
            'sidebarNotes' => array(
                array('title' => __('Keep the workflow tight', 'koalaforms'), 'description' => __('Review inactive forms, ship test submissions, and refine the live ones first.', 'koalaforms')),
                array('title' => __('Use the summary cards', 'koalaforms'), 'description' => __('They show the numbers most admins want in under a second.', 'koalaforms')),
                array('title' => __('Treat the dashboard as a status board', 'koalaforms'), 'description' => __('It should help you decide what to do next, not just list data.', 'koalaforms')),
            ),
        );
    }

    function save_global_settings() {
        $post = wp_unslash($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified by check_admin_referer() in caller
        $settings = [];

        $settings['form_styling'] = $this->normalize_setting_value($post['form_styling'] ?? 'classic');
        $settings['email_template'] = $this->normalize_setting_value($post['email_template'] ?? 'classic');
        $settings['ip_logging'] = !empty($post['ip_logging']) ? 1 : 0;
        $settings['enable_gdpr_consent'] = !empty($post['enable_gdpr_consent']) ? 1 : 0;
        $settings['google_recaptcha_type'] = $this->normalize_setting_value($post['google_recaptcha_type'] ?? 'checkbox');
        $settings['google_recaptcha_site_key'] = sanitize_text_field($post['google_recaptcha_site_key'] ?? '');
        $settings['google_recaptcha_secret_key'] = sanitize_text_field($post['google_recaptcha_secret_key'] ?? '');
        $settings['hcaptcha_type'] = $this->normalize_setting_value($post['hcaptcha_type'] ?? 'checkbox');
        $settings['hcaptcha_site_key'] = sanitize_text_field($post['hcaptcha_site_key'] ?? '');
        $settings['hcaptcha_secret_key'] = sanitize_text_field($post['hcaptcha_secret_key'] ?? '');
        $settings['hcaptcha_threshold'] = $this->normalize_threshold($post['hcaptcha_threshold'] ?? '0.5');
        $settings['cloudfare_site_key'] = sanitize_text_field($post['cloudfare_site_key'] ?? '');
        $settings['cloudfare_secret_key'] = sanitize_text_field($post['cloudfare_secret_key'] ?? '');

        AppUtility::update_global_options($settings);
    }

    private function normalize_setting_value($value) {
        return sanitize_key(strtolower((string) $value));
    }

    private function normalize_threshold($value) {
        $value = is_string($value) ? trim($value) : $value;
        $value = is_numeric($value) ? (float) $value : 0.5;
        if ($value < 0) {
            $value = 0;
        }
        if ($value > 1) {
            $value = 1;
        }

        return (string) $value;
    }
    

    public function submissions(){
        include 'html/submissions_output.php';
    }

    public function tools(){
        include 'html/tools_output.php';
    }
}

new AdminPage();
