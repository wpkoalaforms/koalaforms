import { PREFIX, TEXT_DOMAIN, LABELS } from '../utility';
import { TextControl, PanelBody, Tooltip, CheckboxControl } from '@wordpress/components';

const { __ } = wp.i18n;

const ValidationPanel = ({ setAttributes, attributes }) => {
    const { required, maxDate, minDate, requiredError, dateFormat, minLength, maxLength,
            pattern, patternError, mask, isAge, minAge, maxAge, ageValidationMessage,min,max,
            unique, uniqueErr
         } = attributes;

    return (
        <>
            {/* Validation Options Section */}
            <PanelBody title={__('Validations', TEXT_DOMAIN)} initialOpen={false}>

                {"required" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-cb-setting`}>
                        <CheckboxControl
                            label={__('Required', TEXT_DOMAIN)}
                            checked={required}
                            onChange={(isChecked) => setAttributes({ required: isChecked })}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.reqErrHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.reqErrHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        */}
                    </div>
                )}

                {required && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Required Error Message', TEXT_DOMAIN)}
                            value={requiredError}
                            onChange={(value) => setAttributes({ requiredError: value })}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.reqErrMessageHelp, TEXT_DOMAIN)}</span>

                    </div>
                )}

                {"unique" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-cb-setting`}>
                        <CheckboxControl
                            label={__('Unique', TEXT_DOMAIN)}
                            checked={unique}
                            onChange={(isChecked) => setAttributes({ unique: isChecked })}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.uniqueHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {unique && (
                    <div className={`${PREFIX}-setting ${PREFIX}-cb-setting`}>
                        <TextControl
                            label={__('Unique Error Message', TEXT_DOMAIN)}
                            value={uniqueErr}
                            onChange={(value) => setAttributes({ uniqueErr: value})}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.uniqueErrHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {"minDate" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <label htmlFor="min-date">
                            {__('Minimum Date', TEXT_DOMAIN)}
                        </label>
                        <input
                            type="date"
                            id="min-date"
                            value={minDate}
                            onChange={(event) => setAttributes({ minDate: event.target.value })}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.minDateHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {"maxDate" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-date-setting`}>
                        <label htmlFor="max-date">
                            {__('Maximum Date', TEXT_DOMAIN)}
                        </label>
                        <input
                            type="date"
                            id="max-date"
                            value={maxDate}
                            onChange={(event) => setAttributes({ maxDate: event.target.value })}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.maxDateHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {"isAge" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-date-setting`}>
                        <CheckboxControl
                            label={__('Treat as Age Field', TEXT_DOMAIN)}
                            checked={isAge}
                            onChange={(isChecked) => setAttributes({ isAge: isChecked })}
                        />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.ageHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {isAge && (
                    <>
                        <div className={`${PREFIX}-setting`}>
                            <TextControl
                                label={__('Minimum Age', TEXT_DOMAIN)}
                                value={minAge}
                                onChange={(value) => {
                                    const parsedValue = parseInt(value, 10);
                                    setAttributes({ minAge: isNaN(parsedValue) ? '' : parsedValue })
                                }
                                }
                            />
                            <span className={`${PREFIX}-field-help-text`}>{__('Set the minimum age allowed. Leave blank for no minimum.', TEXT_DOMAIN)}</span>
                        </div>

                        <div className={`${PREFIX}-setting`}>
                            <TextControl
                                label={__('Maximum Age', TEXT_DOMAIN)}
                                value={maxAge}
                                onChange={(value) => {
                                    const parsedValue = parseInt(value, 10);
                                    setAttributes({ maxAge: isNaN(parsedValue) ? '' : parsedValue })
                                }
                                }
                            />

                            <span className={`${PREFIX}-field-help-text`}>{__('Set the maximum age allowed. Leave blank for no maximum.', TEXT_DOMAIN)}</span>
                        </div>

                        <div className={`${PREFIX}-setting`}>
                            <TextControl
                                label={__('Validation Message', TEXT_DOMAIN)}
                                value={ageValidationMessage}
                                onChange={(value) => setAttributes({ ageValidationMessage: value })}

                            />

                            <span className={`${PREFIX}-field-help-text`}>{__('Custom error message when the age does not meet the requirement.', TEXT_DOMAIN)}</span>
                        </div>
                    </>
                )}


                {"minLength" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Minimum Length', TEXT_DOMAIN)}
                            value={minLength}
                            onChange={(value) => {
                                const parsedValue = parseInt(value, 10);
                                setAttributes({ minLength: isNaN(parsedValue) ? '' : parsedValue });
                            }} />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.minTextHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {"maxLength" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Maximum Length', TEXT_DOMAIN)}
                            value={maxLength}
                            onChange={(value) => {
                                const parsedValue = parseInt(value, 10);
                                setAttributes({ maxLength: isNaN(parsedValue) ? '' : parsedValue });
                            }} />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.maxTextHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {"min" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Minimum', TEXT_DOMAIN)}
                            value={min}
                            onChange={(value) => {
                                const parsedValue = parseInt(value, 10);
                                setAttributes({ min: isNaN(parsedValue) ? '' : parsedValue });
                            }} />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.minNumberHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {"max" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Maximum', TEXT_DOMAIN)}
                            value={max}
                            onChange={(value) => {
                                const parsedValue = parseInt(value, 10);
                                setAttributes({ max: isNaN(parsedValue) ? '' : parsedValue });
                            }} />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.maxNumberHelp, TEXT_DOMAIN)}</span>
                    </div>
                )}

                {/*"format" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-date-setting`}>
                        <TextControl
                            label={__('Format', TEXT_DOMAIN)}
                            value={dateFormat}
                            onChange={(value) => setAttributes({ dateFormat: value })} />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.dateFormatHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.dateFormatHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        }

                    </div>
                )*/}

                {"mask" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Mask', TEXT_DOMAIN)}
                            value={mask}
                            onChange={(value) => setAttributes({ mask: value })} />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.maskHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.maskHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        */}
                    </div>
                )}

                {"pattern" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Pattern', TEXT_DOMAIN)}
                            value={pattern}
                            onChange={(value) => setAttributes({ pattern: value })} />
                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.patternHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.patternHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        */}
                    </div>
                )}


                {"pattern" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <TextControl
                            label={__('Pattern Error Message', TEXT_DOMAIN)}
                            value={patternError}
                            onChange={(value) => setAttributes({ patternError: value })} />

                        <span className={`${PREFIX}-field-help-text`}>{__(LABELS.patternErrHelp, TEXT_DOMAIN)}</span>
                        {/** 
                        <Tooltip text={__(LABELS.patternErrHelp, TEXT_DOMAIN)}>
                            <span className={`${PREFIX}-help-icon`}>?</span>
                        </Tooltip>
                        */}
                    </div>
                )}


            </PanelBody>
        </>
    );
}

export default ValidationPanel;