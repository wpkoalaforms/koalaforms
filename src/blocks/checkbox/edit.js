import { useBlockProps } from '@wordpress/block-editor';
import { CheckboxControl } from '@wordpress/components';
import BlockContainer from '../../components/blockContainer';
import {populateDefaultAttrs} from '../../blockHelper';

export default function Edit({ attributes, setAttributes, clientId }) {
    const {defaultCBValue } = attributes;
    const blockProps = useBlockProps();
    populateDefaultAttrs(attributes, setAttributes);

    return (
        <>
            <div {...blockProps}>
                    <BlockContainer blockProps={blockProps} attributes={attributes} clientId={clientId} setAttributes={setAttributes}>
                        <CheckboxControl
                            label=""
                            checked={defaultCBValue}
                            disabled
                        />
                    </BlockContainer>
            </div>
        </>
    );
}
