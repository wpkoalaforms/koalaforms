<?php
namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @description: Class to support email template related operations throughout the plugin.
 */
class EmailTemplate{

    public function __construct() {
       add_action('add_meta_boxes', array($this, 'meta_boxes'));
       add_action('edit_form_after_title', array($this, 'meta_box_before_editor_callback'));
       //add_action('edit_form_top', array($this, 'render_meta_box_before_editor'));

       $this->register_template_meta();
    }

    public function register_template_meta(){
        // Register Recipient Addresses meta field
        register_post_meta(AppUtility::EMAIL_POST_TYPE, '_recipient_addresses', [
            'type'         => 'string',
            'single'       => true,
            'show_in_rest' => true, // Expose to REST API
        ]);

        // Register Email Subject meta field
        register_post_meta(AppUtility::EMAIL_POST_TYPE, '_email_subject', [
            'type'         => 'string',
            'single'       => true,
            'show_in_rest' => true, // Expose to REST API
        ]);
    }

    public function meta_boxes(){
        add_meta_box(
            'email_template_meta', // Unique ID
            __('Email Template Details', 'koalaforms'), // Box title
            array($this, 'email_template_meta_callback'), // Callback function
            AppUtility::EMAIL_POST_TYPE, // Post type
            'normal', // Context (normal, side, or advanced)
            'default' // Priority
        );
    }

    public function meta_box_before_editor_callback($post){
        do_meta_boxes(null, 'normal', $post);
    }

    public function render_meta_box_before_editor(){
        // Render the meta box content here
        echo '<div id="custom-meta-box">';
        echo '<h2>Custom Meta Box</h2>';
        echo '<p>Meta box content goes here.</p>';
        // Add your custom fields or HTML
        echo '</div>';
    }

    public function email_template_meta_callback($post){
        include 'admin/html/email_meta_output.php';
    }
}