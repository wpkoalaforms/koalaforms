<template>
    <div class="kf-settings-savebar">
        <div class="kf-settings-savebar-copy">
            <strong>Save changes</strong>
            <span>Updates apply to all new forms that rely on global defaults.</span>
        </div>

        <button
            type="submit"
            name="koalaforms_save_settings"
            value="1"
            class="button button-primary kf-settings-save-button"
        >
            {{ label }}
        </button>
    </div>
</template>

<script>
export default {
    name: 'SettingsSaveBar',
    props: {
        label: {
            type: String,
            default: 'Save Changes',
        },
    },
};
</script>

<style scoped>
.kf-settings-savebar {
    align-items: center;
    background:
        linear-gradient(135deg, rgba(56, 88, 233, 0.08), rgba(86, 112, 255, 0.03)),
        rgba(255, 255, 255, 0.98);
    border: 1px solid rgba(56, 88, 233, 0.14);
    border-radius: 16px;
    color: var(--kf-brand-ink, #0f172a);
    display: flex;
    gap: 16px;
    justify-content: space-between;
    padding: 16px 18px;
    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08);
}

.kf-settings-savebar-copy {
    display: grid;
    gap: 4px;
}

.kf-settings-savebar-copy strong {
    font-size: 13px;
}

.kf-settings-savebar-copy span {
    color: #5b6b82;
    font-size: 11px;
}

.kf-settings-save-button {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    min-height: 38px;
    padding-inline: 14px;
    box-shadow: 0 10px 20px rgba(56, 88, 233, 0.22);
}

@media (max-width: 720px) {
    .kf-settings-savebar {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
