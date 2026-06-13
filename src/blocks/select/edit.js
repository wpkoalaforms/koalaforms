const { __ } = wp.i18n;
import { useBlockProps  } from '@wordpress/block-editor';
import { SelectControl  } from '@wordpress/components';
import BlockContainer from '../../components/blockContainer';
import { useEffect } from 'react';
import { populateDefaultAttrs } from '../../blockHelper';

export default function Edit({ attributes, setAttributes, clientId }) {

    // Extracting all the attributes 
    const { options, defaultValue} = attributes;

    const blockProps = useBlockProps();
    populateDefaultAttrs(attributes, setAttributes);

    return (
        <>
            {/* Block Content */}
            <div {...blockProps}>
            <BlockContainer blockProps={blockProps} attributes={attributes} clientId={clientId} setAttributes={setAttributes}>
                <SelectControl
                    label=''
                    options={options}
                    value={defaultValue}
                />
            </BlockContainer>
            </div>
        </>
    );
}
