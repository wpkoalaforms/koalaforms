<template>
    <div class="kf-settings-field">
        <label v-if="label" class="kf-settings-field-label">
            {{ label }}
        </label>

        <p v-if="help" class="kf-settings-field-help">
            {{ help }}
        </p>

        <select
            v-if="type === 'select'"
            class="kf-settings-input"
            :name="name || undefined"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <div v-else-if="type === 'radio'" class="kf-settings-radio-group">
            <label v-for="option in options" :key="option.value" class="kf-settings-radio-option">
                <input
                    type="radio"
                    :name="name"
                    :value="option.value"
                    :checked="modelValue === option.value"
                    @change="$emit('update:modelValue', option.value)"
                />
                <span>{{ option.label }}</span>
            </label>
        </div>

        <label v-else-if="type === 'toggle'" class="kf-settings-toggle">
            <input
                type="checkbox"
                :checked="Boolean(modelValue)"
                @change="$emit('update:modelValue', $event.target.checked)"
            />
            <span class="kf-settings-toggle-track" aria-hidden="true"></span>
            <span class="kf-settings-toggle-text">{{ toggleLabel }}</span>
        </label>

        <textarea
            v-else-if="type === 'textarea'"
            class="kf-settings-input kf-settings-textarea"
            :name="name || undefined"
            :rows="rows"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
        />

        <input
            v-else
            class="kf-settings-input"
            :name="name || undefined"
            :type="type"
            :value="modelValue"
            :min="min"
            :max="max"
            :step="step"
            :disabled="disabled"
            :placeholder="placeholder || undefined"
            @input="$emit('update:modelValue', type === 'number' ? normalizeNumber($event.target.value) : $event.target.value)"
        />
    </div>
</template>

<script>
export default {
    name: 'SettingsField',
    props: {
        name: {
            type: String,
            default: '',
        },
        label: {
            type: String,
            default: '',
        },
        help: {
            type: String,
            default: '',
        },
        type: {
            type: String,
            default: 'text',
        },
        modelValue: {
            type: [String, Number, Boolean],
            default: '',
        },
        options: {
            type: Array,
            default: () => [],
        },
        toggleLabel: {
            type: String,
            default: '',
        },
        rows: {
            type: Number,
            default: 4,
        },
        min: {
            type: [Number, String],
            default: null,
        },
        max: {
            type: [Number, String],
            default: null,
        },
        step: {
            type: [Number, String],
            default: null,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        placeholder: {
            type: String,
            default: '',
        },
    },
    emits: ['update:modelValue'],
    methods: {
        normalizeNumber(value) {
            if (value === '' || value === null || typeof value === 'undefined') {
                return '';
            }

            const parsed = Number.parseFloat(value);
            return Number.isNaN(parsed) ? '' : parsed;
        },
    },
};
</script>

<style scoped>
.kf-settings-field {
    display: grid;
    gap: 6px;
}

.kf-settings-field-label {
    color: var(--kf-brand-ink, #0f172a);
    font-size: 12px;
    font-weight: 600;
}

.kf-settings-field-help {
    color: #5b6b82;
    font-size: 11px;
    line-height: 1.5;
    margin: 0;
}

.kf-settings-input {
    background: rgba(255, 255, 255, 0.98);
    border: 1px solid rgba(15, 23, 42, 0.12);
    border-radius: 10px;
    color: var(--kf-brand-ink, #0f172a);
    min-height: 38px;
    padding: 7px 10px;
    width: 100%;
    transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}

.kf-settings-input:focus {
    border-color: var(--kf-brand-primary, #3858e9);
    box-shadow: 0 0 0 3px rgba(56, 88, 233, 0.15);
    outline: none;
}

.kf-settings-input:disabled {
    background: rgba(248, 250, 252, 0.8);
    color: #94a3b8;
    cursor: not-allowed;
    opacity: 0.8;
}

.kf-settings-textarea {
    min-height: 96px;
    resize: vertical;
}

.kf-settings-radio-group {
    display: grid;
    gap: 10px;
}

.kf-settings-radio-option {
    align-items: center;
    background: rgba(248, 250, 252, 0.92);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 10px;
    display: flex;
    gap: 10px;
    padding: 9px 10px;
}

.kf-settings-toggle {
    align-items: center;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 12px;
    display: flex;
    gap: 10px;
    padding: 10px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.72);
}

.kf-settings-toggle input {
    height: 0;
    opacity: 0;
    position: absolute;
    width: 0;
}

.kf-settings-toggle-track {
    background: #cbd5e1;
    border-radius: 999px;
    flex: 0 0 auto;
    height: 22px;
    position: relative;
    width: 40px;
}

.kf-settings-toggle-track::after {
    background: #ffffff;
    border-radius: 999px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
    content: '';
    height: 16px;
    left: 3px;
    position: absolute;
    top: 3px;
    transition: transform 0.18s ease;
    width: 16px;
}

.kf-settings-toggle input:checked + .kf-settings-toggle-track {
    background: linear-gradient(135deg, var(--kf-brand-primary, #3858e9), var(--kf-brand-secondary, #5670ff));
}

.kf-settings-toggle input:checked + .kf-settings-toggle-track::after {
    transform: translateX(18px);
}

.kf-settings-toggle-text {
    color: var(--kf-brand-ink, #0f172a);
    font-size: 12px;
    font-weight: 600;
}
</style>
