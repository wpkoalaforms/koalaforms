import { PREFIX, TEXT_DOMAIN, LABELS } from '../utility';
import { TextControl, PanelBody, BaseControl, SelectControl, ToggleControl, CheckboxControl } from '@wordpress/components';
import OptionList from '../components/optionsList';
import { useEntityProp } from '@wordpress/core-data';
import { useState, useEffect, useRef } from '@wordpress/element';

const { __ } = wp.i18n;

const GeneralPanel = ({ setAttributes, attributes, init }) => {
    const { name, inputLabel, placeholder, defaultCBValue, checkLabel, defaultValue, additionalPadding,
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
                    <TextControl
                        readOnly={true}
                        label={__('Name', TEXT_DOMAIN)}
                        value={name}
                        onChange={(value) => handleNameChange(value)}
                    />
                    <span className={`${PREFIX}-field-help-text`}>{__(LABELS.nameHelp, TEXT_DOMAIN)}</span>
                </div> 

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

                <div className={`${PREFIX}-setting`}>
                    <TextControl
                        label={__('Label', TEXT_DOMAIN)}
                        value={localLabel}
                        onChange={(value) => {
                            setLocalLabel(value);
                            clearTimeout(labelDebounceRef.current);
                            if (value.trim() !== '') {
                                labelDebounceRef.current = setTimeout(() => {
                                    setAttributes({ inputLabel: value.trim() });
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
                        }} />
                    {localLabel.trim() === '' && (
                        <div className={`${PREFIX}-error-message`}>
                            {__('The label field cannot be empty. Please provide a valid label.', TEXT_DOMAIN)}
                        </div>
                    )}
                    <span className={`${PREFIX}-field-help-text`}>{__(LABELS.labelHelp, TEXT_DOMAIN)}</span>
                    {/** 
                    <Tooltip text={__(LABELS.labelHelp, TEXT_DOMAIN)}>
                        <span className={`${PREFIX}-help-icon`}>?</span>
                    </Tooltip>
                    */}
                </div>

                <div className={`${PREFIX}-setting`}>
                    <TextControl
                        label={__('Description', TEXT_DOMAIN)}
                        value={description}
                        onChange={(value) => setAttributes({ description: value })} />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.descHelp, TEXT_DOMAIN)}</span>
                    {/** <Tooltip text={__(LABELS.descHelp, TEXT_DOMAIN)}>
                        <span className={`${PREFIX}-help-icon`}>?</span>
                    </Tooltip>
                    */}
                </div>

                {"placeholder" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Placeholder', TEXT_DOMAIN)}
                            value={placeholder}
                            onChange={(value) => setAttributes({ placeholder: value })} />
                             <span className={`${PREFIX}-field-help-text`}>{__(LABELS.placeholderHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.placeholderHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        */}
                    </div>
                )}

                {"defaultCBValue" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-cb-setting`}>
                        <ToggleControl
                            label={__('Default Value', TEXT_DOMAIN)}
                            checked={defaultCBValue}
                            onChange={(isChecked) => setAttributes({ defaultCBValue: isChecked })} 
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.cbDefaultHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {"defaultValue" in attributes && type =='Text' && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Default Value', TEXT_DOMAIN)}
                            value={defaultValue}
                            onChange={(value) => setAttributes({ defaultValue: value })} />
                            <span className={`${PREFIX}-field-help-text`}>{__(LABELS.defaultValueHelp, TEXT_DOMAIN)}</span>
                    
                    </div>
                )}

                {"hidden" in attributes && type =='Text' && (
                    <div className={`${PREFIX}-setting`}>
                        <CheckboxControl
                            label={__('Hidden', TEXT_DOMAIN)}
                            checked={hidden}
                            onChange={(isChecked) => setAttributes({ hidden: isChecked })}
                        />
                         <span className={`${PREFIX}-field-help-text`}>{__(LABELS.hiddenHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}


                {"checkLabel" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Check Label', TEXT_DOMAIN)}
                            value={checkLabel}
                            onChange={(value) => setAttributes({ checkLabel: value })} />
                            <span className={`${PREFIX}-field-help-text`}>{__(LABELS.checkLabelHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.checkLabelHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        */}

                    </div>
                )}

                {"options" in attributes && type!='Multiselect' && (
                    <div className={`${PREFIX}-setting`}>
                        <OptionList
                            options={options}
                            onChange={(newOptions) => setAttributes({ options: newOptions })}
                            singleDefault={true}
                        />
                    </div>
                )}

                {"options" in attributes && type=='Multiselect' && (
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
                            label={__('Display Mode', PREFIX)} // Label for the dropdown
                            value={displayMode} // Current value of the dropdown
                            options={displayOptions} // Array of options
                            onChange={(newValue) => setAttributes({ displayMode: newValue })}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.paddingHelp, TEXT_DOMAIN)}</span>

                        {/* Tooltip for Horizontal Display Mode */}
                        {displayMode == 'horizontal' && (
                            <span className={`${PREFIX}-field-help-text`}>{__(LABELS.radioHDHelp, TEXT_DOMAIN)}</span>
                        )}

                        {/* Tooltip for Vertical Display Mode */}
                        {displayMode == 'vertical' && (
                            <span className={`${PREFIX}-field-help-text`}>{__(LABELS.radioHVHelp, TEXT_DOMAIN)}</span>
                        )}
                    </div>
                )}



                {"additionalPadding" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Additional Padding (px)', TEXT_DOMAIN)}
                            value={additionalPadding}
                            onChange={(value) => setAttributes({ additionalPadding: value })} />
                            <span className={`${PREFIX}-field-help-text`}>{__(LABELS.paddingHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.paddingHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        */}
                    </div>
                )}

                {"url" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Page URL', TEXT_DOMAIN)}
                            value={url}
                            onChange={(value) => setAttributes({ url: value })} />
                    </div>
                )}

                {"rows" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Rows', TEXT_DOMAIN)}
                            value={rows}
                            onChange={(value) => setAttributes({ rows: value })} />
                            <span className={`${PREFIX}-field-help-text`}>{__(LABELS.logtextRowHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.logtextRowHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        */}
                    </div>
                )}
                {/* {"readOnly" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <ToggleControl
                            label={__('Read Only', TEXT_DOMAIN)}
                            checked={readOnly}
                            onChange={(isChecked) => setAttributes({ readOnly: isChecked })} 
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.readOnlyHelp, TEXT_DOMAIN)}</span>
                    </div>
                )} */}

                {type=='Address' && (
                    <div className={`${PREFIX}-setting`}>
                        <BaseControl label="Hide Address Fields">
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
                                    setAttributes({ hiddenAddressFields: updatedFields })
                                }} 
                            />
                        ))}
                        </BaseControl>
                        <span className={`${PREFIX}-field-help-text`}>Hide Individual Address Fields</span>
                    </div>
                )}

                {"usermeta" in attributes && formSettings.type == 'registration' && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('User Meta Key', TEXT_DOMAIN)}
                            value={usermeta}
                            onChange={(value) => setAttributes({ usermeta: value })} />

                    <span className={`${PREFIX}-field-help-text`}>{__(LABELS.usermetaHelp, TEXT_DOMAIN)}</span>
                    </div>
                )} 
            </PanelBody>
        </>
    );
}

export default GeneralPanel;
