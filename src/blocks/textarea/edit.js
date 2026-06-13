const { __ } = wp.i18n;
import { useBlockProps } from '@wordpress/block-editor';
import { TextControl, PanelBody, CheckboxControl, Tooltip,TextareaControl } from '@wordpress/components';
import BlockContainer from '../../components/blockContainer';
import{ populateDefaultAttrs } from '../../blockHelper';

export default function Edit({ attributes, setAttributes, clientId }) {
    const { title, rows } = attributes;

    const blockProps = useBlockProps();
    populateDefaultAttrs(attributes, setAttributes);
    
    return (
        <>
            <div {...blockProps}>
            <BlockContainer blockProps={blockProps} attributes={attributes} clientId={clientId} setAttributes={setAttributes}>
                <TextareaControl
                    label=""
                    placeholder={title} // Dynamic placeholder based on the 'type' variable
                    style={{ pointerEvents: 'none' }} // Makes the textarea non-editable
                    rows={rows}
                />
            </BlockContainer>
            </div>
        </>
    );
}
