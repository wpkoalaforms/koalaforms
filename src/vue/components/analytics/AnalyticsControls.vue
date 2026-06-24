<template>
    <div class="kf-analytics-controls">
        <div class="kf-analytics-controls__left">
            <label class="kf-analytics-controls__label">
                Form
                <select v-model.number="localFormId" class="kf-analytics-controls__select">
                    <option :value="null" disabled>— Select a form —</option>
                    <option v-for="form in forms" :key="form.id" :value="form.id">{{ form.title }}</option>
                </select>
            </label>
        </div>
        <div class="kf-analytics-controls__right">
            <span v-if="isSaving" class="kf-analytics-controls__saving">Saving…</span>
            <button
                class="button button-primary"
                :disabled="!localFormId"
                @click="$emit('open-add-modal')"
            >+ Add Chart</button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AnalyticsControls',
    emits: ['update:selectedFormId', 'open-add-modal'],
    props: {
        forms:          { type: Array,   default: () => [] },
        selectedFormId: { type: Number,  default: null },
        isSaving:       { type: Boolean, default: false },
    },
    computed: {
        localFormId: {
            get() { return this.selectedFormId; },
            set(val) { this.$emit('update:selectedFormId', val); },
        },
    },
};
</script>

<style>
.kf-analytics-controls {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.kf-analytics-controls__left { display: flex; align-items: flex-end; gap: 12px; }
.kf-analytics-controls__right { display: flex; align-items: center; gap: 12px; }
.kf-analytics-controls__label {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}
.kf-analytics-controls__select {
    font-size: 13px;
    padding: 6px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #fff;
    min-width: 220px;
}
.kf-analytics-controls__saving { font-size: 12px; color: #6b7280; }
</style>
