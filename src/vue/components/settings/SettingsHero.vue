<template>
    <header class="kf-settings-hero">
        <div class="kf-settings-hero-copy">
            <p class="kf-settings-kicker">KoalaForms</p>
            <h1 class="kf-settings-title">{{ title }}</h1>
            <p class="kf-settings-description">
                {{ description }}
            </p>
        </div>

        <div class="kf-settings-hero-meta">
            <div class="kf-settings-meta-card">
                <span class="kf-settings-meta-label">Status</span>
                <strong class="kf-settings-meta-value">Ready</strong>
            </div>
            <div class="kf-settings-meta-card">
                <span class="kf-settings-meta-label">Mode</span>
                <strong class="kf-settings-meta-value">Global defaults</strong>
            </div>
        </div>
    </header>
</template>

<script>
export default {
    name: 'SettingsHero',
    props: {
        title: {
            type: String,
            default: 'Global Settings',
        },
        description: {
            type: String,
            default: 'Configure the KoalaForms defaults used across the plugin.',
        },
    },
};
</script>

<style scoped>
.kf-settings-hero {
    align-items: flex-end;
    background:
        linear-gradient(135deg, rgba(56, 88, 233, 0.06), rgba(86, 112, 255, 0.02)),
        rgba(255, 255, 255, 0.98);
    border: 1px solid rgba(56, 88, 233, 0.12);
    border-radius: 24px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    display: flex;
    gap: 24px;
    justify-content: space-between;
    padding: 28px;
}

.kf-settings-hero-copy {
    max-width: 720px;
}

.kf-settings-kicker {
    color: var(--kf-brand-primary, #3858e9);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    margin: 0 0 10px;
    text-transform: uppercase;
}

.kf-settings-title {
    color: var(--kf-brand-ink, #0f172a);
    font-size: 30px;
    line-height: 1.15;
    margin: 0 0 10px;
}

.kf-settings-description {
    color: #5b6b82;
    font-size: 15px;
    line-height: 1.6;
    margin: 0;
}

.kf-settings-hero-meta {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.kf-settings-meta-card {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(56, 88, 233, 0.03) 100%);
    border: 1px solid rgba(56, 88, 233, 0.10);
    border-radius: 18px;
    min-width: 140px;
    padding: 14px 16px;
}

.kf-settings-meta-label {
    color: #6b7280;
    display: block;
    font-size: 12px;
    margin-bottom: 4px;
}

.kf-settings-meta-value {
    color: var(--kf-brand-ink, #0f172a);
    font-size: 14px;
}

@media (max-width: 960px) {
    .kf-settings-hero {
        align-items: flex-start;
        flex-direction: column;
    }

    .kf-settings-hero-meta {
        width: 100%;
    }
}
</style>
