<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <!-- Form Page Top -->
    <h1 class="kf-inline-head wp-heading-inline">Forms</h1>
    <a href="javascript:void(0)" class="kf-page-title-action kf-open-modal page-title-action" data-id="kf-create-form-modal">Add New Form</a>
    <!-- Form Page Top ends here -->
    
    <!--- Form that needs to be shown as pop up -->
    <div id="response-message"></div>

 <div id="kf-create-form-modal" class="kf-modal-view" style="display:none" >
        <div class="kf-modal-overlay kf-modal-overlay-fade-in close-popup" data-id="kf-create-form-modal">
        </div>
        <div class="popup-content kf-modal-wrap kf-modal-sm kf-modal-out">
            <div class="kf-modal-body kf-p-4">
                <div class="kf-create-form-modal-title">
                    <h3 class="kf-modal-title kf-m-0 kf-pb-2">
                       Quick Create Form
                    </h3>
                    <p>Creates a new form with all the essential settings.</p>
                    <span class="kf-modal-close kf-close-popup kf-position-absolute kf-cursor" data-id="kf-create-form-modal">&times;</span>
                </div>
                <div class="kf-modal-content-wrap kf-mt-4">
                    <form method="POST" id="kf-create-form">
                        <?php wp_nonce_field('koalaforms_create_form'); ?>
                        <input type="hidden" name="action" value="koalaforms_create_form" />
                        <div>
                        <label for="form_name">Name of your form</label>
                        <input type="text" name="form_name" class="regular-text" required />
                        </div>
                        <div class="kf-mt-3"><?php submit_button('Create Form'); ?></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Form ends here -->
</div>