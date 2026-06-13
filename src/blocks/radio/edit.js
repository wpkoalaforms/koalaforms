const { __ } = wp.i18n;
import { useBlockProps  } from '@wordpress/block-editor';
import { RadioControl  } from '@wordpress/components';
import {PREFIX, TEXT_DOMAIN, LABELS } from '../../utility';
import BlockContainer from '../../components/blockContainer';
import { useState, useEffect } from 'react';
import { populateDefaultAttrs } from '../../blockHelper';

export default function Edit({ attributes, setAttributes, clientId }) {

    // Extracting all the attributes 
    const { options, defaultValue, displayMode } = attributes;

    const blockProps = useBlockProps();
    populateDefaultAttrs(attributes, setAttributes);
    
    return (
        <>
            <div {...blockProps}>
                <BlockContainer blockProps={blockProps} attributes={attributes} clientId={clientId} setAttributes={setAttributes}>
                    <RadioControl
                        className={`${PREFIX}-${displayMode}-radio`}
                        label=''
                        options={options}
                        selected={defaultValue}
                        style={{pointerEvents: 'none'}}
                    />
                </BlockContainer>
            </div>
        </>
    );
}
