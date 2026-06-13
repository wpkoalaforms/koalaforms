<?php
namespace KoalaForms;

class Captcha{


    // Returns dropdown options to be displayed in form settings.
    public static function active_captcha_options(){
        $options = AppUtility::get_global_options();
        $captcha_options = array();

        if(!empty($options['google_recaptcha_type']) && !empty($options['google_recaptcha_site_key']) && !empty($options['google_recaptcha_secret_key'])){
           array_push($captcha_options, array('label' => 'Google reCAPTCHA', 'value' => 'google_recaptcha'));
        }

        if(!empty($options['hcaptcha_type']) && !empty($options['hcaptcha_site_key']) && !empty($options['hcaptcha_secret_key'])){
            array_push($captcha_options, array('label' => 'HCaptcha', 'value' => 'hcaptcha'));
        }

        if(!empty($options['cloudfare_site_key']) && !empty($options['cloudfare_secret_key'])){
            array_push($captcha_options, array('label' => 'Cloudflare Turnstile', 'value' => 'cloudfare'));
        }

        if(count($captcha_options) > 0){
            $captcha_options = array_merge(array(array('label' => 'None', 'value' => 'none')), $captcha_options);
        }
        return $captcha_options;
    }

    // Returns site key to be used by the UI for captcha
    public static function get_form_captcha_details($type){
        if(empty($type)) return null;


        $details    = array('vendor' => $type);
        $options    = AppUtility::get_global_options();

        if($type == 'google_recaptcha'){
            $details['type']        = $options['google_recaptcha_type'] ?? '';
            $details['key'] = $options['google_recaptcha_site_key'] ?? '';
        }
        return $details;
    }

}