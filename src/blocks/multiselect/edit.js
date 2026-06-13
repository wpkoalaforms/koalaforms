const { __ } = wp.i18n;
import { useBlockProps  } from '@wordpress/block-editor';
import { CheckboxControl  } from '@wordpress/components';
import BlockContainer from '../../components/blockContainer';
import { useState, useEffect } from 'react';
import { populateDefaultAttrs } from '../../blockHelper';

export default function Edit({ attributes, setAttributes, clientId }) {
    let {options, readOnly} = attributes;
    populateDefaultAttrs(attributes, setAttributes);

    const blockProps = useBlockProps();

    return (
        <>
            <div {...blockProps}>
                <BlockContainer blockProps={blockProps} attributes={attributes} clientId={clientId} setAttributes={setAttributes}>
                {options.map((option, index) => (
                    <CheckboxControl
                        key={index}
                        label={option.label} // Label for the checkbox
                        checked={option.default} // Check if the option is selected
                        style={{pointerEvents: 'none'}}
                        disabled={readOnly}
                    />
                    ))}
                </BlockContainer>
            </div>
        </>
    );
}
