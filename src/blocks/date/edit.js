import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import BlockContainer from '../../components/blockContainer';
import { populateDefaultAttrs } from '../../blockHelper';

export default function Edit({ attributes, setAttributes, clientId }) {
    const { title } = attributes;
    const blockProps = useBlockProps();
    populateDefaultAttrs(attributes, setAttributes);
    return (
        <>
            <div {...blockProps}>
                <BlockContainer blockProps={blockProps} attributes={attributes} clientId={clientId} setAttributes={setAttributes}>
                    <TextControl
                        label=""
                        placeholder={title}
                        style={{pointerEvents: 'none'}}
                        readOnly
                    />
                </BlockContainer>
            </div>
        </>
    );
}
