import { Modal, TextControl, ToggleControl, Button, CheckboxControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { PREFIX, TEXT_DOMAIN, LABELS } from '../../utility';

const RestrictionSettingsModal = ({ formSettings, handleMetaChange, closeModal }) => {
    return (
        <Modal
            title={__('Restrictions', TEXT_DOMAIN)}
            onRequestClose={closeModal}
            className={`${PREFIX}-restriction-settings-modal ${PREFIX}-settings-modal`}
        >
            <div className={`${PREFIX}-modal-content`}>
                <p className={`${PREFIX}-modal-help-text`}>
                    {__('Set access rules and submission limits.', TEXT_DOMAIN)}
                </p>

                <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                    <div className={`${PREFIX}-modal-section-header`}>
                        <h4 className={`${PREFIX}-modal-section-title`}>{__('Submission cap', TEXT_DOMAIN)}</h4>
                        <p className={`${PREFIX}-modal-section-description`}>{__('Limit how many total submissions this form can accept.', TEXT_DOMAIN)}</p>
                    </div>
                    <TextControl
                        label={__('Total Submission Limit', TEXT_DOMAIN)}
                        value={formSettings.total_submission_limit || ''}
                        onChange={(value) => handleMetaChange('total_submission_limit', value)}
                    />
                    <span className={`${PREFIX}-field-help-text`}>{LABELS.totalSubmissionLimitHelp}</span>
                </div>

                <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                    <div className={`${PREFIX}-modal-section-header`}>
                        <h4 className={`${PREFIX}-modal-section-title`}>{__('Login access', TEXT_DOMAIN)}</h4>
                        <p className={`${PREFIX}-modal-section-description`}>{__('Restrict submissions to signed-in users.', TEXT_DOMAIN)}</p>
                    </div>
                    <ToggleControl
                        label={__('Allow Only Logged-in Users to Submit', TEXT_DOMAIN)}
                        checked={formSettings.logged_in_user_restriction || false}
                        onChange={(value) => handleMetaChange('logged_in_user_restriction', value)}
                    />
                    <span className={`${PREFIX}-field-help-text`}>{LABELS.loggedInUserRestrictionHelp}</span>
                </div>

                
                {formSettings.logged_in_user_restriction && (
                    <div className={`${PREFIX}-modal-section`}>

                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                            <div className={`${PREFIX}-modal-section-header`}>
                                <h4 className={`${PREFIX}-modal-section-title`}>{__('Per-user cap', TEXT_DOMAIN)}</h4>
                            </div>
                            <TextControl
                                label={__('Submission Limit Per User', TEXT_DOMAIN)}
                                value={formSettings.submission_limit_per_user || ''}
                                onChange={(value) => handleMetaChange('submission_limit_per_user', value)}
                            />
                            <span className={`${PREFIX}-field-help-text`}>{LABELS.submissionLimitPerUserHelp}</span>
                        </div>

                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                            <div className={`${PREFIX}-modal-section-header`}>
                                <h4 className={`${PREFIX}-modal-section-title`}>{__('Role access', TEXT_DOMAIN)}</h4>
                            </div>
                            <label className="components-base-control__label">
                                {__('Allowed User Roles', TEXT_DOMAIN)}
                            </label>

                            <div className="kf-role-checkbox-group">
                                {Object.entries(koalaforms_ajax_object.roles).map(([role, label]) => {
                                    const rolesArray = formSettings.allowed_user_roles 
                                        ? formSettings.allowed_user_roles.split(';').filter(r => r !== '') 
                                        : [];
                                    
                                    return (
                                        <CheckboxControl
                                            key={role}
                                            label={label}
                                            checked={rolesArray.includes(role)}
                                            onChange={(isChecked) => {
                                                let updatedRoles = formSettings.allowed_user_roles 
                                                    ? formSettings.allowed_user_roles.split(';').filter(r => r !== '')
                                                    : [];

                                                if (isChecked) {
                                                    if (!updatedRoles.includes(role)) {
                                                        updatedRoles.push(role);
                                                    }
                                                } else {
                                                    updatedRoles = updatedRoles.filter((r) => r !== role);
                                                }
                                                
                                                handleMetaChange('allowed_user_roles', updatedRoles.join(';'));
                                            }}
                                        />
                                    );
                                })}
                            </div>

                            <span className={`${PREFIX}-field-help-text`}>Only selected roles can submit. Leave empty to allow all users.</span>
                        </div>

                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                            <div className={`${PREFIX}-modal-section-header`}>
                                <h4 className={`${PREFIX}-modal-section-title`}>{__('Denied message', TEXT_DOMAIN)}</h4>
                            </div>
                            <TextareaControl
                                label={__('Access Denied Message', TEXT_DOMAIN)}
                                value={formSettings.access_denied_msg || 'You are not authorised to access this form.'}
                                onChange={(value) => handleMetaChange('access_denied_msg', value)}
                            />
                            <span className={`${PREFIX}-field-help-text`}>Shown when a visitor does not meet the access rules.</span>
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

export default RestrictionSettingsModal;
