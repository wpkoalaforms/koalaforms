import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { commonMetaProcessing } from '../../blockHelper';
import textMetadata from '../../blocks/text/block.json';

/**
 * Internal dependencies
 */
import Edit from './edit';
import Save from './save';

/**
 * Custom SVG Icon
 */
const addressBlockIcon = (
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
    <path d="M12 21.25s5.25-4.1 5.25-9.23a5.25 5.25 0 1 0-10.5 0C6.75 17.15 12 21.25 12 21.25Z" stroke="#5f6368" strokeWidth="1.9" strokeLinejoin="round"/>
    <circle cx="12" cy="11.75" r="1.9" fill="#5f6368"/>
</svg>
);


let addressMetatdata    = commonMetaProcessing({...metadata});
addressMetatdata.icon = addressBlockIcon;
/**
 * Block Registration
 */
registerBlockType({...addressMetatdata}, {
	edit: Edit,
	save: Save,
});
