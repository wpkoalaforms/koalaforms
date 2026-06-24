<template>
    <div class="kf-modal-overlay" @click.self="$emit('close')">
        <div class="kf-modal">
            <div class="kf-modal__header">
                <h3>Add Chart</h3>
                <button class="kf-modal__close" @click="$emit('close')">&times;</button>
            </div>

            <div class="kf-modal__body">
                <label class="kf-modal__label">
                    Chart Type
                    <select v-model="form.type" class="kf-modal__select">
                        <option value="submissionsOverTime">Submissions Over Time</option>
                        <option value="stageDistribution">Stage Distribution</option>
                        <option value="browserBreakdown">Browser Breakdown</option>
                        <option value="fieldDistribution">Field Distribution</option>
                    </select>
                </label>

                <label v-if="form.type === 'fieldDistribution'" class="kf-modal__label">
                    Field
                    <select v-model="form.field_key" class="kf-modal__select">
                        <option value="">— Select a field —</option>
                        <option v-for="f in fields" :key="f.key" :value="f.key">{{ f.label }}</option>
                    </select>
                </label>

                <label class="kf-modal__label">
                    Date Range
                    <select v-model.number="form.days" class="kf-modal__select">
                        <option :value="7">Last 7 days</option>
                        <option :value="30">Last 30 days</option>
                        <option :value="60">Last 60 days</option>
                        <option :value="90">Last 90 days</option>
                    </select>
                </label>
            </div>

            <div class="kf-modal__footer">
                <button class="button" @click="$emit('close')">Cancel</button>
                <button class="button button-primary" :disabled="!isValid" @click="confirm">Add Chart</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AddChartModal',
    emits: ['add', 'close'],
    props: {
        fields: { type: Array, default: () => [] },
    },
    data() {
        return {
            form: {
                type:      'submissionsOverTime',
                field_key: '',
                days:      30,
            },
        };
    },
    computed: {
        isValid() {
            if (this.form.type === 'fieldDistribution') {
                return !!this.form.field_key;
            }
            return true;
        },
        selectedField() {
            return this.fields.find(f => f.key === this.form.field_key) || null;
        },
    },
    methods: {
        confirm() {
            if (!this.isValid) return;
            const config = {
                id:   'c-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
                type: this.form.type,
                days: this.form.days,
            };
            if (this.form.type === 'fieldDistribution') {
                config.field_key   = this.form.field_key;
                config.field_label = this.selectedField?.label || this.form.field_key;
            }
            this.$emit('add', config);
        },
    },
};
</script>

<style>
.kf-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.45);
    z-index: 100000;
}
.kf-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 10px;
    width: 420px;
    max-width: 95vw;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    z-index: 100001;
}
.kf-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.kf-modal__header h3 { margin: 0; font-size: 16px; }
.kf-modal__close {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 20px;
    color: #6b7280;
    line-height: 1;
}
.kf-modal__close:hover { color: #111; }
.kf-modal__body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.kf-modal__label {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}
.kf-modal__select {
    font-size: 13px;
    padding: 6px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #fff;
    width: 100%;
}
.kf-modal__footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 14px 20px;
    border-top: 1px solid #e5e7eb;
}
</style>
