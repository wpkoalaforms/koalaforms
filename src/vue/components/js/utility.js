const util = {
    isInput : (blockName) => {
            const inputElements = ['kf/text','kf/email','kf/number','kf/url','kf/date','kf/checkbox'];
            return inputElements.includes(blockName);
    },
    isChoiceInput : (blockName) => {
        const optionElements = [ 'kf/radio', 'kf/select'];
        return optionElements.includes(blockName);
    },
    isTextarea: (blockName) =>{
        const elements = [ 'kf/textarea'];
        return elements.includes(blockName);
    },
    isMultiChoiceInput : (blockName) => {
        const optionElements = ['kf/multiselect'];
        return optionElements.includes(blockName);
    },
    isHtml : (blockName) => {
        const inputElements = ['kf/disclosure', 'core/paragraph'];
        return inputElements.includes(blockName);
    },
    elementType: (blockName) => {
        return blockName?.replace("kf/","")?.replace('core/','');
    },
    handleAsync: (promise) => {
        return  promise
                .then(response => [response.data, null])
                .catch(error => [null, error]);
    },
    prepareForm: (data) => {
      let formData = new FormData;
      Object.keys(data).forEach((key) => {
        if (data[key] && typeof data[key] === 'object' && !(data[key] instanceof File)) {
            formData.append(key, JSON.stringify(data[key]));
            return;
        } 
        formData.append(key, data[key])
      });
      return formData;
    },
    isGroupElement: (blockName) => {
        const optionElements = ['core/columns'];
        return optionElements.includes(blockName);
    },
    isAddressElement: (blockName) => {
        const optionElements = ['kf/address'];
        return optionElements.includes(blockName);
    },
    /**
     * Extracts the 'style' and 'class' attributes from an HTML string.
     *
     * @param {string} htmlString - The HTML string to parse.
     * @returns {Object} - An object containing the 'style' and 'class' values.
     */
    extractAttributes: (htmlString) => {
        // Regular expressions for class and style attributes
        const classRegex = /class="([^"]*)"/;
        const styleRegex = /style="([^"]*)"/;
    
        const classMatch = htmlString.match(classRegex);
        const styleMatch = htmlString.match(styleRegex);
    
        // Return extracted values or null if not found
        return {
        cls: classMatch ? classMatch[1] : null,
        style: styleMatch ? styleMatch[1] : null,
        };
    },
    flattenInnerBlocks: (node) => {
        const result = [];
    
        function traverse(block) {
            // If block has no innerBlocks or empty innerBlocks, it's a leaf node
            if (!block.innerBlocks || block.innerBlocks.length === 0) {
                // Only include blocks with meaningful content (kf/* blocks)
                if (block.blockName && block.blockName.startsWith('kf/')) {
                    result.push({
                        blockName: block.blockName,
                        attrs: block.attrs
                    });
                }
                return;
            }
            
            // Recursively traverse each inner block
            block.innerBlocks.forEach(innerBlock => {
                traverse(innerBlock);
            });
        }
        
        traverse(node);
        return result;
    },
    initializeModelValue: (config, modelValue, hasOptions = false) => {
        if(Array.isArray(modelValue)){
            if(modelValue.length > 0)
                return modelValue;

            const defaultOptions = config.attrs.options.filter(option => option.default).map((option) => option.value);
            return defaultOptions;
        } 
        else if(modelValue !== undefined){
            return modelValue;
        }

        if(hasOptions){
            const defaultOption = config.attrs.options.find(option => option.default);
            if(defaultOption){
                return defaultOption.value;
            }
        }

        // Look for default/defaultCBValue
        return config?.attrs?.defaultCBValue || config?.attrs?.defaultValue;

    }
   
}

export default util;