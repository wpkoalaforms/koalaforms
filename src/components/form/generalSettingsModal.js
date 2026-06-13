import { Modal, SelectControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { PREFIX, TEXT_DOMAIN, LABELS } from '../../utility';

const GeneralSettingsModal = ({ formSettings, handleMetaChange, closeModal }) => {
    const hasCaptchaOptions = typeof koalaformsConfig !== 'undefined' && koalaformsConfig?.captchaOptions?.length > 0;

    return (
        <Modal
            title={__('Identity & Spam', TEXT_DOMAIN)}
            onRequestClose={closeModal}
            className={`${PREFIX}-general-settings-modal ${PREFIX}-settings-modal`}
        >
            <div className={`${PREFIX}-modal-content`}>
                <p className={`${PREFIX}-modal-help-text`}>
                    {__('Set captcha and submission ID behavior.', TEXT_DOMAIN)}
                </p>
                {hasCaptchaOptions && (
                    <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                        <div className={`${PREFIX}-modal-section-header`}>
                            <h4 className={`${PREFIX}-modal-section-title`}>{__('Captcha', TEXT_DOMAIN)}</h4>
                            <p className={`${PREFIX}-modal-section-description`}>{__('Choose the anti-spam provider for this form.', TEXT_DOMAIN)}</p>
                        </div>
                        <SelectControl
                            label={__('Provider', TEXT_DOMAIN)}
                            value={formSettings.captcha}
                            options={koalaformsConfig.captchaOptions}
                            onChange={(value) => handleMetaChange('captcha', value)}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{LABELS.captchaHelp}</span>
                    </div>
                )}

                <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-modal-section`}>
                    <div className={`${PREFIX}-modal-section-header`}>
                        <h4 className={`${PREFIX}-modal-section-title`}>{__('Submission ID', TEXT_DOMAIN)}</h4>
                        <p className={`${PREFIX}-modal-section-description`}>{__('Enable or tune the generated entry identifier.', TEXT_DOMAIN)}</p>
                    </div>
                    <ToggleControl
                        label={__('Unique ID', TEXT_DOMAIN)}
                        checked={formSettings.unique_id || false}
                        onChange={(value) => handleMetaChange('unique_id', value)}
                    />
                    <span className={`${PREFIX}-field-help-text`}>{LABELS.uniqueIdHelp}</span>
                </div>

                {formSettings.unique_id && (
                    <div className={`${PREFIX}-modal-section-nested`}>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                            <TextControl
                                label={__('Current index', TEXT_DOMAIN)}
                                type="number"
                                value={formSettings.unique_id_index}
                                onChange={(value) => handleMetaChange('unique_id_index', parseInt(value) || 0)}
                                help={__('The starting number for new submissions. This updates automatically — only change it if you want to reset or jump the count.', TEXT_DOMAIN)}
                            />
                        </div>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                            <TextControl
                                label={__('Prefix', TEXT_DOMAIN)}
                                value={formSettings.unique_id_prefix}
                                onChange={(value) => handleMetaChange('unique_id_prefix', value)}
                                help={__('Optional text added before the number. For example, a prefix of "DOC" produces IDs like DOC101, DOC102…', TEXT_DOMAIN)}
                            />
                        </div>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                            <TextControl
                                label={__('Number Padding', TEXT_DOMAIN)}
                                type="number"
                                value={formSettings.unique_id_padding}
                                onChange={(value) => handleMetaChange('unique_id_padding', parseInt(value))}
                                help={__('Minimum digits in the number. Shorter numbers are padded with leading zeros. A padding of 4 turns "5" into "0005".', TEXT_DOMAIN)}
                            />
                        </div>
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                            <TextControl
                                label={__('Number Offset', TEXT_DOMAIN)}
                                type="number"
                                value={formSettings.unique_id_offset || 0}
                                onChange={(value) => handleMetaChange('unique_id_offset', parseInt(value) || 0)}
                                help={__('How much the number increases with each new submission. Set to 1 to count up one by one, or use a higher value to leave gaps between IDs.', TEXT_DOMAIN)}
                            />
                        </div>
                    </div>
                )}

                <div className={`${PREFIX}-modal-actions`}>
                    <Button variant="tertiary" onClick={closeModal}>
                        {__('Close', TEXT_DOMAIN)}
                    </Button>
                </div>
            </div>
        </Modal>
    );
};

export default GeneralSettingsModal;
