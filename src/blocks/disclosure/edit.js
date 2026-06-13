const { __ } = wp.i18n;
import { useBlockProps  } from '@wordpress/block-editor';
import { TextControl, PanelBody, CheckboxControl, Tooltip, TextareaControl } from '@wordpress/components';
import {PREFIX, TEXT_DOMAIN, LABELS } from '../../utility';
import BlockContainer from '../../components/blockContainer';
import { RichText } from '@wordpress/block-editor';
import {populateDefaultAttrs } from '../../blockHelper';

export default function Edit({ attributes, setAttributes, clientId }) {

    // Extracting all the attributes 
    const {content, checkLabel} = attributes;
    const blockProps = useBlockProps();
    populateDefaultAttrs(attributes, setAttributes);

    
    return (
        <>
            {/* Block Content */}
            <div {...blockProps} >
            <BlockContainer blockProps={blockProps} attributes={attributes} clientId={clientId} setAttributes={setAttributes}>
                <div className={`${PREFIX}-disclosure-preview`}>
                    <RichText
                        tagName="p" // Output as a <p> tag
                        value={content}
                        onChange={(value) => {
                            setAttributes({ content: value })}
                        }
					    placeholder={__('Enter Text Here...', TEXT_DOMAIN)}
                    />

                    {/* Disclosure Checkbox */}
                    <CheckboxControl
                        label={checkLabel}
                        className={`${PREFIX}-disclosure-check-label`}
                        disabled
                    />
                </div>
                </BlockContainer>
            </div>
        </>
    );
}
