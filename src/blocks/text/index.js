import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { commonMetaProcessing } from '../../blockHelper';

/**
 * Internal dependencies
 */
import Edit from './edit';
import Save from './save';

/**
 * Custom SVG Icon
 */
const textFieldBlockIcon  = (
<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M560-160v-520H360v-120h520v120H680v520H560Zm-360 0v-320H80v-120h360v120H320v320H200Z"/></svg>
);

/**
 * Block Registration
 */
registerBlockType(commonMetaProcessing({
    ...metadata,
    icon: textFieldBlockIcon, // Override the icon property with your custom SVG
}), {
	edit: Edit,
	save: Save,
});