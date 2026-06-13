import React, { useState,useEffect } from 'react';

const TextWithSuggestions = ({
    suggestions = [],
    placeholder = "Enter Value",
    value="",
    onValueChange,
}) => {
    const [inputValue, setInputValue] = useState("");
    const [filteredSuggestions, setFilteredSuggestions] = useState([]);
    const [isSuggestionBoxVisible, setIsSuggestionBoxVisible] = useState(false);

    useEffect(() => {
        // Update the inputValue if the `value` prop changes
        setInputValue(value);
    }, [value]);
    
    const handleInputChange = (e) => {
        const value = e.target.value;
        setInputValue(value);

        // Notify the parent of the input change
        if (onValueChange) {
            onValueChange(value);
        }

        // Filter suggestions based on the input value
        if (value) {
            const matchingSuggestions = suggestions.filter((suggestion) =>
                suggestion.toLowerCase().includes(value.toLowerCase())
            );
            setFilteredSuggestions(matchingSuggestions);
            setIsSuggestionBoxVisible(matchingSuggestions.length > 0);
        } else {
            setIsSuggestionBoxVisible(false);
        }
    };

    const handleSuggestionClick = (suggestion) => {
        setInputValue(suggestion); // Update the input value
        setIsSuggestionBoxVisible(false); // Hide the suggestions box

        // Notify the parent of the selection
        if (onValueChange) {
            onValueChange(suggestion);
        }
    };

    return (
        <span style={{ position: "relative" }}>
            {/* Text Input */}
            <input
                type="text"
                value={inputValue}
                onChange={handleInputChange}
                placeholder={placeholder}
            />

            {/* Suggestions Dropdown */}
            {isSuggestionBoxVisible && (
                <div
                    style={{
                        position: "absolute",
                        top: "100%",
                        left: 0,
                        width: "100%",
                        background: "#fff",
                        border: "1px solid #ccc",
                        borderRadius: "4px",
                        zIndex: 1000,
                        maxHeight: "150px",
                        overflowY: "auto",
                    }}
                >
                    {filteredSuggestions.map((suggestion, index) => (
                        <div
                            key={index}
                            onClick={() => handleSuggestionClick(suggestion)}
                            style={{
                                padding: "8px",
                                cursor: "pointer",
                                borderBottom: "1px solid #eee",
                            }}
                            onMouseEnter={(e) =>
                                (e.target.style.backgroundColor = "#f0f0f0")
                            }
                            onMouseLeave={(e) =>
                                (e.target.style.backgroundColor = "#fff")
                            }
                        >
                            {suggestion}
                        </div>
                    ))}
                </div>
            )}
        </span>
    );
};

export default TextWithSuggestions;