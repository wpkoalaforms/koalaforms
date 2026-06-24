import { PREFIX, TEXT_DOMAIN, LABELS, sanitizeInputLabel } from '../utility';
import { TextControl, PanelBody, BaseControl, SelectControl, ToggleControl, CheckboxControl, Tooltip } from '@wordpress/components';
import OptionList from '../components/optionsList';
import { useEntityProp } from '@wordpress/core-data';
import { useState, useEffect, useRef } from '@wordpress/element';

const { __ } = wp.i18n;

const FieldLabel = ({ label, help }) => (
    <div className={`${PREFIX}-field-header`}>
        <span className="components-base-control__label">{label}</span>
        {help && (
            <Tooltip text={help} delay={300}>
                <span className={`${PREFIX}-help-icon`} tabIndex={0}>?</span>
            </Tooltip>
        )}
    </div>
);

const GeneralPanel = ({ setAttributes, attributes, init }) => {
    const { name, inputLabel, displayLabel, placeholder, defaultCBValue, checkLabel, defaultValue, additionalPadding,
        displayMode, options, displayOptions, url, rows, description, readOnly, type, addressFieldTypes,
        hiddenAddressFields, save, hidden, usermeta
    } = attributes;
    const { duplicateFound, emptyFound, handleNameChange } = init;

    const [localLabel, setLocalLabel] = useState(inputLabel ?? '');
    useEffect(() => { setLocalLabel(inputLabel ?? ''); }, [inputLabel]);
    const labelDebounceRef = useRef(null);

    const [meta, setMeta] = useEntityProp('postType', 'koalaforms-forms', 'meta');
    const formSettings = meta?.koalaforms_form_settings || {};

    return (
        <>
            <PanelBody title={__('General', TEXT_DOMAIN)} initialOpen={true}>

                <div className={`${PREFIX}-setting`}>
                    <FieldLabel label={__('ID', TEXT_DOMAIN)} help={LABELS.nameHelp} />
                    <TextControl
                        label=""
                        readOnly={true}
                        value={name}
                        className={`${PREFIX}-readonly-field`}
                        onChange={() => {}}
                    />
                    {emptyFound && (
                        <div className={`${PREFIX}-error-message`}>
                            {LABELS.nameErr}
                        </div>
                    )}
                    {duplicateFound && (
                        <div className={`${PREFIX}-error-message`}>
                            {LABELS.duplicateNameErr}
                        </div>
                    )}
                </div>

                <div className={`${PREFIX}-setting`}>
                    <FieldLabel label={__('Unique Name', TEXT_DOMAIN)} help={LABELS.labelHelp} />
                    <TextControl
                        label=""
                        value={localLabel}
                        onChange={(value) => {
                            const filtered = sanitizeInputLabel(value);
                            setLocalLabel(filtered);
                            clearTimeout(labelDebounceRef.current);
                            if (filtered.trim() !== '') {
                                labelDebounceRef.current = setTimeout(() => {
                                    setAttributes({ inputLabel: filtered.trim() });
                                }, 500);
                            }
                        }}
                        onBlur={() => {
                            clearTimeout(labelDebounceRef.current);
                            if (localLabel.trim() !== '') {
                                setAttributes({ inputLabel: localLabel.trim() });
                            } else {
                                setLocalLabel(inputLabel ?? '');
                            }
                        }}
                    />
                    {localLabel.trim() === '' && (
                        <div className={`${PREFIX}-error-message`}>
                            {__('The label field cannot be empty. Please provide a valid label.', TEXT_DOMAIN)}
                        </div>
                    )}
                </div>

                {"displayLabel" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Label', TEXT_DOMAIN)} help={LABELS.displayLabelHelp} />
                        <TextControl
                            label=""
                            value={displayLabel ?? ''}
                            placeholder={inputLabel ?? ''}
                            onChange={(value) => setAttributes({ displayLabel: value })}
                        />
                    </div>
                )}

                {"placeholder" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Placeholder', TEXT_DOMAIN)} help={LABELS.placeholderHelp} />
                        <TextControl
                            label=""
                            value={placeholder}
                            onChange={(value) => setAttributes({ placeholder: value })}
                        />
                    </div>
                )}

                {"defaultCBValue" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-cb-setting`}>
                        <ToggleControl
                            label={__('Default Value', TEXT_DOMAIN)}
                            checked={defaultCBValue}
                            onChange={(isChecked) => setAttributes({ defaultCBValue: isChecked })}
                        />
                        <Tooltip text={LABELS.cbDefaultHelp} delay={300}>
                            <span className={`${PREFIX}-help-icon`} tabIndex={0}>?</span>
                        </Tooltip>
                    </div>
                )}

                {"defaultValue" in attributes && type == 'Text' && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Default Value', TEXT_DOMAIN)} help={LABELS.defaultValueHelp} />
                        <TextControl
                            label=""
                            value={defaultValue}
                            onChange={(value) => setAttributes({ defaultValue: value })}
                        />
                    </div>
                )}

                {"hidden" in attributes && type == 'Text' && (
                    <div className={`${PREFIX}-setting ${PREFIX}-cb-setting`}>
                        <CheckboxControl
                            label={__('Hidden', TEXT_DOMAIN)}
                            checked={hidden}
                            onChange={(isChecked) => setAttributes({ hidden: isChecked })}
                        />
                        <Tooltip text={LABELS.hiddenHelp} delay={300}>
                            <span className={`${PREFIX}-help-icon`} tabIndex={0}>?</span>
                        </Tooltip>
                    </div>
                )}

                {"checkLabel" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Check Label', TEXT_DOMAIN)} help={LABELS.checkLabelHelp} />
                        <TextControl
                            label=""
                            value={checkLabel}
                            onChange={(value) => setAttributes({ checkLabel: value })}
                        />
                    </div>
                )}

                {"options" in attributes && type != 'Multiselect' && (
                    <div className={`${PREFIX}-setting`}>
                        <OptionList
                            options={options}
                            onChange={(newOptions) => setAttributes({ options: newOptions })}
                            singleDefault={true}
                        />
                    </div>
                )}

                {"options" in attributes && type == 'Multiselect' && (
                    <div className={`${PREFIX}-setting`}>
                        <OptionList
                            options={options}
                            onChange={(newOptions) => setAttributes({ options: newOptions })}
                            singleDefault={false}
                        />
                    </div>
                )}

                {"displayMode" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <SelectControl
                            label={__('Display Mode', 'koalaforms')}
                            value={displayMode}
                            options={displayOptions}
                            onChange={(newValue) => setAttributes({ displayMode: newValue })}
                        />
                        {displayMode == 'horizontal' && (
                            <span className={`${PREFIX}-field-help-text`}>{LABELS.radioHDHelp}</span>
                        )}
                        {displayMode == 'vertical' && (
                            <span className={`${PREFIX}-field-help-text`}>{LABELS.radioHVHelp}</span>
                        )}
                    </div>
                )}

                {"additionalPadding" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Additional Padding (px)', TEXT_DOMAIN)}
                            value={additionalPadding}
                            onChange={(value) => setAttributes({ additionalPadding: value })}
                        />
                    </div>
                )}

                {"url" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Page URL', TEXT_DOMAIN)}
                            value={url}
                            onChange={(value) => setAttributes({ url: value })}
                        />
                    </div>
                )}

                {"rows" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Rows', TEXT_DOMAIN)} help={LABELS.logtextRowHelp} />
                        <TextControl
                            label=""
                            value={rows}
                            onChange={(value) => setAttributes({ rows: value })}
                        />
                    </div>
                )}

                {type == 'Address' && (
                    <div className={`${PREFIX}-setting`}>
                        <BaseControl label={__('Hide Address Fields', TEXT_DOMAIN)}>
                            {addressFieldTypes.map(field => (
                                <CheckboxControl
                                    key={field.value}
                                    label={field.label}
                                    checked={hiddenAddressFields.includes(field.value)}
                                    onChange={() => {
                                        const fieldValue = field.value;
                                        const updatedFields = hiddenAddressFields.includes(fieldValue)
                                            ? hiddenAddressFields.filter(item => item !== fieldValue)
                                            : [...hiddenAddressFields, fieldValue];
                                        setAttributes({ hiddenAddressFields: updatedFields });
                                    }}
                                />
                            ))}
                        </BaseControl>
                    </div>
                )}

                {"usermeta" in attributes && formSettings.type == 'registration' && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('User Meta Key', TEXT_DOMAIN)} help={LABELS.usermetaHelp} />
                        <TextControl
                            label=""
                            value={usermeta}
                            onChange={(value) => setAttributes({ usermeta: value })}
                        />
                    </div>
                )}

            </PanelBody>
        </>
    );
}

export default GeneralPanel;
