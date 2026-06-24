const { __ } = wp.i18n;
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { populateDefaultAttrs, ADDRESS_BLOCK_TYPES, useActive } from '../../blockHelper';
import BlockContainer from '../../components/blockContainer';
import { TEXT_DOMAIN, PREFIX } from '../../utility';

const ADDRESS_FIELDS = [
    { label: "Address Line 1", value: "addressLine1" },
    { label: "Address Line 2", value: "addressLine2" },
    { label: "City", value: "city" },
    { label: "State", value: "state" },
    { label: "Country", value: "country" },
    { label: "Zip Code", value: "zipcode" },
];

export default function Edit({ attributes, setAttributes, clientId }) {
    const { hiddenAddressFields, stateFieldType, inputLabel } = attributes;
    const blockProps = useBlockProps();
    const { isActivated, toggleActive } = useActive();
    const { selectBlock } = useDispatch( 'core/block-editor' );
    const isSelected = useSelect( ( select ) => select( 'core/block-editor' ).isBlockSelected( clientId ) );

    setAttributes({ addressFieldTypes: ADDRESS_FIELDS });
    populateDefaultAttrs(attributes, setAttributes);

    const hiddenLine1   = hiddenAddressFields.includes('addressLine1');
    const hiddenLine2   = hiddenAddressFields.includes('addressLine2');
    const hiddenCountry = hiddenAddressFields.includes('country');
    const hiddenZipcode = hiddenAddressFields.includes('zipcode');
    const hiddenState   = hiddenAddressFields.includes('state');
    const hiddenCity    = hiddenAddressFields.includes('city');

    // Update state field type based on conditions
    useEffect(() => {
        if (hiddenCountry && !hiddenState) {
            setAttributes({ stateFieldType: 'text' }); // Change to Text Field
        } else {
            setAttributes({ stateFieldType: 'select' }); // Keep as Dropdown
        }
    }, [hiddenCountry, hiddenState]);

    return (
        <div {...blockProps}>
            <div
                className={ `kf-address-container${ isSelected ? ' is-selected' : '' }` }
                onMouseDown={ ( e ) => {
                    if ( e.target === e.currentTarget ) {
                        e.preventDefault();
                        e.stopPropagation();
                        selectBlock( clientId );
                    }
                } }
            >
            <BlockContainer blockProps={blockProps} attributes={attributes} clientId={clientId} setAttributes={setAttributes}>
                <div className="kf-address-collapse-toggle">
                    <Button
                        variant="tertiary"
                        onClick={toggleActive}
                        className={`${PREFIX}-expand-collapse-button`}
                    >
                        {isActivated ? __('Expand', TEXT_DOMAIN) : __('Collapse', TEXT_DOMAIN)}
                    </Button>
                </div>

                {!isActivated && (
                    <InnerBlocks
                        allowedBlocks={ADDRESS_BLOCK_TYPES}
                        renderAppender={() => <InnerBlocks.ButtonBlockAppender />}
                        template={[
                            // First Row: Two Columns (Address Line 1 & Address Line 2)
                            ['core/columns', {}, [
                                ['core/column', {}, [['kf/text', { inputLabel: 'Address Line 1', subtype: 'addressLine1', hidden: hiddenLine1 }]]],
                                ['core/column', {}, [['kf/text', { inputLabel: 'Address Line 2', subtype: 'addressLine2', hidden: hiddenLine2 }]]]
                            ]],
                            // Second Row: Two Columns (Country & Zip Code)
                            ['core/columns', {}, [
                                ['core/column', {}, [['kf/select', { inputLabel: 'Country', subtype: 'country', hidden: hiddenCountry }]]],
                                ['core/column', {}, [['kf/text', { inputLabel: 'Zip Code', subtype: 'zipcode', hidden: hiddenZipcode }]]]
                            ]],
                            // Third Row: Two Columns (City & State)
                            ['core/columns', {}, [
                                ['core/column', {}, [['kf/text', { inputLabel: 'City', subtype: 'city', hidden: hiddenCity }]]],
                                ['core/column', {}, [
                                    [
                                        stateFieldType === 'select' ? 'kf/select' : 'kf/text',
                                        { inputLabel: 'State', subtype: 'state', hidden: hiddenState }
                                    ]
                                ]]
                            ]]
                        ]}
                        templateLock={false}
                    />
                )}
            </BlockContainer>
        </div>
        </div>
    );
}
