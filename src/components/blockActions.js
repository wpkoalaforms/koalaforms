import { __ } from '@wordpress/i18n';
import { SelectControl, PanelBody } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { getInputBlockNames,findDuplicateBlockNames, getAllBlocks, populateNameLabel } from '../blockHelper';
import { PREFIX , showErrorToast} from '../utility';
import { select, subscribe, dispatch } from '@wordpress/data';
import {STEP_BLOCK_NAME} from '../blockHelper';

// Insert merge field as a non-editable button
const insertMergeFieldAsTag = (text, fieldValue, setAttributes) => {
    const selection = window.getSelection();
    if (selection && selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        const beforeCursor = text.substring(0, range.startOffset);
        const afterCursor = text.substring(range.startOffset);
        
        const mergeFieldSyntax = `${fieldValue}`;
        const updatedText = `${beforeCursor}${mergeFieldSyntax}${afterCursor}`;
        setAttributes({ content: updatedText });
    }
};

const withMergeFieldInspectorControls = createHigherOrderComponent((BlockEdit) => {
    const mergeFields = getInputBlockNames(true).map((name) => ({ label: name, value: name }));
    return (props) => {
        const { name, attributes, setAttributes } = props;

        if (name !== 'core/paragraph') {
            return <BlockEdit {...props} />;
        }

        const onInsertMergeField = (fieldValue) => {
            insertMergeFieldAsTag(attributes.content, fieldValue, setAttributes);
        };

        return (
            <>
                <BlockEdit {...props} />
                <InspectorControls>
                    <PanelBody title={__('Merge Fields', PREFIX)}>
                        <SelectControl
                            label={__('Insert Merge Field', PREFIX)}
                            options={[{ label: __('Select...', PREFIX), value: '' }, ...mergeFields]}
                            onChange={(value) => value && onInsertMergeField(value)}
                        />
                    </PanelBody>
                </InspectorControls>
            </>
        );
    };
}, 'withMergeFieldInspectorControls');

addFilter(
    'editor.BlockEdit',
    'kf/with-merge-field-inspector-controls',
    withMergeFieldInspectorControls
);


let wasSaving = false;


// Subscribe to post-saving events
subscribe(() => {
    const { isSavingPost, isAutosavingPost } = select('core/editor');
    const isSaving = isSavingPost();

    // Run logic before save completes
    if (!wasSaving && isSaving && !isAutosavingPost()) {
        wasSaving = true;
        if(!formValidation()){
            dispatch('core/editor').unlockPostSaving();
        }
    }

    // Update saving state
    if (!isSaving) {
        wasSaving = false;
    }
});

// Your custom pre-save logic
function formValidation() {
    const blocks = select('core/block-editor').getBlocks(); // Get all blocks in the editor
    const customBlockInstances = blocks.filter(block => block.name ===  'kf/step'); // Filter by block type

    if (customBlockInstances.length == 0){
        showErrorToast(`Oops! It looks like there are no steps in the form.`);
    }
}



/////////

let previousBlocks = getAllBlocks();

subscribe(() => {
    const currentBlocks = getAllBlocks();
    // Detect if new blocks were added
    if (currentBlocks.length > previousBlocks.length) {
        const newBlocks = currentBlocks.filter(
            (block) => !previousBlocks.some((prevBlock) => prevBlock.clientId === block.clientId)
        );

        const duplicateBlocks = [];
        // newBlocks.forEach((newBlock) => {
        //     // Check if this block has inherited a uniqueId from another block
        //     const isDuplicate = previousBlocks.some(
        //         (prevBlock) => prevBlock.attributes.name === newBlock.attributes.name
        //     );

        //     if (isDuplicate) {
        //         duplicateBlocks.push(newBlock);
        //     }
        // });
        
        if (newBlocks.length){
            populateNameLabel(previousBlocks, newBlocks);
        }
    }

    // Update the previous block state
    previousBlocks = currentBlocks;
});
