<template>
    <section class="kf-settings-section">
        <div class="kf-settings-section-header">
            <div>
                <h2 class="kf-settings-section-title">{{ title }}</h2>
                <p v-if="description" class="kf-settings-section-description">
                    {{ description }}
                </p>
            </div>

            <slot name="header-action" />
        </div>

        <div class="kf-settings-section-body">
            <slot />
        </div>
    </section>
</template>

<script>
export default {
    name: 'SettingsSection',
    props: {
        title: {
            type: String,
            required: true,
        },
        description: {
            type: String,
            default: '',
        },
    },
};
</script>

<style scoped>
.kf-settings-section {
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 16px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    scroll-margin-top: 140px;
}

.kf-settings-section-header {
    align-items: flex-start;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    display: flex;
    gap: 16px;
    justify-content: space-between;
    padding: 16px 18px;
}

.kf-settings-section-title {
    color: var(--kf-brand-ink, #0f172a);
    font-size: 15px;
    margin: 0 0 4px;
}

.kf-settings-section-description {
    color: #5b6b82;
    font-size: 11px;
    line-height: 1.6;
    margin: 0;
}

.kf-settings-section-body {
    padding: 16px 18px;
}
</style>
