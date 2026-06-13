import { Modal, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef } from '@wordpress/element';
import { PREFIX, TEXT_DOMAIN, LABELS } from '../../utility';
import { initializeEditors, cleanupEditors } from './editorUtils';

const PostSubmissionModal = ({ formSettings, handleMetaChange, closeModal }) => {
    const editorRef = useRef(null);

    useEffect(() => {
        initializeEditors('post_submission', formSettings, handleMetaChange, {}, () => {});
        return () => {
            cleanupEditors('post_submission');
        };
    }, []);

    const getSuccessMessage = () => {
        return formSettings.success_message;
    };

    return (
        <Modal
            title={__('After Submit', TEXT_DOMAIN)}
            onRequestClose={closeModal}
            className={`${PREFIX}-post-submission-modal ${PREFIX}-settings-modal`}
        >
            <div className={`${PREFIX}-modal-content`}>
                <p className={`${PREFIX}-modal-help-text`}>
                    {__('Set the redirect and success message shown after submit.', TEXT_DOMAIN)}
                </p>
                <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                    <div className={`${PREFIX}-modal-section-header`}>
                        <h4 className={`${PREFIX}-modal-section-title`}>{__('Redirect', TEXT_DOMAIN)}</h4>
                        <p className={`${PREFIX}-modal-section-description`}>{__('Send users to another page after success.', TEXT_DOMAIN)}</p>
                    </div>
                    <TextControl
                        label={__('Redirect URL', TEXT_DOMAIN)}
                        value={formSettings.redirection || ''}
                        onChange={(value) => handleMetaChange('redirection', value)}
                    />
                    <span className={`${PREFIX}-field-help-text`}>{LABELS.redirectionHelp}</span>
                </div>

                <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                    <div className={`${PREFIX}-modal-section-header`}>
                        <h4 className={`${PREFIX}-modal-section-title`}>{__('Success message', TEXT_DOMAIN)}</h4>
                        <p className={`${PREFIX}-modal-section-description`}>{__('Display a confirmation message after submission.', TEXT_DOMAIN)}</p>
                    </div>
                    <label className={`${PREFIX}-modal-field-label`}>
                        {__('Success Message', TEXT_DOMAIN)}
                    </label>
                    <div ref={editorRef}>
                        <textarea
                            id="success-message-editor"
                            name="success-message-editor"
                            defaultValue={getSuccessMessage()}
                            onChange={(e) => handleMetaChange('success_message', e.target.value)}
                            placeholder={__('Write the success message shown after submit...', TEXT_DOMAIN)}
                            className={`${PREFIX}-modal-editor-textarea`}
                        />
                    </div>
                    <p className={`${PREFIX}-modal-help-text`}>
                        {__('HTML is supported.', TEXT_DOMAIN)}
                    </p>
                </div>

                <div className={`${PREFIX}-modal-actions`}>
                    <Button
                        variant="tertiary"
                        onClick={closeModal}
                    >
                        {__('Close', TEXT_DOMAIN)}
                    </Button>
                </div>
            </div>
        </Modal>
    );
};

export default PostSubmissionModal;
