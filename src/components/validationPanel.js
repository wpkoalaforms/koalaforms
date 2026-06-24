import { PREFIX, TEXT_DOMAIN, LABELS } from '../utility';
import { TextControl, PanelBody, Tooltip, CheckboxControl } from '@wordpress/components';

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

const ValidationPanel = ({ setAttributes, attributes }) => {
    const { required, maxDate, minDate, requiredError, dateFormat, minLength, maxLength,
            pattern, patternError, mask, isAge, minAge, maxAge, ageValidationMessage, min, max,
            unique, uniqueErr
         } = attributes;

    return (
        <>
            <PanelBody title={__('Validations', TEXT_DOMAIN)} initialOpen={false}>

                {"required" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-cb-setting`}>
                        <CheckboxControl
                            label={__('Required', TEXT_DOMAIN)}
                            checked={required}
                            onChange={(isChecked) => setAttributes({ required: isChecked })}
                        />
                        <Tooltip text={LABELS.reqErrHelp} delay={300}>
                            <span className={`${PREFIX}-help-icon`} tabIndex={0}>?</span>
                        </Tooltip>
                    </div>
                )}

                {required && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Required Error Message', TEXT_DOMAIN)} help={LABELS.reqErrMessageHelp} />
                        <TextControl
                            label=""
                            value={requiredError}
                            onChange={(value) => setAttributes({ requiredError: value })}
                        />
                    </div>
                )}

                {"unique" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-cb-setting`}>
                        <CheckboxControl
                            label={__('Unique', TEXT_DOMAIN)}
                            checked={unique}
                            onChange={(isChecked) => setAttributes({ unique: isChecked })}
                        />
                        <Tooltip text={LABELS.uniqueHelp} delay={300}>
                            <span className={`${PREFIX}-help-icon`} tabIndex={0}>?</span>
                        </Tooltip>
                    </div>
                )}

                {unique && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Unique Error Message', TEXT_DOMAIN)} help={LABELS.uniqueErrHelp} />
                        <TextControl
                            label=""
                            value={uniqueErr}
                            onChange={(value) => setAttributes({ uniqueErr: value })}
                        />
                    </div>
                )}

                {"minDate" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Minimum Date', TEXT_DOMAIN)} help={LABELS.minDateHelp} />
                        <input
                            type="date"
                            id="min-date"
                            value={minDate}
                            onChange={(event) => setAttributes({ minDate: event.target.value })}
                        />
                    </div>
                )}

                {"maxDate" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-date-setting`}>
                        <FieldLabel label={__('Maximum Date', TEXT_DOMAIN)} help={LABELS.maxDateHelp} />
                        <input
                            type="date"
                            id="max-date"
                            value={maxDate}
                            onChange={(event) => setAttributes({ maxDate: event.target.value })}
                        />
                    </div>
                )}

                {"isAge" in attributes && (
                    <div className={`${PREFIX}-setting ${PREFIX}-date-setting ${PREFIX}-cb-setting`}>
                        <CheckboxControl
                            label={__('Treat as Age Field', TEXT_DOMAIN)}
                            checked={isAge}
                            onChange={(isChecked) => setAttributes({ isAge: isChecked })}
                        />
                        <Tooltip text={LABELS.ageHelp} delay={300}>
                            <span className={`${PREFIX}-help-icon`} tabIndex={0}>?</span>
                        </Tooltip>
                    </div>
                )}

                {isAge && (
                    <>
                        <div className={`${PREFIX}-setting`}>
                            <FieldLabel
                                label={__('Minimum Age', TEXT_DOMAIN)}
                                help={__('Set the minimum age allowed. Leave blank for no minimum.', TEXT_DOMAIN)}
                            />
                            <TextControl
                                label=""
                                value={minAge}
                                onChange={(value) => {
                                    const parsedValue = parseInt(value, 10);
                                    setAttributes({ minAge: isNaN(parsedValue) ? '' : parsedValue });
                                }}
                            />
                        </div>

                        <div className={`${PREFIX}-setting`}>
                            <FieldLabel
                                label={__('Maximum Age', TEXT_DOMAIN)}
                                help={__('Set the maximum age allowed. Leave blank for no maximum.', TEXT_DOMAIN)}
                            />
                            <TextControl
                                label=""
                                value={maxAge}
                                onChange={(value) => {
                                    const parsedValue = parseInt(value, 10);
                                    setAttributes({ maxAge: isNaN(parsedValue) ? '' : parsedValue });
                                }}
                            />
                        </div>

                        <div className={`${PREFIX}-setting`}>
                            <FieldLabel
                                label={__('Validation Message', TEXT_DOMAIN)}
                                help={__('Custom error message when the age does not meet the requirement.', TEXT_DOMAIN)}
                            />
                            <TextControl
                                label=""
                                value={ageValidationMessage}
                                onChange={(value) => setAttributes({ ageValidationMessage: value })}
                            />
                        </div>
                    </>
                )}

                {"minLength" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Minimum Length', TEXT_DOMAIN)} help={LABELS.minTextHelp} />
                        <TextControl
                            label=""
                            value={minLength}
                            onChange={(value) => {
                                const parsedValue = parseInt(value, 10);
                                setAttributes({ minLength: isNaN(parsedValue) ? '' : parsedValue });
                            }}
                        />
                    </div>
                )}

                {"maxLength" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Maximum Length', TEXT_DOMAIN)} help={LABELS.maxTextHelp} />
                        <TextControl
                            label=""
                            value={maxLength}
                            onChange={(value) => {
                                const parsedValue = parseInt(value, 10);
                                setAttributes({ maxLength: isNaN(parsedValue) ? '' : parsedValue });
                            }}
                        />
                    </div>
                )}

                {"min" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Minimum', TEXT_DOMAIN)} help={LABELS.minNumberHelp} />
                        <TextControl
                            label=""
                            value={min}
                            onChange={(value) => {
                                const parsedValue = parseInt(value, 10);
                                setAttributes({ min: isNaN(parsedValue) ? '' : parsedValue });
                            }}
                        />
                    </div>
                )}

                {"max" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Maximum', TEXT_DOMAIN)} help={LABELS.maxNumberHelp} />
                        <TextControl
                            label=""
                            value={max}
                            onChange={(value) => {
                                const parsedValue = parseInt(value, 10);
                                setAttributes({ max: isNaN(parsedValue) ? '' : parsedValue });
                            }}
                        />
                    </div>
                )}

                {"mask" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Mask', TEXT_DOMAIN)} help={LABELS.maskHelp} />
                        <TextControl
                            label=""
                            value={mask}
                            onChange={(value) => setAttributes({ mask: value })}
                        />
                    </div>
                )}

                {"pattern" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Pattern', TEXT_DOMAIN)} help={LABELS.patternHelp} />
                        <TextControl
                            label=""
                            value={pattern}
                            onChange={(value) => setAttributes({ pattern: value })}
                        />
                    </div>
                )}

                {"pattern" in attributes && (
                    <div className={`${PREFIX}-setting`}>
                        <FieldLabel label={__('Pattern Error Message', TEXT_DOMAIN)} help={LABELS.patternErrHelp} />
                        <TextControl
                            label=""
                            value={patternError}
                            onChange={(value) => setAttributes({ patternError: value })}
                        />
                    </div>
                )}

            </PanelBody>
        </>
    );
}

export default ValidationPanel;
