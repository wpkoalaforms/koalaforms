import { PREFIX, TEXT_DOMAIN } from '../utility';
import { TextControl, PanelBody ,  ToolbarGroup, ComboboxControl, Button,  Tooltip } from '@wordpress/components';
import ConditionGroup from './conditionGroup';

const { __ } = wp.i18n;

const ConditionapPanel = ({ setAttributes, attributes }) => {
    const { conditions } = attributes;
    return (
        <PanelBody title={__('Conditional Properties', TEXT_DOMAIN)} initialOpen={true}>
            <div className={`${PREFIX}-setting`}>
                <ConditionGroup
                    onConditionsChange={(updatedConditions) => setAttributes({ conditions: updatedConditions })}
                    initialConditions={conditions}
                />
            </div>
        </PanelBody>
    );
}

export default ConditionapPanel;