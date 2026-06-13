const { __ } = wp.i18n;
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { TextControl, PanelBody, Button, RangeControl } from '@wordpress/components';
import { useEffect, useMemo } from '@wordpress/element';
import { useActive } from '../../blockHelper';
import { capitalizeWords, PREFIX, TEXT_DOMAIN } from '../../utility';
import GeneralPanel from '../../components/generalPanel';
import { useBlockInitialization, getAllBlockWithPrefix, addNewBlock, useDuplicateNameGuard } from '../../blockHelper';
import { useDispatch, useSelect } from '@wordpress/data';
import { v4 as uuidv4 } from 'uuid';

const countStepFields = (innerBlocks = []) => innerBlocks.reduce((count, block) => {
    if (block.name === 'core/columns' || block.name === 'core/column') {
        return count + countStepFields(block.innerBlocks || []);
    }

    if (block.name?.startsWith('kf/') && block.name !== 'kf/step') {
        return count + 1;
    }

    return count;
}, 0);

const formatStepTitle = (value, fallback) => {
    const source = value || fallback;
    return capitalizeWords(source?.toString().replace(/_/g, ' ').trim()) || fallback;
};

export default function Edit({ attributes, setAttributes, clientId }) {
    const { inputLabel, nextBtnLabel, prevBtnLabel, previousWidth, nextWidth } = attributes;
    const { insertBlock } = useDispatch('core/block-editor');

    // Managing the collapse/expand state for the block.
    const { isActivated, toggleActive } = useActive();
    const init = useBlockInitialization(clientId, setAttributes);
    const {
        stepIndex,
        blockIndex,
        stepsSignature,
        fieldCount,
    } = useSelect((select) => {
        const blockEditor = select('core/block-editor');
        const blocks = blockEditor.getBlocks();
        const stepsList = blocks.filter((block) => block.name === 'kf/step');
        const currentStepIndex = stepsList.findIndex((block) => block.clientId === clientId);
        const currentStep = blockEditor.getBlock(clientId);
        const getStepTitle = (block, index) => formatStepTitle(block?.attributes?.inputLabel, `${__('Step', TEXT_DOMAIN)} ${index + 1}`);

        return {
            stepIndex: currentStepIndex,
            blockIndex: blockEditor.getBlockIndex(clientId),
            stepsSignature: stepsList.map((block, index) => `${block.clientId}::${getStepTitle(block, index)}`).join('|'),
            fieldCount: countStepFields(currentStep?.innerBlocks || []),
        };
    }, [clientId]);
    const steps = useMemo(() => {
        if (!stepsSignature) {
            return [];
        }

        return stepsSignature.split('|').filter(Boolean).map((item, index) => {
            const [stepClientId, title] = item.split('::');
            return {
                clientId: stepClientId,
                index,
                title,
            };
        });
    }, [stepsSignature]);

    const blockProps = useBlockProps({
        className: `${PREFIX}-root-block-preview`,
        style: {
            minHeight: isActivated ? '80px' : '180px',
            maxHeight: isActivated ? '0px' : 'none',
        },
    });

    useEffect(() => {
        if (!attributes?.name?.trim()) {
            setAttributes({ name: uuidv4() });
        }
    }, [attributes?.name, setAttributes]);

    useDuplicateNameGuard(clientId, attributes, setAttributes);

    useEffect(() => {
        const updates = {};
        if (!nextBtnLabel) updates.nextBtnLabel = 'Next';
        if (!prevBtnLabel) updates.prevBtnLabel = 'Previous';
        if (!previousWidth) updates.previousWidth = '3';
        if (!nextWidth) updates.nextWidth = '3';
        if (Object.keys(updates).length) setAttributes(updates);
    }, []);

    const totalSteps = steps.length || 1;
    const buttonPreviewColumns = stepIndex > 0 ? `${Math.max(Number(previousWidth || 3), 1)}fr ${Math.max(Number(nextWidth || 3), 1)}fr` : `${Math.max(Number(nextWidth || 3), 1)}fr`;
    const addNewStep = () => {
        insertBlock(addNewBlock(), blockIndex + 1);
    };
    /***********************************Return HTML  */
    return (
			<>
				<InspectorControls>
					<div className={`${PREFIX}-settings-container`}>
						<GeneralPanel
							setAttributes={setAttributes}
							attributes={attributes}
							init={init}
						/>
						{/* Button Properties Section */}
						<PanelBody
							title={__("Button Properties", TEXT_DOMAIN)}
							initialOpen={false}
						>
							<div className={`${PREFIX}-setting`}>
								<TextControl
									label={__("Next", TEXT_DOMAIN)}
									value={nextBtnLabel}
									onChange={(value) => setAttributes({ nextBtnLabel: value })}
								/>
							</div>

							<div className={`${PREFIX}-setting`}>
								<TextControl
									label={__("Previous", TEXT_DOMAIN)}
									value={prevBtnLabel}
									onChange={(value) => setAttributes({ prevBtnLabel: value })}
								/>
							</div>

							<div className={`${PREFIX}-setting`}>
								<RangeControl
									label={__("Previous Width", TEXT_DOMAIN)}
									min={1}
									max={12}
									value={previousWidth}
									onChange={(value) => setAttributes({ previousWidth: value })}
								/>
							</div>

							<div className={`${PREFIX}-setting`}>
								<RangeControl
									label={__("Next Width", TEXT_DOMAIN)}
									min={1}
									max={12}
									value={nextWidth}
									onChange={(value) => setAttributes({ nextWidth: value })}
								/>
							</div>

						</PanelBody>
					</div>
				</InspectorControls>

				<div {...blockProps}>
					{totalSteps > 1 && stepIndex === 0 && (
						<div className={`${PREFIX}-step-flow`} aria-label={__('Step order', TEXT_DOMAIN)}>
							{steps.map((step) => (
								<div
									key={step.clientId}
									className={`${PREFIX}-step-flow-item ${
										step.clientId === clientId
											? `${PREFIX}-step-flow-item-active`
											: step.index < stepIndex
												? `${PREFIX}-step-flow-item-complete`
												: ''
									}`}
								>
									<span className={`${PREFIX}-step-flow-index`}>{step.index + 1}</span>
									<span className={`${PREFIX}-step-flow-label`}>{step.title}</span>
								</div>
							))}
						</div>
					)}

					<div className={`${PREFIX}-step-card-header`}>
						<div className={`${PREFIX}-step-card-meta`}>
							<h3 className={`${PREFIX}-step-card-title`}>{inputLabel}</h3>
						</div>

						<div className={`${PREFIX}-step-card-actions`}>
							<Button
								variant="tertiary"
								onClick={addNewStep}
								className={`${PREFIX}-add-step-button`}
							>
								{__('Add Step', TEXT_DOMAIN)}
							</Button>
							<Button
								variant="tertiary"
								onClick={toggleActive}
								className={`${PREFIX}-expand-collapse-button`}
							>
								{isActivated ? __("Expand", TEXT_DOMAIN) : __("Collapse", TEXT_DOMAIN)}
							</Button>
						</div>
					</div>

					{isActivated && (
						<p className={`${PREFIX}-step-card-summary`}>
							{`${fieldCount} ${fieldCount === 1 ? __('field in this step.', TEXT_DOMAIN) : __('fields in this step.', TEXT_DOMAIN)}`}
						</p>
					)}

					{/* Collapsible Content */}
					{!isActivated && (
						<>
							{/**************Content Area for other fields *****************
							 */}

							<InnerBlocks
								allowedBlocks={getAllBlockWithPrefix()}
								templateLock={false}
								layout={{ type: "grid", columns: 3 }}
								renderAppender={() => <InnerBlocks.ButtonBlockAppender />}
							/>

							{/* Button preview row */}
							<div
								className={`${PREFIX}-step-btn-preview ${stepIndex > 0 ? `${PREFIX}-step-btn-preview--dual` : `${PREFIX}-step-btn-preview--single`}`}
								style={{ gridTemplateColumns: buttonPreviewColumns, pointerEvents: 'none' }}
							>
								{stepIndex > 0 && (
									<Button
										variant="secondary"
										className={`${PREFIX}-step-btn-preview-button ${PREFIX}-step-btn-preview-button-secondary`}
									>
										{prevBtnLabel || __('Previous', TEXT_DOMAIN)}
									</Button>
								)}
								{stepIndex < totalSteps - 1 && (
									<Button
										variant="primary"
										className={`${PREFIX}-step-btn-preview-button ${PREFIX}-step-btn-preview-button-primary`}
									>
										{nextBtnLabel || __('Next', TEXT_DOMAIN)}
									</Button>
								)}
							</div>

						</>
					)}
				</div>
			</>
		);
}
