// import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
    const { content } = attributes;
    return <RichText.Content tagName="p" value={content} />;
}
