<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class AdminAjax{

    public function __construct() {
        add_action('wp_ajax_koalaforms_create_form', array($this, 'create_form'));

        // Actions to load the form schema for rendering.
        add_action('wp_ajax_koalaforms_load_form', array($this, 'load_form_schema'));
        add_action('wp_ajax_nopriv_koalaforms_load_form', array($this, 'load_form_schema'));

        // Actions to handle form submission for both registered and public users. 
        add_action('wp_ajax_koalaforms_submit_form', array($this, 'submit_form'));
        add_action('wp_ajax_nopriv_koalaforms_submit_form', array($this, 'submit_form'));

        // Actions to fetch country list
        add_action('wp_ajax_koalaforms_get_countries', array($this, 'get_countries'));
        add_action('wp_ajax_nopriv_koalaforms_get_countries', array($this, 'get_countries'));

        // Actions to fetch state list
        add_action('wp_ajax_koalaforms_get_states', array($this, 'get_states'));
        add_action('wp_ajax_nopriv_koalaforms_get_states', array($this, 'get_states'));

    }

    public function create_form(){
        if (!current_user_can('manage_options')) {
            echo json_encode(['status' => 'error', 'message' => __('You are not allowed to create forms.', 'koalaforms')]);
            wp_die();
        }

        // Verify the nonce passed in the form
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'koalaforms_create_form')) {
            echo json_encode(['status' => 'error', 'message' => __('Invalid Security nonce.', 'koalaforms')]);
            wp_die();
        }

        $form_name = '';
        // Sanitize the form name
        if(isset($_POST['form_name'])){
            $form_name = sanitize_text_field(wp_unslash($_POST['form_name']));
        }
        
        // Validate form name
        if (empty($form_name)) {
            echo json_encode(['status' => 'error', 'message' => __('Form name cannot be empty.', 'koalaforms')]);
            wp_die();
        }

        // Insert the post
        $post_id = wp_insert_post([
            'post_title'  => $form_name,
            'post_type'   => AppUtility::FORM_POST_TYPE, 
            'post_status' => 'publish',
            'post_content' => AppUtility::SAMPLE_FORM_CONTENT,
            'meta_input'    => array(  // Insert custom post meta data
                                'form_settings' => array(
                                    'is_active'   => true
                                )
                            )
        ]);

        if ($post_id) {
            // Redirect to the edit screen of the new post
            echo json_encode([
                'status' => 'success',
                'message' => __('Form created successfully.', 'koalaforms'),
                'redirect_url' => admin_url('post.php?action=edit&post=' . $post_id)
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => __('Failed to create a new form.', 'koalaforms')]);
        }
        wp_die();
    }

    // Being called from frontend to get the form schema and load.
    public function load_form_schema(){
        check_ajax_referer('koalaforms_load_form_nonce', 'nonce');
        
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        if (!empty($form_id)) {
            $form_model = Form::create_instance();
            $form = $form_model->get_form($form_id);

            if(empty($form)){
                wp_send_json_error(['error' => __('Form does not exist.', 'koalaforms')]);
            }

            $submit_nonce    = wp_create_nonce('koalaforms_submission_nonce_'.$form_id);
            $captcha_details = isset($form->settings['captcha_details']) ? $form->settings['captcha_details'] : null;
            $response        =  ['elements' => $form->_field_config, 
                                    'submissionNonce'=>$submit_nonce, 
                                    'captcha_details'=> $captcha_details,
                                    'username_field' => $form->settings['username_field'],
                                    'primary_email_field' => $form->settings['primary_email_field'],
                                    'user_logged_in' => is_user_logged_in(),
                                    'type' => $form->settings['type']
                                ];

            if($form->settings['type'] == 'registration'){
                if(is_user_logged_in()){
                    $response['user_email'] = wp_get_current_user()->user_email;
                }
            }
            wp_send_json_success($response);
        } 
        wp_send_json_error(['error' => __('Form does not exist.', 'koalaforms')]);
    }

    // Validated and submit the form 
    public function submit_form(){
        $response             = array();
        $form_id = isset( $_POST['form_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
        check_ajax_referer('koalaforms_submission_nonce_'.$form_id, 'nonce');
        
        $form_model = Form::create_instance();
        $form       = $form_model->get_form($form_id);
        
        
        if (empty($form)) {
            wp_send_json_success(['errors' => array('form' => __('Form does not exist.', 'koalaforms'))]);
        }

        if (empty($form->_field_config)) {
            wp_send_json_success(['errors' => array('form' => __('No fields are available.', 'koalaforms'))]);
        }

        $form_renderer = FormRender::get_instance();
        $result = $form_renderer->form_submission_allowed($form_id);

        if (!$result['show']) {
            $response['form_errors'] = array($result['error']);
            wp_send_json_success($response);
        }

        $current_step   = isset( $_POST['current_step'] ) ? sanitize_text_field( wp_unslash( $_POST['current_step'] ) ) : '';
        if (empty($current_step)) {
            wp_send_json_success(['errors' => array('form' => __('Invalid Step.', 'koalaforms'))]);
        }
        
        
        $step_config          = $form_model->step_config($form->_field_config, $current_step);
        $validation_result    = $form_model->validate_form($_POST, $step_config, $form_id);
        if(count($validation_result['errors']) > 0){
            $response['errors'] = $validation_result['errors'];
            wp_send_json_success($response);
        }
        
       
        $is_last_step         = isset( $_POST['last_step'] ) ? sanitize_text_field( wp_unslash( $_POST['last_step'] ) ) : '';
        // Submission is inserted into the database only if it's a last step.
        if ($is_last_step == 'true'){

            if(!empty($form->settings['captcha'])){
                $recaptcha = isset($_POST['recaptcha']) ? sanitize_text_field( wp_unslash( $_POST['recaptcha'] ) ) : null;
                if(empty($form_model->verify_captcha($recaptcha, $form_id))){
                    $response['errors'] = array('captcha' => __('Invalid Captcha Value', 'koalaforms'));
                    wp_send_json_success($response);
                }
            }

            $sub_data = $form_model->posted_data($_POST, $form_id);
            $submission_model = Submission::create_instance();
            $sub = $submission_model->upsert_submission($sub_data,$form_id);

            if (is_wp_error($sub))
                $response['errors'] = $sub->get_error_message();
            else{
                // Save the default stage to the submission and record it in the history.
                $default_stage = $form->settings['default_stage'] ?? '';
                if (!empty($default_stage)) {
                    $default_stage = sanitize_text_field($default_stage);
                    AppUtility::update_meta($sub, 'submission_stage', $default_stage);
                    $initial_history = array(
                        array(
                            'stage'      => $default_stage,
                            'changed_at' => current_time('mysql'),
                            'changed_by' => get_current_user_id(),
                            'note'       => '',
                        ),
                    );
                    AppUtility::update_meta($sub, 'submission_stage_history', wp_json_encode($initial_history));
                }

                $response['submission_id']  = $sub;
                
                if(!empty(esc_url_raw($form->settings['redirection'])))
                    $response['redirection'] = esc_url_raw($form->settings['redirection']);

                if(!empty($form->settings['success_message'])){
                    $response['success_message'] = wp_kses_post($form->settings['success_message']);
                }

                $response = apply_filters('koalaforms_post_submission', $response,$form_id, $sub);
            }
        }
        wp_send_json_success($response);
    }

    public function get_countries() {
        check_ajax_referer('koalaforms_load_form_nonce', 'nonce');
        $countries = array(
            '' => __('Select Country', 'koalaforms'),
            'AF' => __('Afghanistan', 'koalaforms'),
            'AX' => __('Aland Islands', 'koalaforms'),
            'AL' => __('Albania', 'koalaforms'),
            'DZ' => __('Algeria', 'koalaforms'),
            'AS' => __('American Samoa', 'koalaforms'),
            'AD' => __('Andorra', 'koalaforms'),
            'AO' => __('Angola', 'koalaforms'),
            'AI' => __('Anguilla', 'koalaforms'),
            'AQ' => __('Antarctica', 'koalaforms'),
            'AG' => __('Antigua and Barbuda', 'koalaforms'),
            'AR' => __('Argentina', 'koalaforms'),
            'AM' => __('Armenia', 'koalaforms'),
            'AW' => __('Aruba', 'koalaforms'),
            'AU' => __('Australia', 'koalaforms'),
            'AT' => __('Austria', 'koalaforms'),
            'AZ' => __('Azerbaijan', 'koalaforms'),
            'BS' => __('Bahamas', 'koalaforms'),
            'BH' => __('Bahrain', 'koalaforms'),
            'BD' => __('Bangladesh', 'koalaforms'),
            'BB' => __('Barbados', 'koalaforms'),
            'BY' => __('Belarus', 'koalaforms'),
            'BE' => __('Belgium', 'koalaforms'),
            'PW' => __('Belau', 'koalaforms'),
            'BZ' => __('Belize', 'koalaforms'),
            'BJ' => __('Benin', 'koalaforms'),
            'BM' => __('Bermuda', 'koalaforms'),
            'BT' => __('Bhutan', 'koalaforms'),
            'BO' => __('Bolivia', 'koalaforms'),
            'BQ' => __('Bonaire, Saint Eustatius and Saba', 'koalaforms'),
            'BA' => __('Bosnia and Herzegovina', 'koalaforms'),
            'BW' => __('Botswana', 'koalaforms'),
            'BV' => __('Bouvet Island', 'koalaforms'),
            'BR' => __('Brazil', 'koalaforms'),
            'IO' => __('British Indian Ocean Territory', 'koalaforms'),
            'VG' => __('British Virgin Islands', 'koalaforms'),
            'BN' => __('Brunei', 'koalaforms'),
            'BG' => __('Bulgaria', 'koalaforms'),
            'BF' => __('Burkina Faso', 'koalaforms'),
            'BI' => __('Burundi', 'koalaforms'),
            'KH' => __('Cambodia', 'koalaforms'),
            'CM' => __('Cameroon', 'koalaforms'),
            'CA' => __('Canada', 'koalaforms'),
            'CV' => __('Cape Verde', 'koalaforms'),
            'KY' => __('Cayman Islands', 'koalaforms'),
            'CF' => __('Central African Republic', 'koalaforms'),
            'TD' => __('Chad', 'koalaforms'),
            'CL' => __('Chile', 'koalaforms'),
            'CN' => __('China', 'koalaforms'),
            'CX' => __('Christmas Island', 'koalaforms'),
            'CC' => __('Cocos (Keeling) Islands', 'koalaforms'),
            'CO' => __('Colombia', 'koalaforms'),
            'KM' => __('Comoros', 'koalaforms'),
            'CG' => __('Congo (Brazzaville)', 'koalaforms'),
            'CD' => __('Congo (Kinshasa)', 'koalaforms'),
            'CK' => __('Cook Islands', 'koalaforms'),
            'CR' => __('Costa Rica', 'koalaforms'),
            'HR' => __('Croatia', 'koalaforms'),
            'CU' => __('Cuba', 'koalaforms'),
            'CW' => __('Curaçao', 'koalaforms'),
            'CY' => __('Cyprus', 'koalaforms'),
            'CZ' => __('Czech Republic', 'koalaforms'),
            'DK' => __('Denmark', 'koalaforms'),
            'DJ' => __('Djibouti', 'koalaforms'),
            'DM' => __('Dominica', 'koalaforms'),
            'DO' => __('Dominican Republic', 'koalaforms'),
            'EC' => __('Ecuador', 'koalaforms'),
            'EG' => __('Egypt', 'koalaforms'),
            'SV' => __('El Salvador', 'koalaforms'),
            'GQ' => __('Equatorial Guinea', 'koalaforms'),
            'ER' => __('Eritrea', 'koalaforms'),
            'EE' => __('Estonia', 'koalaforms'),
            'ET' => __('Ethiopia', 'koalaforms'),
            'FK' => __('Falkland Islands', 'koalaforms'),
            'FO' => __('Faroe Islands', 'koalaforms'),
            'FJ' => __('Fiji', 'koalaforms'),
            'FI' => __('Finland', 'koalaforms'),
            'FR' => __('France', 'koalaforms'),
            'GF' => __('French Guiana', 'koalaforms'),
            'PF' => __('French Polynesia', 'koalaforms'),
            'TF' => __('French Southern Territories', 'koalaforms'),
            'GA' => __('Gabon', 'koalaforms'),
            'GM' => __('Gambia', 'koalaforms'),
            'GE' => __('Georgia', 'koalaforms'),
            'DE' => __('Germany', 'koalaforms'),
            'GH' => __('Ghana', 'koalaforms'),
            'GI' => __('Gibraltar', 'koalaforms'),
            'GR' => __('Greece', 'koalaforms'),
            'GL' => __('Greenland', 'koalaforms'),
            'GD' => __('Grenada', 'koalaforms'),
            'GP' => __('Guadeloupe', 'koalaforms'),
            'GU' => __('Guam', 'koalaforms'),
            'GT' => __('Guatemala', 'koalaforms'),
            'GG' => __('Guernsey', 'koalaforms'),
            'GN' => __('Guinea', 'koalaforms'),
            'GW' => __('Guinea-Bissau', 'koalaforms'),
            'GY' => __('Guyana', 'koalaforms'),
            'HT' => __('Haiti', 'koalaforms'),
            'HM' => __('Heard Island and McDonald Islands', 'koalaforms'),
            'HN' => __('Honduras', 'koalaforms'),
            'HK' => __('Hong Kong', 'koalaforms'),
            'HU' => __('Hungary', 'koalaforms'),
            'IS' => __('Iceland', 'koalaforms'),
            'IN' => __('India', 'koalaforms'),
            'ID' => __('Indonesia', 'koalaforms'),
            'IR' => __('Iran', 'koalaforms'),
            'IQ' => __('Iraq', 'koalaforms'),
            'IE' => __('Ireland', 'koalaforms'),
            'IM' => __('Isle of Man', 'koalaforms'),
            'IL' => __('Israel', 'koalaforms'),
            'IT' => __('Italy', 'koalaforms'),
            'CI' => __('Ivory Coast', 'koalaforms'),
            'JM' => __('Jamaica', 'koalaforms'),
            'JP' => __('Japan', 'koalaforms'),
            'JE' => __('Jersey', 'koalaforms'),
            'JO' => __('Jordan', 'koalaforms'),
            'KZ' => __('Kazakhstan', 'koalaforms'),
            'KE' => __('Kenya', 'koalaforms'),
            'KI' => __('Kiribati', 'koalaforms'),
            'KW' => __('Kuwait', 'koalaforms'),
            'KG' => __('Kyrgyzstan', 'koalaforms'),
            'LA' => __('Laos', 'koalaforms'),
            'LV' => __('Latvia', 'koalaforms'),
            'LB' => __('Lebanon', 'koalaforms'),
            'LS' => __('Lesotho', 'koalaforms'),
            'LR' => __('Liberia', 'koalaforms'),
            'LY' => __('Libya', 'koalaforms'),
            'LI' => __('Liechtenstein', 'koalaforms'),
            'LT' => __('Lithuania', 'koalaforms'),
            'LU' => __('Luxembourg', 'koalaforms'),
            'MO' => __('Macao S.A.R., China', 'koalaforms'),
            'MK' => __('Macedonia', 'koalaforms'),
            'MG' => __('Madagascar', 'koalaforms'),
            'MW' => __('Malawi', 'koalaforms'),
            'MY' => __('Malaysia', 'koalaforms'),
            'MV' => __('Maldives', 'koalaforms'),
            'ML' => __('Mali', 'koalaforms'),
            'MT' => __('Malta', 'koalaforms'),
            'MH' => __('Marshall Islands', 'koalaforms'),
            'MQ' => __('Martinique', 'koalaforms'),
            'MR' => __('Mauritania', 'koalaforms'),
            'MU' => __('Mauritius', 'koalaforms'),
            'YT' => __('Mayotte', 'koalaforms'),
            'MX' => __('Mexico', 'koalaforms'),
            'FM' => __('Micronesia', 'koalaforms'),
            'MD' => __('Moldova', 'koalaforms'),
            'MC' => __('Monaco', 'koalaforms'),
            'MN' => __('Mongolia', 'koalaforms'),
            'ME' => __('Montenegro', 'koalaforms'),
            'MS' => __('Montserrat', 'koalaforms'),
            'MA' => __('Morocco', 'koalaforms'),
            'MZ' => __('Mozambique', 'koalaforms'),
            'MM' => __('Myanmar', 'koalaforms'),
            'NA' => __('Namibia', 'koalaforms'),
            'NR' => __('Nauru', 'koalaforms'),
            'NP' => __('Nepal', 'koalaforms'),
            'NL' => __('Netherlands', 'koalaforms'),
            'NC' => __('New Caledonia', 'koalaforms'),
            'NZ' => __('New Zealand', 'koalaforms'),
            'NI' => __('Nicaragua', 'koalaforms'),
            'NE' => __('Niger', 'koalaforms'),
            'NG' => __('Nigeria', 'koalaforms'),
            'NU' => __('Niue', 'koalaforms'),
            'NF' => __('Norfolk Island', 'koalaforms'),
            'MP' => __('Northern Mariana Islands', 'koalaforms'),
            'KP' => __('North Korea', 'koalaforms'),
            'NO' => __('Norway', 'koalaforms'),
            'OM' => __('Oman', 'koalaforms'),
            'PK' => __('Pakistan', 'koalaforms'),
            'PS' => __('Palestinian Territory', 'koalaforms'),
            'PA' => __('Panama', 'koalaforms'),
            'PG' => __('Papua New Guinea', 'koalaforms'),
            'PY' => __('Paraguay', 'koalaforms'),
            'PE' => __('Peru', 'koalaforms'),
            'PH' => __('Philippines', 'koalaforms'),
            'PN' => __('Pitcairn', 'koalaforms'),
            'PL' => __('Poland', 'koalaforms'),
            'PT' => __('Portugal', 'koalaforms'),
            'PR' => __('Puerto Rico', 'koalaforms'),
            'QA' => __('Qatar', 'koalaforms'),
            'RE' => __('Reunion', 'koalaforms'),
            'RO' => __('Romania', 'koalaforms'),
            'RU' => __('Russia', 'koalaforms'),
            'RW' => __('Rwanda', 'koalaforms'),
            'BL' => __('Saint Barth&eacute;lemy', 'koalaforms'),
            'SH' => __('Saint Helena', 'koalaforms'),
            'KN' => __('Saint Kitts and Nevis', 'koalaforms'),
            'LC' => __('Saint Lucia', 'koalaforms'),
            'MF' => __('Saint Martin (French part)', 'koalaforms'),
            'SX' => __('Saint Martin (Dutch part)', 'koalaforms'),
            'PM' => __('Saint Pierre and Miquelon', 'koalaforms'),
            'VC' => __('Saint Vincent and the Grenadines', 'koalaforms'),
            'SM' => __('San Marino', 'koalaforms'),
            'ST' => __('S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'koalaforms'),
            'SA' => __('Saudi Arabia', 'koalaforms'),
            'SN' => __('Senegal', 'koalaforms'),
            'RS' => __('Serbia', 'koalaforms'),
            'SC' => __('Seychelles', 'koalaforms'),
            'SL' => __('Sierra Leone', 'koalaforms'),
            'SG' => __('Singapore', 'koalaforms'),
            'SK' => __('Slovakia', 'koalaforms'),
            'SI' => __('Slovenia', 'koalaforms'),
            'SB' => __('Solomon Islands', 'koalaforms'),
            'SO' => __('Somalia', 'koalaforms'),
            'ZA' => __('South Africa', 'koalaforms'),
            'GS' => __('South Georgia/Sandwich Islands', 'koalaforms'),
            'KR' => __('South Korea', 'koalaforms'),
            'SS' => __('South Sudan', 'koalaforms'),
            'ES' => __('Spain', 'koalaforms'),
            'LK' => __('Sri Lanka', 'koalaforms'),
            'SD' => __('Sudan', 'koalaforms'),
            'SR' => __('Suriname', 'koalaforms'),
            'SJ' => __('Svalbard and Jan Mayen', 'koalaforms'),
            'SZ' => __('Swaziland', 'koalaforms'),
            'SE' => __('Sweden', 'koalaforms'),
            'CH' => __('Switzerland', 'koalaforms'),
            'SY' => __('Syria', 'koalaforms'),
            'TW' => __('Taiwan', 'koalaforms'),
            'TJ' => __('Tajikistan', 'koalaforms'),
            'TZ' => __('Tanzania', 'koalaforms'),
            'TH' => __('Thailand', 'koalaforms'),
            'TL' => __('Timor-Leste', 'koalaforms'),
            'TG' => __('Togo', 'koalaforms'),
            'TK' => __('Tokelau', 'koalaforms'),
            'TO' => __('Tonga', 'koalaforms'),
            'TT' => __('Trinidad and Tobago', 'koalaforms'),
            'TN' => __('Tunisia', 'koalaforms'),
            'TR' => __('Turkey', 'koalaforms'),
            'TM' => __('Turkmenistan', 'koalaforms'),
            'TC' => __('Turks and Caicos Islands', 'koalaforms'),
            'TV' => __('Tuvalu', 'koalaforms'),
            'UG' => __('Uganda', 'koalaforms'),
            'UA' => __('Ukraine', 'koalaforms'),
            'AE' => __('United Arab Emirates', 'koalaforms'),
            'GB' => __('United Kingdom (UK)', 'koalaforms'),
            'US' => __('United States (US)', 'koalaforms'),
            'UM' => __('United States (US) Minor Outlying Islands', 'koalaforms'),
            'VI' => __('United States (US) Virgin Islands', 'koalaforms'),
            'UY' => __('Uruguay', 'koalaforms'),
            'UZ' => __('Uzbekistan', 'koalaforms'),
            'VU' => __('Vanuatu', 'koalaforms'),
            'VA' => __('Vatican', 'koalaforms'),
            'VE' => __('Venezuela', 'koalaforms'),
            'VN' => __('Vietnam', 'koalaforms'),
            'WF' => __('Wallis and Futuna', 'koalaforms'),
            'EH' => __('Western Sahara', 'koalaforms'),
            'WS' => __('Samoa', 'koalaforms'),
            'YE' => __('Yemen', 'koalaforms'),
            'ZM' => __('Zambia', 'koalaforms'),
            'ZW' => __('Zimbabwe', 'koalaforms'),
        );
        $decoded_countries = array();
        foreach($countries as $key=>$val){
            $encoded_val = html_entity_decode($val);
            array_push($decoded_countries, ["label" => $encoded_val, "value" => $key]);
        }
        return wp_send_json_success(['countries' => $decoded_countries]);
    }

    public function get_states() {
        check_ajax_referer('koalaforms_load_form_nonce', 'nonce');

        $states= array();
        $code = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
        if ( ! preg_match( '/^[A-Z]{2}$/', $code ) ) {
            wp_send_json_error( array( 'error' => 'Invalid country code.' ) );
        }

        $file_path = __DIR__."/states/".$code.".php";
        if (file_exists($file_path)){
            include $file_path;
        }
        
        $decoded_states = array();
        if(isset($states[$code])){
            foreach($states[$code] as $key=>$val){
                $encoded_val = html_entity_decode($val);
                array_push($decoded_states, ["label" => $encoded_val, "value" => $key]);
            }
        }
        return wp_send_json_success(['states' => $decoded_states]);
    }
}

    