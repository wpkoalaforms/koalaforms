<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

class Email
{   
    private $from= null;
    private $from_name= null;
    private static $instance = null;
    
    private function __construct()
    {   
       add_filter('koalaforms_post_submission', array($this,'post_submission_notifications'),10,3); 
    }
    
    public static function create_instance()
    {   
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function post_submission_notifications($response, $form_id, $sub_id){

        // check for any errors, and return 
        if(!empty($response['errors']) || !empty($response['form_errors']))
            return $response; 

        $form_model = Form::create_instance();
        $form       = $form_model->get_form($form_id);

        $submission_model = Submission::create_instance();
        $submission       = $submission_model->get_submission($sub_id);

        $this->auto_reply_user($form, $submission);
        $this->notify_admin($form, $submission);
        return $response;
    }

    //Send notification to admin
    public function notify_admin($form, $submission){
        if(empty($form->settings['admin_notification']))
            return;

        // Fix: fallback subject so email never sends with a blank subject line
        $subject = !empty($form->settings['admin_email_subject'])
            ? $form->settings['admin_email_subject']
            : sprintf(
                /* translators: %s: site name */
                __('New Form Submission - %s', 'koalaforms'),
                get_bloginfo('name')
            );

        // Fix: fallback body so email never sends blank
        $message = !empty($form->settings['admin_email_body'])
            ? $form->settings['admin_email_body']
            : '{{REGISTRATION_DATA}}';

        $registration_html = '';

        foreach($submission['form_fields'] as $item){
            $attrs       = $item['field']['attrs'];
            $field_value = isset($item['value']) ? $item['value'] : '';

            if(is_array($field_value)){
                $field_value = implode(', ', $field_value);
            }

            $field_value = esc_html($field_value);
            $message     = str_replace('{{' . $attrs['inputLabel'] . '}}', $field_value, $message);
            $registration_html .= '<div><strong>' . esc_html($attrs['inputLabel']) . ':</strong> ' . $field_value . '</div><br>';
        }

        // Fix: was = instead of .= which wiped all field data above
        if (!empty($submission['unique_id'])){
            $registration_html .= '<div><strong>Unique ID:</strong> ' . esc_html($submission['unique_id']) . '</div><br>';
            $message            = str_replace('{{UNIQUE_ID}}', esc_html($submission['unique_id']), $message);
        }

        $message = str_replace('{{REGISTRATION_DATA}}', $registration_html, $message);

        $to = !empty($form->settings['admin_email']) ? $form->settings['admin_email'] : get_option('admin_email');

        // Validate to address before sending
        if(empty($to) || !is_email($to)){
            wp_trigger_error( __FUNCTION__, 'KoalaForms notify_admin: invalid or missing admin email address.', E_USER_NOTICE );
            return;
        }

        $message = do_shortcode(wpautop($message));

        // Fix: validate from email before applying filter
        if(!empty($form->settings['admin_from_email']) && is_email($form->settings['admin_from_email'])){
            $this->from = $form->settings['admin_from_email'];
            add_filter('wp_mail_from', array($this, 'set_email_from'));
        }
        if(!empty($form->settings['admin_from_name'])){
            $this->from_name = $form->settings['admin_from_name'];
            add_filter('wp_mail_from_name', array($this, 'set_email_from_name'));
        }

        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        $sent = wp_mail($to, $subject, $message);
        remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));

        // Fix: always clean up filters regardless of send result
        $this->from = null;
        remove_filter('wp_mail_from', array($this, 'set_email_from'));
        $this->from_name = null;
        remove_filter('wp_mail_from_name', array($this, 'set_email_from_name'));

        if(!$sent){
            wp_trigger_error( __FUNCTION__, 'KoalaForms notify_admin: wp_mail failed to send to ' . $to, E_USER_NOTICE );
        }
    }
    
    //Send auto reply message to user
    public function auto_reply_user($form, $submission){
        if(!isset($form->settings['auto_reply']) || empty($form->settings['auto_reply'])){
            return;
        }

        $primary_email_field_name = !empty($form->settings['primary_email_field'])
            ? $form->settings['primary_email_field']
            : '';

        // Fix: branded fallback subject instead of generic 'Submission Completed'
        $subject = !empty($form->settings['auto_reply_subject'])
            ? $form->settings['auto_reply_subject']
            : sprintf(
                /* translators: %s: site name */
                __('Thank you for contacting %s', 'koalaforms'),
                get_bloginfo('name')
            );

        // Fix: fallback body so email never sends blank
        $message = !empty($form->settings['auto_reply_body'])
            ? $form->settings['auto_reply_body']
            : __('Thank you for your submission. We will get back to you shortly.', 'koalaforms');

        $to = null;

        foreach($submission['form_fields'] as $item){
            $attrs       = $item['field']['attrs'];
            $field_value = isset($item['value']) ? $item['value'] : '';

            if(is_array($field_value)){
                $field_value = implode(', ', $field_value);
            }

            // Fix: match primary email field to extract recipient address
            if(!empty($primary_email_field_name)
                && isset($attrs['name'])
                && $attrs['name'] === $primary_email_field_name
            ){
                $to = sanitize_email($field_value);
            }

            $message = str_replace('{{' . $attrs['inputLabel'] . '}}', esc_html($field_value), $message);
        }

        if(!empty($submission['unique_id'])){
            $message = str_replace('{{UNIQUE_ID}}', esc_html($submission['unique_id']), $message);
        }

        // Fix: fall back to logged-in user email if no email field matched
        if(empty($to) || !is_email($to)){
            if(is_user_logged_in()){
                $current_user = wp_get_current_user();
                $to = $current_user->user_email;
            } else {
                wp_trigger_error( __FUNCTION__, 'KoalaForms auto_reply_user: could not determine recipient email — no primary email field matched and user is not logged in.', E_USER_NOTICE );
                return;
            }
        }

        $message = do_shortcode(wpautop($message));

        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        $sent = wp_mail($to, $subject, $message);
        remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));

        if(!$sent){
            wp_trigger_error( __FUNCTION__, 'KoalaForms auto_reply_user: wp_mail failed to send to ' . $to, E_USER_NOTICE );
        }
    }

    public function set_html_content_type($content_type) {
        return 'text/html';
    }

    public function set_email_from($from){
        if(!empty($this->from)){
            return $this->from;
        }
        return $from;
    }
    
    public function set_email_from_name($from_name){
        if(!empty($this->from_name)){
            return $this->from_name;
        }
        return $from_name;
    }
}