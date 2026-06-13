import { InnerBlocks } from '@wordpress/block-editor';

export default function save({ attributes }) {
    return (
        <div>
            <InnerBlocks.Content />
        </div>
    );
}
