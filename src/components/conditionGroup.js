import React, { useState, useEffect } from 'react';
import { Button, Modal } from '@wordpress/components';
import { getInputBlockNames } from '../blockHelper';
import TextWithSuggestions from './textWithSuggestions';

const operators = ['Equals', 'Not Equals', 'Greater Than', 'Less Than', 'Less Than Equal', 'Greater Than Equal'];

const ConditionGroup = ({ 
    onConditionsChange, 
    initialConditions = [] 
}) => {
    const fields  = getInputBlockNames().map((name) => ({fieldName: name}));
    const fieldSuggestions = getInputBlockNames(true);
    const [conditions, setConditions] = useState([]);
    const [groupOperator, setGroupOperator] = useState('AND');
    const [isModalOpen, setIsModalOpen] = useState(false);

    useEffect(() => {
        setConditions(initialConditions);
    }, [initialConditions]);

    const openModal = () => setIsModalOpen(true);
    const closeModal = () => setIsModalOpen(false);

    const handleConditionChange = (newConditions) => {
        setConditions(newConditions);
        onConditionsChange(newConditions);
    };

    const saveConditions = () => {
        onConditionsChange(conditions);
        closeModal();
    };

    const groupConditions = () => {
        if (conditions.length > 1) {
            const groupedConditions = [
                {
                    groupOperator,
                    conditions: [...conditions],
                },
            ];
            handleConditionChange(groupedConditions);
        }
    };

    const handleValueChange = (value, currentConditions, index) => {
        const updatedConditions = [...currentConditions];
        updatedConditions[index].value = value;
        handleConditionChange(updatedConditions);
    };

    // Recursive function to render conditions and groups
    const renderConditions = (currentConditions) => {
        return currentConditions.map((condition, index) => {
            if (condition.groupOperator) {
                // Render grouped conditions
                return (
                    <div
                        key={index}
                        style={{
                            border: '1px solid #ccc',
                            padding: '10px',
                            marginBottom: '10px',
                        }}
                    >
                        <strong>{condition.groupOperator}</strong>
                        <div style={{ marginLeft: '15px' }}>
                            {renderConditions(condition.conditions)}
                        </div>
                    </div>
                );
            }

            // Render individual conditions
            return (
                <div key={index} style={{ marginBottom: '10px' }}>
                    <select
                        value={condition.fieldName || ''}
                        onChange={(e) => {
                            const updatedConditions = [...currentConditions];
                            updatedConditions[index].fieldName = e.target.value;
                            handleConditionChange(updatedConditions);
                        }}
                    >
                        <option value="">Select Field</option>
                        {fields.map((field) => (
                            <option key={field.fieldName} value={field.fieldName}>
                                {field.fieldName}
                            </option>
                        ))}
                    </select>
                    <select
                        value={condition.operator || ''}
                        onChange={(e) => {
                            const updatedConditions = [...currentConditions];
                            updatedConditions[index].operator = e.target.value;
                            handleConditionChange(updatedConditions);
                        }}
                    >
                        <option value="">Select Operator</option>
                        {operators.map((operator) => (
                            <option key={operator} value={operator}>
                                {operator}
                            </option>
                        ))}
                    </select>
                    
                    <TextWithSuggestions
                        value={condition.value}
                        suggestions={fieldSuggestions}
                        placeholder="Start typing..."
                        onValueChange={(value) => handleValueChange(value, currentConditions, index)} // Pass the callback
                    />
                    <Button
                        variant="secondary"
                        onClick={() => {
                            const updatedConditions = currentConditions.filter(
                                (_, i) => i !== index
                            );
                            handleConditionChange(updatedConditions);
                        }}
                    >
                        Remove
                    </Button>
                </div>
            );
        });
    };

    return (
        <>
            <Button variant="secondary" onClick={openModal}>
                Add/Edit Conditions
            </Button>

            {isModalOpen && (
                <Modal title="Define Conditions" onRequestClose={closeModal}>
                    <div>
                        <div style={{ marginBottom: '15px' }}>
                            <label>
                                Logical Operator:&nbsp;
                                <select
                                    value={groupOperator}
                                    onChange={(e) => setGroupOperator(e.target.value)}
                                >
                                    <option value="AND">AND</option>
                                    <option value="OR">OR</option>
                                </select>
                            </label>
                        </div>

                        <div>{renderConditions(conditions)}</div>

                        <div style={{ marginTop: '20px' }}>
                            <Button
                                variant="secondary"
                                onClick={() => {
                                    handleConditionChange([
                                        ...conditions,
                                        { fieldName: '', operator: '', value: '' },
                                    ]);
                                }}
                            >
                                Add Condition
                            </Button>

                            <Button
                                variant="secondary"
                                onClick={groupConditions}
                                disabled={conditions.length < 2}
                                style={{ marginLeft: '10px' }}
                            >
                                Group Conditions
                            </Button>
                            

                            <div style={{ marginTop: '20px' }}>
                                <Button variant="primary" onClick={saveConditions}>
                                    Save
                                </Button>
                                <Button variant="secondary" onClick={closeModal}>
                                    Close
                                </Button>
                            </div>
                        </div>
                    </div>
                </Modal>
            )}
        </>
    );
};

export default ConditionGroup;