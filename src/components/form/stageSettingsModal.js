import { Modal, TextControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PREFIX, TEXT_DOMAIN } from '../../utility';

const StageSettingsModal = ({ formSettings, handleMetaChange, closeModal }) => {
    const [stages, setStages] = useState(() =>
        Array.isArray(formSettings.stage) ? formSettings.stage : []
    );

    const [defaultStage, setDefaultStage] = useState(formSettings.default_stage);

    // Only sets the stages list itself. Callers that know the default stage is
    // affected (renamed or removed) are responsible for updating it themselves —
    // this avoids re-deriving "is the default still present?" from state that
    // may already be stale by the time this runs.
    const updateStages = (updated) => {
        setStages(updated);
        handleMetaChange('stage', updated);
    };

    const addStage = () => {
        updateStages([...stages, '']);
    };

    const updateStage = (index, value) => {
        const updated = stages.map((s, i) => (i === index ? value : s));
        // If the renamed stage was the default, the default's name moves with it.
        if (stages[index] === defaultStage) {
            setDefaultStage(value);
            handleMetaChange('default_stage', value);
        }
        updateStages(updated);
    };

    const removeStage = (index) => {
        const isDefault = stages[index] === defaultStage;
        if (isDefault && !window.confirm(
            __('This is the default stage. New submissions will have no stage assigned until you set a new default. Remove it anyway?', TEXT_DOMAIN)
        )) {
            return;
        }
        if (isDefault) {
            setDefaultStage('');
            handleMetaChange('default_stage', '');
        }
        updateStages(stages.filter((_, i) => i !== index));
    };

    const setDefault = (value) => {
        setDefaultStage(value);
        handleMetaChange('default_stage', value);
    };

    return (
        <Modal
            title={__('Stages', TEXT_DOMAIN)}
            onRequestClose={closeModal}
            className={`${PREFIX}-stage-settings-modal ${PREFIX}-settings-modal`}
        >
            <div className={`${PREFIX}-modal-content`}>
                <p className={`${PREFIX}-modal-help-text`}>
                    {__('Stages let you track where each submission is in your workflow — for example "Submitted", "Under Review", "Approved", or "Rejected". You can move a submission between stages from its detail page.', TEXT_DOMAIN)}
                </p>

                <p style={{ fontSize: '12px', color: '#757575', marginTop: '-8px', marginBottom: '16px' }}>
                    {__('ℹ Select the radio button to set a stage as the default. The default is automatically assigned when a new submission arrives. Removing a stage does not affect submissions already in it.', TEXT_DOMAIN)}
                </p>

                {/* Column headers */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 80px 64px', gap: '8px', paddingBottom: '6px', borderBottom: '1px solid #e2e4e7', marginBottom: '8px' }}>
                    <span style={{ fontSize: '11px', fontWeight: 600, color: '#757575', textTransform: 'uppercase', letterSpacing: '0.5px' }}>{__('Stage name', TEXT_DOMAIN)}</span>
                    <span style={{ fontSize: '11px', fontWeight: 600, color: '#757575', textTransform: 'uppercase', letterSpacing: '0.5px', textAlign: 'center' }}>{__('Default', TEXT_DOMAIN)}</span>
                    <span style={{ fontSize: '11px', fontWeight: 600, color: '#757575', textTransform: 'uppercase', letterSpacing: '0.5px', textAlign: 'center' }}>{__('Action', TEXT_DOMAIN)}</span>
                </div>

                <div className={`${PREFIX}-status-list`}>
                    {stages.map((stage, index) => (
                        <div
                            key={index}
                            className={`${PREFIX}-status-row`}
                            style={{ display: 'grid', gridTemplateColumns: '1fr 80px 64px', gap: '8px', alignItems: 'center', marginBottom: '8px' }}
                        >
                            <TextControl
                                value={stage}
                                placeholder={__('Stage name…', TEXT_DOMAIN)}
                                onChange={(value) => updateStage(index, value)}
                            />

                            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                                <input
                                    type="radio"
                                    name="default_stage"
                                    checked={defaultStage === stage}
                                    onChange={() => setDefault(stage)}
                                    style={{ width: '16px', height: '16px', accentColor: '#007cba', cursor: 'pointer' }}
                                />
                            </div>

                            <div style={{ display: 'flex', justifyContent: 'center' }}>
                                <Button
                                    variant="tertiary"
                                    isDestructive
                                    size="small"
                                    onClick={() => removeStage(index)}
                                >
                                    {__('Remove', TEXT_DOMAIN)}
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>

                <div className={`${PREFIX}-modal-actions`}>
                    <Button variant="secondary" onClick={addStage}>
                        {__('+ Add Stage', TEXT_DOMAIN)}
                    </Button>
                    <Button variant="tertiary" onClick={closeModal}>
                        {__('Close', TEXT_DOMAIN)}
                    </Button>
                </div>
            </div>
        </Modal>
    );
};

export default StageSettingsModal;
