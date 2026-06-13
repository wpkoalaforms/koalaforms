import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { commonMetaProcessing } from '../../blockHelper';

/**
 * Internal dependencies
 */
import Edit from './edit';
import Save from './save';

/**
 * Block Registration
 */
registerBlockType(commonMetaProcessing(metadata), {
	edit: Edit,
	save: Save,
});
