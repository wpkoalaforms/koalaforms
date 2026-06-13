import { useState, useCallback, memo, useEffect } from '@wordpress/element';
import { Button, Modal, TextControl, CheckboxControl } from '@wordpress/components';

// --- Helpers ---

const optionsToBulkText = (options) =>
    options.map((opt) => {
        let line = opt.value !== opt.label ? `${opt.label} | ${opt.value}` : opt.label;
        if (opt.description) line += ` | ${opt.description}`;
        return line;
    }).join('\n');

const parseBulkText = (text, existingOptions) => {
    const existingDefaults = new Set(
        existingOptions.filter((o) => o.default).map((o) => `${o.label}||${o.value}`)
    );
    return text
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line) => {
            const parts = line.split('|').map((p) => p.trim());
            const label = parts[0] || '';
            const value = parts[1] !== undefined ? parts[1] : parts[0];
            const description = parts[2] || '';
            return { label, value, description, default: existingDefaults.has(`${label}||${value}`) };
        });
};

// --- Visual tab: single inline-editable row ---

const VisualRow = memo(({ option, index, isFirst, isLast, onMoveUp, onMoveDown, onUpdate, onRemove }) => {
    const [isEditing, setIsEditing] = useState(false);
    const [editLabel, setEditLabel] = useState('');
    const [editValue, setEditValue] = useState('');
    const [editDescription, setEditDescription] = useState('');
    const [editDefault, setEditDefault] = useState(false);

    const startEdit = useCallback(() => {
        setEditLabel(option.label);
        setEditValue(option.value);
        setEditDescription(option.description || '');
        setEditDefault(!!option.default);
        setIsEditing(true);
    }, [option]);

    const handleSave = useCallback(() => {
        if (!editLabel.trim()) return;
        onUpdate(index, { label: editLabel.trim(), value: editValue.trim(), description: editDescription.trim(), default: editDefault });
        setIsEditing(false);
    }, [editLabel, editValue, editDescription, editDefault, index, onUpdate]);

    const handleKeyDown = useCallback((e) => {
        if (e.key === 'Enter') handleSave();
        if (e.key === 'Escape') setIsEditing(false);
    }, [handleSave]);

    if (isEditing) {
        return (
            <li style={{ padding: '10px', background: '#f6f7f7', borderRadius: '4px', marginBottom: '6px', border: '1px solid #ddd' }}>
                <div style={{ display: 'flex', gap: '8px', marginBottom: '8px' }}>
                    <div style={{ flex: 1 }}>
                        <TextControl label="Label" value={editLabel} onChange={setEditLabel} onKeyDown={handleKeyDown} __nextHasNoMarginBottom />
                    </div>
                    <div style={{ flex: 1 }}>
                        <TextControl label="Value" value={editValue} onChange={setEditValue} onKeyDown={handleKeyDown} __nextHasNoMarginBottom />
                    </div>
                </div>
                <div style={{ marginBottom: '8px' }}>
                    <TextControl label="Description (optional)" value={editDescription} onChange={setEditDescription} placeholder="Short description shown to users" __nextHasNoMarginBottom />
                </div>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <CheckboxControl label="Set as Default" checked={editDefault} onChange={setEditDefault} __nextHasNoMarginBottom />
                    <div style={{ display: 'flex', gap: '6px' }}>
                        <Button variant="primary" isSmall onClick={handleSave}>Save</Button>
                        <Button variant="secondary" isSmall onClick={() => setIsEditing(false)}>Cancel</Button>
                    </div>
                </div>
            </li>
        );
    }

    return (
        <li style={{ display: 'flex', alignItems: 'center', gap: '6px', padding: '6px 4px', borderBottom: '1px solid #f0f0f0' }}>
            <span style={{ flex: 1, fontSize: '13px', overflow: 'hidden' }}>
                <strong>{option.label}</strong>
                {option.value && option.value !== option.label && (
                    <span style={{ color: '#888' }}> : {option.value}</span>
                )}
                {option.default && <span style={{ color: '#007cba', marginLeft: '6px' }}>(Default)</span>}
                {option.description && (
                    <span style={{ display: 'block', color: '#999', fontSize: '12px', marginTop: '1px' }}>{option.description}</span>
                )}
            </span>
            <Button variant="secondary" isSmall disabled={isFirst} onClick={() => onMoveUp(index)} aria-label="Move up">▲</Button>
            <Button variant="secondary" isSmall disabled={isLast} onClick={() => onMoveDown(index)} aria-label="Move down">▼</Button>
            <Button variant="secondary" isSmall onClick={startEdit}>Edit</Button>
            <Button variant="secondary" isSmall isDestructive onClick={() => onRemove(index)}>Remove</Button>
        </li>
    );
});

// --- Options editor modal ---

const OptionsEditorModal = ({ options, onChange, singleDefault, onClose }) => {
    const [activeTab, setActiveTab] = useState('visual');
    const [localOptions, setLocalOptions] = useState(() => [...options]);
    const [bulkText, setBulkText] = useState('');
    const [newLabel, setNewLabel] = useState('');
    const [newValue, setNewValue] = useState('');

    // Sync textarea when switching to bulk tab
    useEffect(() => {
        if (activeTab === 'bulk') {
            setBulkText(optionsToBulkText(localOptions));
        }
    }, [activeTab]);

    const switchTab = useCallback((tab) => {
        if (tab === 'visual' && activeTab === 'bulk') {
            setLocalOptions(parseBulkText(bulkText, localOptions));
        }
        setActiveTab(tab);
    }, [activeTab, bulkText, localOptions]);

    const handleSave = useCallback(() => {
        const finalOptions = activeTab === 'bulk'
            ? parseBulkText(bulkText, localOptions)
            : localOptions;
        onChange(finalOptions);
        onClose();
    }, [activeTab, bulkText, localOptions, onChange, onClose]);

    // Visual tab: add option
    const addOption = useCallback(() => {
        if (!newLabel.trim()) return;
        const option = { label: newLabel.trim(), value: newValue.trim() || newLabel.trim(), default: false };
        setLocalOptions((prev) => [...prev, option]);
        setNewLabel('');
        setNewValue('');
    }, [newLabel, newValue]);

    const handleAddKeyDown = useCallback((e) => {
        if (e.key === 'Enter') addOption();
    }, [addOption]);

    // Visual tab: update existing option
    const updateOption = useCallback((index, updated) => {
        setLocalOptions((prev) => {
            let next = prev.map((opt, i) => (i === index ? updated : opt));
            if (singleDefault && updated.default) {
                next = next.map((opt, i) => ({ ...opt, default: i === index }));
            }
            return next;
        });
    }, [singleDefault]);

    const removeOption = useCallback((index) => {
        setLocalOptions((prev) => prev.filter((_, i) => i !== index));
    }, []);

    const moveOption = useCallback((index, direction) => {
        setLocalOptions((prev) => {
            const swapIndex = index + direction;
            if (swapIndex < 0 || swapIndex >= prev.length) return prev;
            const next = [...prev];
            [next[index], next[swapIndex]] = [next[swapIndex], next[index]];
            return next;
        });
    }, []);

    const tabStyle = (tab) => ({
        padding: '8px 18px',
        border: 'none',
        background: 'none',
        cursor: 'pointer',
        fontWeight: activeTab === tab ? '600' : '400',
        borderBottom: activeTab === tab ? '2px solid #007cba' : '2px solid transparent',
        color: activeTab === tab ? '#007cba' : '#555',
        marginBottom: '-1px',
        fontSize: '13px',
    });

    return (
        <Modal title="Edit Options" size="large" onRequestClose={onClose}>
            {/* Tab bar */}
            <div style={{ display: 'flex', borderBottom: '1px solid #ddd', marginBottom: '20px' }}>
                <button style={tabStyle('visual')} onClick={() => switchTab('visual')}>Visual</button>
                <button style={tabStyle('bulk')} onClick={() => switchTab('bulk')}>Bulk Edit</button>
            </div>

            {/* Visual tab */}
            {activeTab === 'visual' && (
                <div>
                    <div style={{ display: 'flex', gap: '8px', alignItems: 'flex-end', marginBottom: '16px' }}>
                        <div style={{ flex: 1 }}>
                            <TextControl
                                label="Label"
                                value={newLabel}
                                onChange={setNewLabel}
                                onKeyDown={handleAddKeyDown}
                                placeholder="e.g. Technology"
                                __nextHasNoMarginBottom
                            />
                        </div>
                        <div style={{ flex: 1 }}>
                            <TextControl
                                label="Value (optional)"
                                value={newValue}
                                onChange={setNewValue}
                                onKeyDown={handleAddKeyDown}
                                placeholder="e.g. technology"
                                __nextHasNoMarginBottom
                            />
                        </div>
                        <Button variant="primary" onClick={addOption} style={{ flexShrink: 0 }}>
                            + Add
                        </Button>
                    </div>

                    {localOptions.length === 0 ? (
                        <p style={{ color: '#888', textAlign: 'center', padding: '24px 0' }}>No options yet. Add one above.</p>
                    ) : (
                        <ul style={{ listStyle: 'none', padding: 0, margin: 0, maxHeight: '400px', overflowY: 'auto' }}>
                            {localOptions.map((option, index) => (
                                <VisualRow
                                    key={`${index}-${option.label}`}
                                    option={option}
                                    index={index}
                                    isFirst={index === 0}
                                    isLast={index === localOptions.length - 1}
                                    onMoveUp={(i) => moveOption(i, -1)}
                                    onMoveDown={(i) => moveOption(i, 1)}
                                    onUpdate={updateOption}
                                    onRemove={removeOption}
                                />
                            ))}
                        </ul>
                    )}
                </div>
            )}

            {/* Bulk edit tab */}
            {activeTab === 'bulk' && (
                <div>
                    <p style={{ color: '#666', fontSize: '12px', marginBottom: '8px' }}>
                        One option per line. Format: <code>Label | value | description</code>. Value and description are optional.
                    </p>
                    <textarea
                        value={bulkText}
                        onChange={(e) => setBulkText(e.target.value)}
                        rows={18}
                        placeholder={'Select your industry |\nLLC | llc | Limited liability, flexible structure\nSole Proprietor | sole_proprietor'}
                        style={{
                            width: '100%',
                            fontFamily: 'monospace',
                            fontSize: '13px',
                            padding: '10px',
                            boxSizing: 'border-box',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            resize: 'vertical',
                        }}
                    />
                    <p style={{ color: '#999', fontSize: '11px', marginTop: '4px' }}>
                        Note: switching to Visual or saving will apply these changes. Default selections must be set in Visual mode.
                    </p>
                </div>
            )}

            {/* Footer */}
            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px', marginTop: '24px', paddingTop: '14px', borderTop: '1px solid #eee' }}>
                <Button variant="secondary" onClick={onClose}>Cancel</Button>
                <Button variant="primary" onClick={handleSave}>Save Options</Button>
            </div>
        </Modal>
    );
};

// --- Sidebar summary (what's shown in the inspector panel) ---

const OptionList = ({ options, onChange, singleDefault = false }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);

    return (
        <div>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '6px' }}>
                <span style={{ fontSize: '12px', color: '#757575' }}>
                    {options.length === 0 ? 'No options configured' : `${options.length} option${options.length !== 1 ? 's' : ''} configured`}
                </span>
                <Button variant="secondary" isSmall onClick={() => setIsModalOpen(true)}>
                    Edit Options ↗
                </Button>
            </div>

            {options.length > 0 && (
                <ul style={{ listStyle: 'none', padding: 0, margin: 0, fontSize: '12px', color: '#555' }}>
                    {options.slice(0, 3).map((opt, i) => (
                        <li key={i} style={{ padding: '2px 0', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                            • {opt.label}{opt.default ? <span style={{ color: '#007cba' }}> (Default)</span> : ''}
                        </li>
                    ))}
                    {options.length > 3 && (
                        <li style={{ color: '#999', fontSize: '11px', marginTop: '2px' }}>
                            +{options.length - 3} more…
                        </li>
                    )}
                </ul>
            )}

            {isModalOpen && (
                <OptionsEditorModal
                    options={options}
                    onChange={onChange}
                    singleDefault={singleDefault}
                    onClose={() => setIsModalOpen(false)}
                />
            )}
        </div>
    );
};

export default OptionList;
