<?php

namespace KoalaForms;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders the "Submission Details" metabox on the submission edit screen.
 *
 * Owns three concerns:
 *   - Stage pipeline  (prepare_stage_data)
 *   - Layout view     (prepare_layout_data + submission_output.php)
 *   - Layout builder  (submission_layout_modal.php)
 *
 * Future: render() will delegate to render_view() vs render_edit() once
 * inline field editing is added. Save will go through AdminAjax, not save_post,
 * because the submission CPT blocks standard post saving.
 */
class SubmissionMetaBox {

    private $post;
    private $submission;
    private $form_id;
    private $form;

    public function __construct( $post ) {
        $this->post    = $post;
        $this->form_id = AppUtility::get_meta( $post->ID, 'form_id', true );
        $this->form    = Form::create_instance()->get_form( $this->form_id );

        $submission_model = Submission::create_instance();
        $submission_model->mark_submission_as_read( $post->ID );
        $this->submission = $submission_model->get_submission( $post->ID );
    }

    // Entry point called by AdminLoader::submission_meta_box_callback().
    public function render() {
        $stage_data    = $this->prepare_stage_data();
        $current_stage = $stage_data['current_stage'];
        $stage_list    = $stage_data['stage_list'];
        $current_index = $stage_data['current_index'];
        // submission_entry_stage_output.php expects the raw stage list in $stage.
        $stage         = $stage_list;

        $submission = $this->submission;

        include( 'admin/html/submission_entry_stage_output.php' );

        // Pro add-ons can override the submission detail render entirely.
        $handled = apply_filters( 'koalaforms_submission_detail_render', false, $this->submission, $this->form_id, $this->form );
        if ( ! $handled ) {
            include( 'admin/html/submission_output.php' );
        }

        do_action( 'koalaforms_submission_detail_after_render', $this->submission, $this->form_id, $this->form );
    }

    // Builds the stage list, resolves the current stage index, and localizes
    // the stage data for admin.js (pipeline UI + stage-change modal).
    private function prepare_stage_data() {
        $stage = ( $this->form !== null && ! empty( $this->form->settings['stage'] ) && is_array( $this->form->settings['stage'] ) )
            ? $this->form->settings['stage']
            : array();

        $current_stage = AppUtility::get_meta( $this->post->ID, 'submission_stage', true );

        $stage_list = array();
        foreach ( $stage as $s ) {
            $label = is_string( $s ) ? trim( $s ) : ( isset( $s['label'] ) ? trim( $s['label'] ) : '' );
            if ( $label !== '' ) $stage_list[] = $label;
        }
        $current_index = array_search( $current_stage, $stage_list, true );

        wp_localize_script( 'koalaforms-admin-js', 'koalaformsStageData', array(
            'confirmedIdx' => ( $current_index !== false ) ? (int) $current_index : -1,
            'movingTo'     => __( 'Moving to', 'koalaforms' ),
            'saving'       => __( 'Saving...', 'koalaforms' ),
        ) );

        return compact( 'stage_list', 'current_stage', 'current_index' );
    }

}
