import { InspectorControls } from '@wordpress/block-editor';
import { useBlockInitialization, useValidateParent, isRootBlock, useDuplicateNameGuard } from '../blockHelper';
import { PREFIX } from '../utility';

// Settings Panel Components
import ValidationPanel from '../components/validationPanel';
import GeneralPanel from '../components/generalPanel';

const BlockContainer = ({ blockProps, attributes, clientId, setAttributes, children }) => {
    const { type, isPreview, previewHTML, inputLabel } = attributes;
    const localBlockProps = { className: `${PREFIX}-block-preview`, };

    blockProps = { ...blockProps, ...localBlockProps };

    // Validate for Duplicate/Empty name attribute for the block.
    const init = useBlockInitialization(clientId, setAttributes);
    useDuplicateNameGuard(clientId, attributes, setAttributes);

    if(isPreview && previewHTML){
        return (
            <div
                    dangerouslySetInnerHTML={{ __html: previewHTML }}
            ></div>
        )
    }
    
    if (!isPreview && !useValidateParent(clientId)) {
        let message;
        if (isRootBlock(type)) {
            message = `${attributes.title} cannot be used inside a Step block.`;
        } else {
            message = `${attributes.title} can only be used inside a Step block.`;
        }
    
        return (
            <p className={`${PREFIX}-invalid-parent`}>
                {message}
            </p>
        );
    }

    return (
        <>
            <InspectorControls>
                <div className={`${PREFIX}-settings-container`}>
                    <GeneralPanel setAttributes={setAttributes} attributes={attributes} init={init} />
                    <ValidationPanel setAttributes={setAttributes} attributes={attributes} />
                    {/* <ConditionapPanel setAttributes={setAttributes} attributes={attributes} /> */}
                </div>
            </InspectorControls>


            <div className={`${PREFIX}-block-wrap ${PREFIX}-editor-field-wrap ${PREFIX}-${type.toLowerCase()}-common-wrap`}>
                <label className={`${PREFIX}-editor-field-label`}>
                    {inputLabel}
                    {attributes.required && inputLabel && (
                        <span className={`${PREFIX}-required-marker`}> *</span>
                    )}
                </label>

                {children}
            </div>
        </>
    );
};

export default BlockContainer;
