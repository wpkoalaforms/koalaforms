<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class AppUtility
 *
 * This class contains constant values used globally across the application.
 * Constants in this class are meant to store configuration settings, URLs, timeouts,
 * or any other values that should remain unchanged during the execution of the program.
 * Access these constants directly using the ClassName::CONSTANT_NAME syntax.
 *
 * Example:
 *   - AppUtility::API_URL
 *   - AppUtility::TIMEOUT
 *
 * Usage of constants helps maintain consistency across the codebase and simplifies
 * future updates to configuration values, as changes need to be made in only one place.
 */
use DateTime;
use Exception;

class AppUtility {
    /*
     * PREFIX CONVENTIONS — change here only, everything else follows.
     *
     * PREFIX        ('kf')           — Gutenberg block names (kf/text, kf/phone …).
     *                                  Stored in wp_posts.post_content. Do NOT change
     *                                  without a post_content migration.
     *
     * PLUGIN_PREFIX ('koalaforms_')  — Everything else that touches WordPress:
     *                                    • WP options        get_option / update_option
     *                                    • AJAX action names wp_ajax_*
     *                                    • Nonce actions
     *                                    • Script / style handles
     *                                    • Post meta keys    via AppUtility::meta_key()
     *                                  Changing this constant is enough for code; a
     *                                  wp_options + wp_postmeta DB migration is also needed.
     *
     * CSS classes use 'kf-' (e.g. .kf-modal) — UI only, no WP registry impact.
     */
    const APP_NAME              = 'WP Flexi Forms';
    const VERSION               = '1.0.0';
    const DEFAULT_LANGUAGE      = 'en';
    const PREFIX                = 'kf';
    const PLUGIN_PREFIX         = 'koalaforms_';
    const FORM_POST_TYPE        = 'koalaforms-forms';
    const EMAIL_POST_TYPE       = 'koalaforms-emails';
    const SUBMISSION_POST_TYPE  = 'koalaforms-subs';

    const SAMPLE_FORM_CONTENT   =   '<!-- wp:kf/step {"inputLabel":"Step_1","name":"Step_1"} -->'.
                                    '<div></div>'.
                                    '<!-- /wp:kf/step -->';

    const FORM_PROPS            = array(
                                        'description' => array(
                                            'type' => 'string',
                                        ),
                                        'is_active' => array(
                                            'type' => 'boolean',
                                        ),
                                        'logged_in_user_restriction'=> array(
                                            'type' => 'boolean'
                                        ),
                                        'stage'=> array(
                                            'type' => 'array'
                                        ),
                                        'inactive_message' => array(
                                            'type' => 'string',
                                        ),
                                        'submission_limit_per_user' => array(
                                            'type' => 'string',
                                        ),
                                        'total_submission_limit' => array(
                                            'type' => 'string'
                                        ),
                                        'allow_logged_in_users'=>array(
                                            'type' => 'string'
                                        ),
                                        'admin_emails' => array(
                                            'type' => 'string'
                                        ),
                                        'captcha' => array(
                                            'type' => 'string'
                                        ),
                                        'admin_notification' => array(
                                            'type' => 'boolean'
                                        ),
                                        'redirection' => array(
                                            'type' => 'string'
                                        ),
                                        'success_message' => array(
                                            'type'=> 'string',
                                        ),
                                        'auto_reply' => array(
                                            'type' => 'boolean'
                                        ),
                                        'auto_reply_subject' => array(
                                            'type' => 'string'
                                        ),
                                        'auto_reply_body' => array(
                                            'type' => 'string'
                                        ),
                                        'primary_email_field' => array(
                                            'type' =>  'string'
                                        ),
                                        'type' => array(
                                            'type' =>  'string'
                                        ),
                                        'username_field'=> array(
                                            'type' => 'string'
                                        ),
                                        'unique_id'=> array(
                                            'type' => 'boolean'
                                        ),
                                        'unique_id_prefix'=> array(
                                            'type' => 'string'
                                        ),
                                        'unique_id_padding'=> array(
                                            'type' => 'number'
                                        ),
                                        'unique_id_offset'=> array(
                                            'type' => 'number'
                                        ),
                                        'unique_id_index'=> array(
                                            'type' => 'number'
                                        ),
                                        'admin_email' => array(
                                            'type' => 'string'
                                        ),
                                        'admin_from_email' => array(
                                            'type' => 'string'
                                        ),
                                        'admin_from_name' => array(
                                            'type' => 'string'
                                        ),
                                        'admin_email_subject' => array(
                                            'type' => 'string'
                                        ),
                                        'admin_email_body' => array(
                                            'type' => 'string'
                                        ),
                                        'allowed_user_roles' => array(
                                            'type' => 'string'
                                        ),
                                        'access_denied_msg' => array(
                                            'type' => 'string'
                                        ),
                                        'default_stage' => array(
                                            'type' => 'string'
                                        )

                                    );


    /**
     * Field block type slugs (without the kf/ prefix, title-cased to match JS).
     * This is the single source of truth — JS reads this via koalaformsConfig.inputBlockTypes.
     * Add new field block types here only. Do NOT duplicate this list in blockHelper.js.
     */
    const INPUT_BLOCK_TYPES = [
        'Text', 'Date', 'Number', 'Checkbox', 'Radio', 'MultiSelect',
        'Email', 'URL', 'Select', 'Textarea', 'Disclosure', 'Address',
    ];

    public static function allowedBlocks(){
        $blocks = array_map('strtolower', self::INPUT_BLOCK_TYPES);
        $blocks[] = 'step';
        $prefixedBlocks = [];
        foreach($blocks as $blockName){
            array_push($prefixedBlocks, self::PREFIX.'/'.$blockName);
        }
        $prefixedBlocks= array_merge($prefixedBlocks, ['core/paragraph', 'core/heading', 'core/columns', 'core/details', 
                            'core/image', 'core/video', 'core/table','core/spacer']);
        return $prefixedBlocks;
    }

    /**
     * Get the value of a constant by its name.
     *
     * @param string $key The name of the constant.
     * @return string|null The value of the constant, or null if it doesn't exist.
     */
    public static function pluginKey($key) {
        return self::PLUGIN_PREFIX.$key;
    }

    public static function meta_key($key) {
        return self::PLUGIN_PREFIX . ltrim($key, '_');
    }

    public static function get_meta($post_id, $key, $single = false) {
        return get_post_meta($post_id, self::meta_key($key), $single);
    }

    public static function update_meta($post_id, $key, $value) {
        return update_post_meta($post_id, self::meta_key($key), $value);
    }

    public static function delete_meta($post_id, $key) {
        return delete_post_meta($post_id, self::meta_key($key));
    }

    public static function add_meta($post_id, $key, $value) {
        return add_post_meta($post_id, self::meta_key($key), $value);
    }

    /* Validates post save conditions to ensure safe and correct saving of post data.
    *
    * This function performs multiple checks to ensure that the post save process
    * is legitimate. It checks for autosave, post revisions, verifies the post type,
    * and ensures the current user has permission to edit the post.
    *
    * @param int $post_id The ID of the post being saved.
    * @param string $post_type The expected post type for validation.
    * @return bool Returns true if all checks pass, false otherwise.
    */
   public static function validatePostSave($post_id, $post_type, $action = 'edit_post') {
       // Skip autosave to avoid saving data when WordPress is just automatically saving the post.
       if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
           return false; // Return false to stop the save process.
       }
   
       // Skip saving during post revisions to avoid saving data during auto-generated revisions.
       if (wp_is_post_revision($post_id)) {
           return false; // Return false to stop saving.
       }
   
       // Ensure the post type matches the expected post type to prevent unintended data saving.
       if (get_post_type($post_id) !== $post_type) {
           return false; // Return false if the post type does not match.
       }
   
       // Check if the current user has permission to edit the post.
       // If the user doesn't have permission, don't allow the post to be saved.
       if (!current_user_can($action, $post_id)) {
           return false; // Return false if the user does not have permission.
       }
   
       // If all checks pass, return true to allow the save process to continue.
       return true;
   }

    public static function text_length($value) {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }
        return strlen($value);
    } 

    public static function get_global_options(){
        return get_option(self::PLUGIN_PREFIX.'settings', []);
    }

    public static function update_global_options($new_settings){
        $existing_settings = self::get_global_options();
        update_option(self::PLUGIN_PREFIX.'settings', wp_parse_args($new_settings, $existing_settings));
    }

    /**
     * Calculate age from a date string.
     *
     * @param string $dob Date of birth in 'Y-m-d' format.
     * @return int|null Age in years, or null if invalid date.
     */
    public static function calculate_age_from_date($dob) {
        if (empty($dob)) {
            return null;
        }

        try {
            $birthDate = new DateTime($dob);
            $today = new DateTime('today');
            $age = $birthDate->diff($today)->y;
            return $age;
        } catch (Exception $e) {
            // Invalid date format
            return null;
        }
    }

    /**
     * Check if a given date is in the past (before today).
     *
     * @param string $date Date string (preferably in 'Y-m-d' format).
     * @return bool|null Returns true if date is in the past, false if not, or null if invalid.
     */
    public static function is_past_date($date) {
        if (empty($date)) {
            return null;
        }

        try {
            $inputDate = new DateTime($date);
            $today = new DateTime('today');

            return $inputDate < $today;
        } catch (Exception $e) {
            // Invalid date format
            return null;
        }
    }

}