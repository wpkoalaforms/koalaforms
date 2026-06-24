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
const stepBlockIcon = (
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
    <circle cx="6" cy="12" r="2.2" fill="#5f6368" />
    <circle cx="12" cy="12" r="2.2" fill="#5f6368" />
    <circle cx="18" cy="12" r="2.2" fill="#5f6368" />
    <path d="M8.4 12h1.4M13.4 12h1.4" stroke="#5f6368" strokeWidth="1.8" strokeLinecap="round" />
</svg>
);

/**
 * Block Registration
 */
registerBlockType(commonMetaProcessing({
    ...metadata,
    icon: stepBlockIcon,
}), {
	edit: Edit,
	save: Save,
});
