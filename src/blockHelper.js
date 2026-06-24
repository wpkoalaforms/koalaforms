const { __ } = wp.i18n;
import { useSelect, useDispatch, select, subscribe } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import { PREFIX, TEXT_DOMAIN } from './utility';
import {default as blockMeta} from './blocks/step/block.json';
import { v4 as uuidv4 } from 'uuid';

// Defining a global category for all the form blocks
export const BLOCK_CATEGORY = 'koalaforms';

// Exported constants for block names
export const STEP_BLOCK_NAME = `${PREFIX}/Step`;

// Sourced from PHP: inc/AppUtility.php — INPUT_BLOCK_TYPES constant.
// Passed to JS via wp_localize_script in Blocks.php as koalaformsConfig.inputBlockTypes.
// To add a new field block type, update AppUtility::INPUT_BLOCK_TYPES in PHP only.
export const INPUT_BLOCK_TYPES = window.koalaformsConfig?.inputBlockTypes ?? [];

export const ADDRESS_BLOCK_TYPES = [
    'Text','Select', 'core/columns'
];

export const OTHER_BLOCK_TYPES = [];
export const ROOT_BLOCKS = ['Step'];

// Utility to fetch all block names with a prefix
export const getAllBlockWithPrefix = () => {
    const koalaformsBlocks = [...INPUT_BLOCK_TYPES, ...OTHER_BLOCK_TYPES].map(
        (block) => `${PREFIX}/${block.toLowerCase()}`
    );
    const coreBlocks = [
        'core/paragraph', 'core/heading', 'core/columns', 'core/details',
        'core/image', 'core/video', 'core/table', 'core/spacer'
    ];
    return [...koalaformsBlocks, ...coreBlocks];
};

// Utility to recursively fetch parent and child block names
export const useBlockParentName = (clientId) => {
    return useSelect((select) => {
        const { getBlockRootClientId, getBlockName } = select('core/block-editor');

        const getTopLevelParentName = (currentClientId) => {
            const parentClientId = getBlockRootClientId(currentClientId);
            return parentClientId ? getTopLevelParentName(parentClientId) : getBlockName(currentClientId);
        };

        return {
            parentBlockName: getTopLevelParentName(clientId),
            childBlockName: getBlockName(clientId),
        };
    }, [clientId]);
};

// Check if a block is a root block
export const isRootBlock = (blockName) => {
    blockName = blockName?.toLowerCase();
    const normalizedRoots = ROOT_BLOCKS.map((block) => `${PREFIX}/${block.toLowerCase()}`);
    return normalizedRoots.includes(blockName) || ROOT_BLOCKS.map((b) => b.toLowerCase()).includes(blockName);
};

// Validate parent block relationship
export const useValidateParent = (clientId) => {
    const { parentBlockName, childBlockName } = useBlockParentName(clientId);
    if (isRootBlock(childBlockName)) {
        return !parentBlockName.includes('step');
    }

    return parentBlockName === `${PREFIX}/step`;
};

// Helper to flatten block structures
export const flattenBlocks = (blocks) => {
    const result = [];
    const extractBlocks = (block) => {
        result.push(block);
        block.innerBlocks?.forEach(extractBlocks);
    };
    blocks.forEach(extractBlocks);
    return result;
};

// Helper to validate block names
const validateBlockNames = (blocks) => {
    const blockNames = {};
    let duplicateFound = false;
    let emptyFound = false;

    blocks.forEach((block) => {
        if (block.name.includes(PREFIX)) {
            const blockNameValue = block.attributes.name?.trim();
            if (!blockNameValue) emptyFound = true;
            if (blockNameValue && blockNames[blockNameValue]) duplicateFound = true;
            else blockNames[blockNameValue] = true;
        }
    });

    return { duplicateFound, emptyFound };
};

// Toggle activation state
export const useActive = () => {
    const [isActivated, setIsActivated] = useState(false);
    return {
        isActivated,
        toggleActive: () => setIsActivated((prevState) => !prevState),
    };
};

// Validate blocks and handle name changes
export const useBlockValidation = (clientId, setAttributes) => {
    const [duplicateFound, setDuplicateFound] = useState(false);
    const [emptyFound, setEmptyFound] = useState(false);

    const { lockPostSaving, unlockPostSaving } = useDispatch('core/editor');

    const handleNameChange = (name) => {
        name = name.replace(/[^a-zA-Z0-9-_]/g, '');
        setAttributes({ name });
        const { duplicateFound, emptyFound } = validateBlockNames(getAllBlocks());
        setDuplicateFound(duplicateFound);
        setEmptyFound(emptyFound);

        (duplicateFound || emptyFound) ? lockPostSaving('block-name-validation') : unlockPostSaving('block-name-validation');
    };

    return { duplicateFound, emptyFound, handleNameChange };
};

// Populate name labels for blocks
export const populateNameLabel = (existingBlocks, newBlocks) => {
    const blockNames = existingBlocks.map((block) => block.attributes.name);
    const blockTypeCounts = {};
    const newBlockClientIds = newBlocks.map((b)=>b.clientId);

    existingBlocks.forEach((block) => {
        const blockType = block.attributes.type || '';
        blockTypeCounts[blockType] = (blockTypeCounts[blockType] || 0) + 1;
    });

    newBlocks.forEach((block) => {
        const blockType = block.attributes.type || '';
        blockTypeCounts[blockType] = (blockTypeCounts[blockType] || 0) + 1;
        if (block.name.includes(PREFIX) && newBlockClientIds.includes(block.clientId) && !block.attributes.name?.trim()) {
            block.attributes.name = uuidv4();
            if(!block.attributes.subtype)
                block.attributes.inputLabel = `${blockType} ${blockTypeCounts[blockType]}`;
        }
    })
    
};

// On mount: if this block's name duplicates another block it was cloned — assign a fresh name.
export const useDuplicateNameGuard = (clientId, attributes, setAttributes) => {
    useEffect(() => {
        const { name } = attributes;
        if (!name) return;
        const isDuplicate = getAllBlocks().some(
            (block) => block.attributes.name === name && block.clientId !== clientId
        );
        if (isDuplicate) {
            setAttributes({ name: uuidv4() });
        }
    }, []);
};

// Initialize blocks with default attributes
export const useBlockInitialization = (clientId, setAttributes) => {
    const { duplicateFound, emptyFound, handleNameChange } = useBlockValidation(clientId, setAttributes);
    return { duplicateFound, emptyFound, handleNameChange };
};

// Highlight duplicate blocks in the editor
export const highlightDuplicateBlocks = () => {
    findDuplicateBlockNames().forEach((duplicateBlock) => {
        const blockElement = document.querySelector(`[data-block="${duplicateBlock.clientId}"]`);
        if (blockElement) {
            blockElement.style.border = '2px solid red';
            blockElement.style.backgroundColor = '#ffe6e6';
        }
    });
};

// Retrieve all blocks and flatten them
export const getAllBlocks = () => flattenBlocks(select('core/block-editor').getBlocks());

// Get input block names for a specific syntax
export const getInputBlockNames = (label = false) => {
    return getAllBlocks()
        .filter((block) => INPUT_BLOCK_TYPES.includes(block.attributes.type))
        .map((block) => (label ? `${block.attributes.inputLabel}` : block.attributes.name));
};

export const getInputBlockNamesOptions = (types) => {
    types = types || INPUT_BLOCK_TYPES;
    const blockOptions =    getAllBlocks()
                    .filter((block) => types.includes(block.attributes.type))
                    .map((block) => ({value: block.attributes.name, label: block.attributes.inputLabel, text: block.attributes.inputLabel}));
    const defaultOptions = [{text: 'Please select',label: 'Please select', value: ''}];
    return [...defaultOptions, ...blockOptions];
};

// Get input block names for a specific syntax
export const getInputBlockOptions = () => {
    const options=  getInputBlockNames(true).map((option) => ({text: option,label: option, value: option}));
    return options;
};

// Process metadata for common attributes
//
// NOTE FOR PHP / FormBuilder: This function injects runtime attributes into
// every block at editor registration time. These do NOT exist in block.json.
//
// 'type' and 'conditions' are mirrored in BlockSchema::resolve() in
// inc/FormBuilder.php so that _field_config is structurally identical whether
// a form was built in the editor or via FormBuilder.
//
// 'title', 'description', 'isPreview', 'previewHTML' are editor UI only —
// no PHP counterpart needed.
//
// If you add a new runtime attribute here that downstream code (validation,
// rendering, reporting) will read from _field_config, add it to
// BlockSchema::resolve() in inc/FormBuilder.php as well.
export const commonMetaProcessing = (metadata) => {
    metadata.attributes = {
        ...metadata.attributes,
        type: { type: 'string', default: metadata.name },
        title: { type: 'string', default: metadata.title },
        description: { type: 'string', default: "" },
        conditions: { type: 'array', default: [] },
        isPreview: false,
        previewHTML: '',
        ...(metadata.attributes.inputLabel ? {} : {
            inputLabel: { type: 'string', default: metadata.title }
        })
    };
    metadata.name = `${PREFIX}/${metadata.name.toLowerCase()}`;
    metadata.category = BLOCK_CATEGORY;
    metadata.textdomain = TEXT_DOMAIN;
    return metadata;
}

// Find duplicate block names
export const findDuplicateBlockNames = () => {
    const blocks = getAllBlocks();
    const nameCount = {};
    const duplicates = [];
    blocks.forEach(({ attributes: { name }, clientId }) => {
        if (nameCount[name]) nameCount[name].push(clientId);
        else nameCount[name] = [clientId];
    });
    Object.keys(nameCount).forEach((name) => {
        if (nameCount[name].length > 1) duplicates.push(...nameCount[name]);
    });
    return duplicates;
};

// Wait for a specific block to register
export const waitForColumnsBlock = () => {
    return new Promise((resolve) => {
        const checkBlockRegistration = () => {
            const blockType = wp.blocks.getBlockType('core/columns');
            if (blockType) resolve(blockType);
            else setTimeout(checkBlockRegistration, 50);
        };
        checkBlockRegistration();
    });
};

// Add a new block with default attributes
export const addNewBlock = () => wp.blocks.createBlock(`${PREFIX}/step`, { ...commonMetaProcessing(blockMeta), name: '' });

// Populates default values for null-defaulted block attributes in the Gutenberg editor.
//
// ⚠️  KEEP IN SYNC WITH PHP: inc/FormBuilder.php — BlockSchema::NULL_OVERRIDES
//
// This function and NULL_OVERRIDES share the same contract: any attribute that
// defaults to null in block.json and needs a concrete value must be handled in
// BOTH places. This function runs in the browser editor; NULL_OVERRIDES runs when
// forms are created programmatically via FormBuilder (API, CLI, etc.).
//
// When you add or change a null-default here, update NULL_OVERRIDES in PHP too,
// and vice versa. The two must always produce the same values for the same keys.
export const populateDefaultAttrs = (attributes, setAttributes) => {
    const {requiredError, rows, options, type, inputLabel, ageValidationMessage, patternError, uniqueErr} = attributes;
    const defaultAttrs    = {};

    // Common for all the input elements
    if(requiredError === null){
        defaultAttrs.requiredError = __('This is a required field.', TEXT_DOMAIN);
    }

    if(inputLabel === null){
        defaultAttrs.inputLabel = type;
    }
    
    if(uniqueErr === null){
        defaultAttrs.uniqueErr = __('Value already exist in the database.', TEXT_DOMAIN);
    }

    if(['Text', 'Url', 'Email'].includes(type)){
        defaultAttrs.patternError = __('Value does not match with given pattern.', TEXT_DOMAIN);
    }

    if(type === 'Phone' && attributes.mask === null){
        defaultAttrs.mask = '(###) ###-####';
    }

    // For Long Text area 
    if(rows === null){
        defaultAttrs.rows = 5;
    }

    // Populating default options for Radio, Select and Multiselect
    if (options === null){
        defaultAttrs.options = [{"label": "Option 1", "value": "option1"}, {"label": "Option 2", "value": "option2"}];
    }
    
    // Prepopulating the default option.
    if (options?.length){
        const defaultOption = options.find((option) => option.default);
        if(defaultOption?.default){
            defaultAttrs.defaultValue = defaultOption.value;
        }
    }

    if (type == 'Disclosure'){
        const {content, checkLabel} = attributes;
        if (content === null){
            defaultAttrs.content = __("Please read the disclosure statement carefully before proceeding. By checking the box below, you agree to the terms and conditions outlined in this document. \u003cbr\u003e\u003cbr\u003e\u003cstrong\u003eI acknowledge and agree to the following\u003c/strong\u003e",TEXT_DOMAIN);
        }
        if(checkLabel === null){
            defaultAttrs.checkLabel = __("I have read and understood the disclosure statement.",TEXT_DOMAIN);
        }
    }

    // Prepopulating age validation message
    if(ageValidationMessage === null){
        defaultAttrs.ageValidationMessage = __('Invalid age value.', TEXT_DOMAIN);
    }
    
    setAttributes(defaultAttrs);
}
