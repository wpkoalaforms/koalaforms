import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { store as editorStore } from '@wordpress/editor';
import { TextControl, ToggleControl, TextareaControl, SelectControl, Button, PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { PREFIX, TEXT_DOMAIN, LABELS } from '../utility';
import { openModal, closeModal, isModalOpen } from './form/modalUtils';
import GeneralSettingsModal from './form/generalSettingsModal';
import PostSubmissionModal from './form/postSubmissionModal';
import RestrictionSettingsModal from './form/restrictionSettingsModal';
import NotificationSettingsModal from './form/notificationSettingsModal.js';
import StageSettingsModal from './form/stageSettingsModal.js';

const MODAL_TYPES = {
    GENERAL_SETTINGS: 'general_settings',
    STAGE: 'stage',
    POST_SUBMISSION: 'post_submission',
    RESTRICTION_SETTINGS: 'restriction_settings',
    NOTIFICATION_SETTINGS: 'notification_settings',
};


const FORM_TYPES = [
    { label: 'Contact Form', value: 'contact' },
    { label: 'Registration Form', value: 'registration' },
];

const SUBMISSION_HANDLING_OPTIONS = [
    { label: 'Save to Database', value: 'database' },
    { label: 'Webhook Integration', value: 'webhook' },
];

const DEFAULT_SELECT_OPTION = [{ text: 'Please select', label: 'Please select', value: '' }];

const flattenBlocks = (blocks = []) => blocks.reduce((acc, block) => {
    acc.push(block);
    if (block.innerBlocks?.length) {
        acc.push(...flattenBlocks(block.innerBlocks));
    }
    return acc;
}, []);

const buildInputOptions = (blocks, allowedTypes) => {
    const blockOptions = flattenBlocks(blocks)
        .filter((block) => allowedTypes.includes(block.attributes?.type))
        .map((block) => ({
            value: block.attributes?.name || '',
            label: block.attributes?.inputLabel || '',
            text: block.attributes?.inputLabel || '',
        }))
        .filter((option) => option.value && option.label);

    return [...DEFAULT_SELECT_OPTION, ...blockOptions];
};

const FormSettingPanel = () => {
    const [openModals, setOpenModals] = useState(new Set());
    const { removeEditorPanel } = useDispatch(editorStore);
    const [meta, setMeta] = useEntityProp('postType', 'koalaforms-forms', 'meta');
    const formSettings = meta?.koalaforms_form_settings || {};

    const inputEmailOptions = useSelect((select) => {
        const blocks = select('core/block-editor').getBlocks();
        return buildInputOptions(blocks, ['Email']);
    }, []);
    const inputUsernameOptions = useSelect((select) => {
        const blocks = select('core/block-editor').getBlocks();
        return buildInputOptions(blocks, ['Email', 'Text']);
    }, []);

    const advancedSettings = [
        {
            key: MODAL_TYPES.STAGE,
            title: __('Stage', TEXT_DOMAIN),
        },
        {
            key: MODAL_TYPES.GENERAL_SETTINGS,
            title: __('Identity & Spam', TEXT_DOMAIN),
        },
        {
            key: MODAL_TYPES.POST_SUBMISSION,
            title: __('After Submit', TEXT_DOMAIN),
        },
        {
            key: MODAL_TYPES.NOTIFICATION_SETTINGS,
            title: __('Email Notifications', TEXT_DOMAIN),
        },
        {
            key: MODAL_TYPES.RESTRICTION_SETTINGS,
            title: __('Access & Limits', TEXT_DOMAIN),
        },
    ];

    const isRegistrationForm = formSettings.type === 'registration';
    const handleMetaChange = (field, value) => {
        setMeta({
            ...meta,
            koalaforms_form_settings: {
                ...formSettings,
                [field]: value,
            },
        });
    };

    useEffect(() => {
        removeEditorPanel('featured-image');
        removeEditorPanel('post-status');
    }, [removeEditorPanel]);


    return (
        <>
            <PluginDocumentSettingPanel name="form-settings" title={__('Form Settings', TEXT_DOMAIN)}>
                <PanelBody title={__('Basic Settings', TEXT_DOMAIN)} initialOpen={true}>
                    <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-cb-setting`}>
                        <SelectControl
                            label={__('Form Type', TEXT_DOMAIN)}
                            value={formSettings.type}
                            options={FORM_TYPES}
                            onChange={(value) => handleMetaChange('type', value)}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{LABELS.formTypeHelp}</span>
                    </div>

                    <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                        <SelectControl
                            label={__('Primary Email Field', TEXT_DOMAIN)}
                            value={formSettings.primary_email_field || ''}
                            options={inputEmailOptions}
                            onChange={(value) => handleMetaChange('primary_email_field', value)}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{LABELS.primaryFieldHelp}</span>
                    </div>

                    {isRegistrationForm && (
                        <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                            <SelectControl
                                label={__('Username Field', TEXT_DOMAIN)}
                                value={formSettings.username_field || ''}
                                options={inputUsernameOptions}
                                onChange={(value) => handleMetaChange('username_field', value)}
                            />
                            <span className={`${PREFIX}-field-help-text`}>{LABELS.usernameFieldHelp}</span>
                        </div>
                    )}

                    <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel ${PREFIX}-cb-setting`}>
                        <ToggleControl
                            label={__('Is Form Active?', TEXT_DOMAIN)}
                            checked={formSettings.is_active || false}
                            onChange={(value) => handleMetaChange('is_active', value)}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{LABELS.isActiveHelp}</span>
                    </div>

                    
                    <div className={`${PREFIX}-setting ${PREFIX}-general-setting-panel`}>
                        <TextareaControl
                            label={__('Inactive Form Message', TEXT_DOMAIN)}
                            value={formSettings.inactive_message || ''}
                            onChange={(value) => handleMetaChange('inactive_message', value)}
                            maxLength={500}
                            help={__('Displayed when the form is disabled.', TEXT_DOMAIN)}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{LABELS.inactiveMessageHelp}</span>
                    </div>
                </PanelBody>

                <PanelBody title={__('Advanced Settings', TEXT_DOMAIN)} initialOpen={false}>
                    <div className={`${PREFIX}-settings-list`}>
                        {advancedSettings.map((setting, index) => (
                            <button
                                key={setting.key}
                                className={`${PREFIX}-settings-list-row`}
                                onClick={() => openModal(setOpenModals, setting.key)}
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'space-between',
                                    width: '100%',
                                    padding: '10px 0',
                                    background: 'none',
                                    border: 'none',
                                    borderBottom: index < advancedSettings.length - 1 ? '1px solid #e0e0e0' : 'none',
                                    cursor: 'pointer',
                                    color: 'inherit',
                                    fontSize: '13px',
                                    textAlign: 'left',
                                }}
                            >
                                <span>{setting.title}</span>
                                <span style={{ color: '#757575', fontSize: '18px', lineHeight: 1 }}>›</span>
                            </button>
                        ))}
                    </div>
                </PanelBody>
            </PluginDocumentSettingPanel>

            {isModalOpen(openModals, MODAL_TYPES.STAGE) && (
                <StageSettingsModal
                    formSettings={formSettings}
                    handleMetaChange={handleMetaChange}
                    closeModal={() => closeModal(setOpenModals, MODAL_TYPES.STAGE)}
                />
            )}

            {isModalOpen(openModals, MODAL_TYPES.GENERAL_SETTINGS) && (
                <GeneralSettingsModal
                    formSettings={formSettings}
                    handleMetaChange={handleMetaChange}
                    closeModal={() => closeModal(setOpenModals, MODAL_TYPES.GENERAL_SETTINGS)}
                />
            )}
            {isModalOpen(openModals, MODAL_TYPES.POST_SUBMISSION) && (
                <PostSubmissionModal
                    formSettings={formSettings}
                    handleMetaChange={handleMetaChange}
                    closeModal={() => closeModal(setOpenModals, MODAL_TYPES.POST_SUBMISSION)}
                />
            )}
            {isModalOpen(openModals, MODAL_TYPES.RESTRICTION_SETTINGS) && (
                <RestrictionSettingsModal
                    formSettings={formSettings}
                    handleMetaChange={handleMetaChange}
                    closeModal={() => closeModal(setOpenModals, MODAL_TYPES.RESTRICTION_SETTINGS)}
                />
            )}
            {isModalOpen(openModals, MODAL_TYPES.NOTIFICATION_SETTINGS) && (
                <NotificationSettingsModal
                    formSettings={formSettings}
                    handleMetaChange={handleMetaChange}
                    closeModal={() => closeModal(setOpenModals, MODAL_TYPES.NOTIFICATION_SETTINGS)}
                />
            )}
        </>
    );
};

registerPlugin('form-settings', {
    render: FormSettingPanel,
});
