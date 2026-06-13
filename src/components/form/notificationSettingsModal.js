import { Modal, ToggleControl, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';
import { PREFIX, TEXT_DOMAIN, LABELS } from '../../utility';
import { initializeEditors, cleanupEditors } from './editorUtils';

const NotificationSettingsModal = ({ formSettings, handleMetaChange, closeModal }) => {
    const DEFAULT_ADMIN_EMAIL_BODY = `Hello Admin, <br>
                                      <p>You have received a new submission. Here are the details:</p>
                                    {{REGISTRATION_DATA}}`;

    useEffect(() => {
        initializeEditors('notification_settings', formSettings, handleMetaChange, {}, () => {});
        return () => {
            cleanupEditors('notification_settings');
        };
    }, [formSettings.admin_notification, formSettings.auto_reply]);

    return (
        <Modal
            title={__('Notifications', TEXT_DOMAIN)}
            onRequestClose={closeModal}
            className={`${PREFIX}-notification-settings-modal ${PREFIX}-settings-modal`}
        >
            <div className={`${PREFIX}-modal-content`}>
                <p className={`${PREFIX}-modal-help-text`}>
                    {__('Set admin alerts and optional auto-replies.', TEXT_DOMAIN)}
                </p>
                <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                    <div className={`${PREFIX}-modal-section-header`}>
                        <h4 className={`${PREFIX}-modal-section-title`}>{__('Admin notification', TEXT_DOMAIN)}</h4>
                        <p className={`${PREFIX}-modal-section-description`}>{__('Send an email when a new submission arrives.', TEXT_DOMAIN)}</p>
                    </div>
                    <ToggleControl
                        label={__('Enable Admin Notification', TEXT_DOMAIN)}
                        checked={formSettings.admin_notification || false}
                        onChange={(value) => handleMetaChange('admin_notification', value)}
                    />
                    <span className={`${PREFIX}-field-help-text`}>Send an email for each submission.</span>
                </div>

                {formSettings.admin_notification && (
                    <div className={`${PREFIX}-modal-section-nested`}>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                            <div className={`${PREFIX}-modal-section-header`}>
                                <h4 className={`${PREFIX}-modal-section-title`}>{__('Recipients', TEXT_DOMAIN)}</h4>
                            </div>
                            <TextControl
                                label={__('Admin Email', TEXT_DOMAIN)}
                                value={formSettings.admin_email || ''}
                                onChange={(value) => handleMetaChange('admin_email', value)}
                            />
                            <span className={`${PREFIX}-field-help-text`}>Separate multiple emails with commas. Leave blank to use the site admin email.</span>
                        </div>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                            <div className={`${PREFIX}-modal-section-header`}>
                                <h4 className={`${PREFIX}-modal-section-title`}>{__('Sender details', TEXT_DOMAIN)}</h4>
                            </div>
                            <TextControl
                                label={__('From Email', TEXT_DOMAIN)}
                                value={formSettings.admin_from_email || ''}
                                onChange={(value) => handleMetaChange('admin_from_email', value)}
                            />
                            <span className={`${PREFIX}-field-help-text`}>Use a domain email address when possible.</span>
                        </div>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                            <div className={`${PREFIX}-modal-section-header`}>
                                <h4 className={`${PREFIX}-modal-section-title`}>{__('Message', TEXT_DOMAIN)}</h4>
                            </div>
                            <TextControl
                                label={__('From Name', TEXT_DOMAIN)}
                                value={formSettings.admin_from_name || ''}
                                onChange={(value) => handleMetaChange('admin_from_name', value)}
                            />
                            <span className={`${PREFIX}-field-help-text`}>Email sender name.</span>
                        </div>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                            <TextControl
                                label={__('Subject', TEXT_DOMAIN)}
                                value={formSettings.admin_email_subject || ''}
                                onChange={(value) => handleMetaChange('admin_email_subject', value)}
                            />
                            <span className={`${PREFIX}-field-help-text`}>Subject line for the notification email.</span>
                        </div>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                            <label className={`${PREFIX}-modal-field-label`}>
                                {__('Email Body', TEXT_DOMAIN)}
                            </label>
                            <textarea
                                id="admin-email-editor"
                                defaultValue={formSettings.admin_email_body || DEFAULT_ADMIN_EMAIL_BODY}
                                onChange={(e) => handleMetaChange('admin_email_body', e.target.value)}
                                className={`${PREFIX}-modal-editor-textarea ${PREFIX}-d-none`}
                            />
                            <span className={`${PREFIX}-field-help-text`}>{LABELS.adminEmailBodyHelp}</span>
                        </div>
                    </div>
                )}

                <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                    <div className={`${PREFIX}-modal-section-header`}>
                        <h4 className={`${PREFIX}-modal-section-title`}>{__('Auto reply', TEXT_DOMAIN)}</h4>
                        <p className={`${PREFIX}-modal-section-description`}>{__('Send a confirmation email to the submitter.', TEXT_DOMAIN)}</p>
                    </div>
                    <ToggleControl
                        label={__('Auto Reply the User', TEXT_DOMAIN)}
                        checked={formSettings.auto_reply || false}
                        onChange={(value) => handleMetaChange('auto_reply', value)}
                    />
                    <span className={`${PREFIX}-field-help-text`}>{LABELS.autoResponderHelp}</span>
                </div>

                {formSettings.auto_reply && (
                    <div className={`${PREFIX}-modal-section-nested`}>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                            <div className={`${PREFIX}-modal-section-header`}>
                                <h4 className={`${PREFIX}-modal-section-title`}>{__('Subject', TEXT_DOMAIN)}</h4>
                            </div>
                            <TextControl
                                label={__('Email Subject', TEXT_DOMAIN)}
                                value={formSettings.auto_reply_subject || ''}
                                onChange={(value) => handleMetaChange('auto_reply_subject', value)}
                            />
                            <span className={`${PREFIX}-field-help-text`}>{LABELS.arSubjectHelp}</span>
                        </div>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                            <div className={`${PREFIX}-modal-section-header`}>
                                <h4 className={`${PREFIX}-modal-section-title`}>{__('Body', TEXT_DOMAIN)}</h4>
                            </div>
                            <label className={`${PREFIX}-modal-field-label`}>
                                {__('Email Body', TEXT_DOMAIN)}
                            </label>
                            <textarea
                                id="auto-reply-editor"
                                defaultValue={formSettings.auto_reply_body || ''}
                                onChange={(e) => handleMetaChange('auto_reply_body', e.target.value)}
                                className={`${PREFIX}-modal-editor-textarea ${PREFIX}-d-none`}
                            />
                            <span className={`${PREFIX}-field-help-text`}>{LABELS.arBodyHelp}</span>
                        </div>
                    </div>
                )}

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

export default NotificationSettingsModal;
