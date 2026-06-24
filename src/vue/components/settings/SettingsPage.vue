<template>
    <div class="kf-settings-dashboard">
        <aside class="kf-settings-sidebar">
            <div class="kf-settings-sidebar-brand">
                <div class="kf-settings-brand-mark">K</div>
                <div>
                    <p class="kf-settings-kicker">KoalaForms</p>
                    <strong class="kf-settings-brand-title">{{ pageTitle }}</strong>
                </div>
            </div>

            <nav class="kf-settings-nav" aria-label="Settings sections">
                <button
                    v-for="section in sections"
                    :key="section.id"
                    type="button"
                    class="kf-settings-nav-item"
                    :class="{ 'is-active': activeSection === section.id }"
                    :aria-pressed="activeSection === section.id"
                    @click="scrollToSection(section.id)"
                >
                    <span class="kf-settings-nav-item-icon" aria-hidden="true">
                        <svg v-if="section.icon === 'branding'" viewBox="0 0 24 24" fill="none">
                            <rect x="4.5" y="4.5" width="15" height="15" rx="4" />
                            <path d="M8 8h8M8 12h5M8 16h6" />
                        </svg>
                        <svg v-else-if="section.icon === 'privacy'" viewBox="0 0 24 24" fill="none">
                            <path d="M12 3.5l6 2.5v5.5c0 4.1-2.6 7.5-6 9-3.4-1.5-6-4.9-6-9V6l6-2.5z" />
                            <path d="M9.5 12.2l1.9 1.9 3.8-4" />
                        </svg>
                        <svg v-else-if="section.icon === 'logs'" viewBox="0 0 24 24" fill="none">
                            <path d="M4 6h16M4 10h10M4 14h12M4 18h8" />
                        </svg>
                        <svg v-else-if="section.icon === 'plugin'" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2v4M8 6h8l1 3H7L8 6zM7 9v4a5 5 0 0 0 10 0V9" />
                            <path d="M9 18v3M15 18v3" />
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none">
                            <path d="M12 3.5l6.8 2.9 2.2 6.9-2.2 6.8L12 22l-6.8-2.9-2.2-6.8 2.2-6.9L12 3.5z" />
                            <path d="M12 8v4M12 16h.01" />
                        </svg>
                    </span>

                    <span class="kf-settings-nav-item-label">{{ section.label }}</span>
                </button>
            </nav>

            <div class="kf-settings-sidebar-note">
                <strong>Global defaults</strong>
                <p>Use the form editor for per-form behavior and content settings.</p>
            </div>
        </aside>

        <main class="kf-settings-content">
            <header class="kf-settings-topbar">
                <div class="kf-settings-topbar-copy">
                    <p class="kf-settings-topbar-tag">KoalaForms defaults</p>
                    <div class="kf-settings-topbar-title">{{ pageTitle }}</div>
                    <p class="kf-settings-topbar-description">{{ pageDescription }}</p>
                </div>

                <div class="kf-settings-topbar-actions">
                    <div class="kf-settings-status-chip" aria-live="polite">
                        <span class="kf-settings-status-dot"></span>
                        {{ saveStatusText }}
                    </div>
                    <button
                        type="submit"
                        class="button button-primary kf-settings-save-button"
                        :disabled="isSaving"
                    >
                        {{ saveLabel }}
                    </button>
                </div>
            </header>

            <div class="kf-settings-hidden-fields" aria-hidden="true">
                <input type="hidden" name="koalaforms_save_settings" value="1" />
                <input type="hidden" name="form_styling" :value="form.form_styling" />
                <input type="hidden" name="email_template" :value="form.email_template" />
                <input type="hidden" name="ip_logging" :value="form.ip_logging ? 1 : 0" />
                <input type="hidden" name="enable_gdpr_consent" :value="form.enable_gdpr_consent ? 1 : 0" />
                <input type="hidden" name="google_recaptcha_type" :value="form.google_recaptcha_type" />
                <input type="hidden" name="google_recaptcha_site_key" :value="form.google_recaptcha_site_key" />
                <input type="hidden" name="google_recaptcha_secret_key" :value="form.google_recaptcha_secret_key" />
                <input type="hidden" name="hcaptcha_type" :value="form.hcaptcha_type" />
                <input type="hidden" name="hcaptcha_site_key" :value="form.hcaptcha_site_key" />
                <input type="hidden" name="hcaptcha_secret_key" :value="form.hcaptcha_secret_key" />
                <input type="hidden" name="hcaptcha_threshold" :value="form.hcaptcha_threshold" />
                <input type="hidden" name="cloudfare_site_key" :value="form.cloudfare_site_key" />
                <input type="hidden" name="cloudfare_secret_key" :value="form.cloudfare_secret_key" />
                <input type="hidden" name="logging_enabled" :value="form.logging_enabled ? 1 : 0" />
                <input type="hidden" name="log_level" :value="form.log_level" />
                <input type="hidden" name="log_retention_days" :value="form.log_retention_days" />
                <template v-for="section in proSections" :key="section.id + '-fields'">
                    <template v-for="card in section.cards" :key="card.title">
                        <input
                            v-for="field in card.fields"
                            :key="field.key"
                            type="hidden"
                            :name="field.key"
                            :value="proForm[field.key]"
                        />
                    </template>
                </template>
            </div>

            <div class="kf-settings-body">
                <SettingsSection
                    id="spam"
                    ref="spam"
                    title="Spam & Captcha"
                    description="Pick the anti-spam provider and keys you want to use."
                >
                    <template #header-action>
                        <span class="kf-settings-chip">{{ captchaSummary }}</span>
                    </template>

                    <div class="kf-settings-section-stack">
                        <div class="kf-settings-card">
                            <div class="kf-settings-card-head">
                                <div>
                                    <h3 class="kf-settings-card-title">Google reCAPTCHA</h3>
                                    <p class="kf-settings-card-description">
                                        Set up Google reCAPTCHA for forms that need stronger verification.
                                    </p>
                                </div>
                            </div>

                            <div class="kf-settings-grid">
                                <SettingsField
                                    v-model="form.google_recaptcha_type"
                                    type="radio"
                                    name="google_recaptcha_type"
                                    label="Mode"
                                    help="Select the reCAPTCHA mode you want to use."
                                    :options="captchaTypeOptions"
                                />

                                <SettingsField
                                    v-model="form.google_recaptcha_site_key"
                                    type="text"
                                    label="Site Key"
                                    help="Paste the site key from your Google reCAPTCHA settings."
                                />

                                <SettingsField
                                    v-model="form.google_recaptcha_secret_key"
                                    type="text"
                                    label="Secret Key"
                                    help="Paste the secret key from your Google reCAPTCHA settings."
                                />
                            </div>
                        </div>
                    </div>
                </SettingsSection>

                <SettingsSection
                    v-for="section in proSections"
                    :key="section.id"
                    :id="section.id"
                    :ref="(el) => setSectionRef(section.id, el)"
                    :title="section.title"
                    :description="section.description"
                >
                    <template v-if="section.connected" #header-action>
                        <span class="kf-settings-chip kf-settings-chip--connected">Connected</span>
                    </template>

                    <div class="kf-settings-section-stack">
                        <div v-for="(card, ci) in section.cards" :key="ci" class="kf-settings-card">
                            <div class="kf-settings-card-head">
                                <div>
                                    <h3 class="kf-settings-card-title">{{ card.title }}</h3>
                                    <p class="kf-settings-card-description">{{ card.description }}</p>
                                </div>
                            </div>
                            <div class="kf-settings-grid">
                                <SettingsField
                                    v-for="field in card.fields"
                                    :key="field.key"
                                    :type="field.type || 'text'"
                                    :label="field.label"
                                    :help="field.help"
                                    :placeholder="field.placeholder || ''"
                                    :modelValue="proForm[field.key]"
                                    @update:modelValue="(val) => proForm[field.key] = val"
                                />
                            </div>
                        </div>
                    </div>
                </SettingsSection>

                <SettingsSection
                    id="logs"
                    ref="logs"
                    title="Activity Logs"
                    description="Track email notifications and integration activity for your forms. Control what gets recorded and how long logs are retained."
                >
                    <div class="kf-settings-section-stack">
                        <div class="kf-settings-notice kf-settings-notice--warn">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                            </svg>
                            <p>Log entries are created each time a form notification is sent. Set a shorter retention period to keep your database lean.</p>
                        </div>

                        <div class="kf-settings-card">
                            <div class="kf-settings-card-head">
                                <div>
                                    <h3 class="kf-settings-card-title">Activity Logs</h3>
                                    <p class="kf-settings-card-description">
                                        Control what gets written to the integration logs and how long entries are kept.
                                    </p>
                                </div>
                            </div>

                            <div class="kf-settings-grid">
                                <SettingsField
                                    v-model="form.logging_enabled"
                                    type="toggle"
                                    label="Enable Logging"
                                    help="Write activity logs for all active external integrations."
                                    toggle-label="Enable activity logging"
                                />

                                <SettingsField
                                    v-model="form.log_level"
                                    type="select"
                                    label="Log Level"
                                    help="Choose the minimum severity level to record."
                                    :options="logLevelOptions"
                                    :disabled="!form.logging_enabled"
                                />

                                <SettingsField
                                    v-model="form.log_retention_days"
                                    type="select"
                                    label="Retention"
                                    help="Entries older than this are deleted automatically. Choose carefully — logs fill fast on active sites."
                                    :options="logRetentionOptions"
                                    :disabled="!form.logging_enabled"
                                />
                            </div>
                        </div>
                    </div>
                </SettingsSection>

                <SettingsSaveBar :label="saveLabel" />
            </div>
        </main>
    </div>
</template>

<script>
import SettingsSection from './SettingsSection.vue';
import SettingsField from './SettingsField.vue';
import SettingsSaveBar from './SettingsSaveBar.vue';

export default {
    name: 'SettingsPage',
    components: {
        SettingsSection,
        SettingsField,
        SettingsSaveBar,
    },
    props: {
        pageTitle: {
            type: String,
            default: 'Global Settings',
        },
        pageDescription: {
            type: String,
            default: 'Configure KoalaForms defaults for styling, storage, and spam protection.',
        },
        saveLabel: {
            type: String,
            default: 'Save Changes',
        },
        settings: {
            type: Object,
            default: () => ({}),
        },
        captchaOptions: {
            type: Array,
            default: () => [],
        },
        proSections: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        const normalizedSettings = this.normalizeSettings(this.settings);
        const proForm = this.buildProForm(this.proSections);

        return {
            form: normalizedSettings,
            baselineForm: JSON.parse(JSON.stringify(normalizedSettings)),
            proForm,
            proSectionRefs: {},
            captchaTypeOptions: [
                { label: 'Checkbox', value: 'checkbox' },
                { label: 'Invisible', value: 'invisible' },
            ],
            logLevelOptions: [
                { label: 'Everything', value: 'all' },
                { label: 'Errors only', value: 'error' },
            ],
            logRetentionOptions: [
                { label: '7 days', value: '7' },
                { label: '30 days', value: '30' },
                { label: '90 days', value: '90' },
                { label: 'Forever', value: '0' },
            ],
            activeSection: 'spam',
            isSaving: false,
            sectionObserver: null,
            formEl: null,
            submitHandler: null,
            beforeUnloadHandler: null,
        };
    },
    computed: {
        sections() {
            const result = [
                { id: 'spam', label: 'Spam', icon: 'spam' },
            ];
            (this.proSections || []).forEach((section) => {
                result.push({ id: section.id, label: section.label, icon: section.icon || 'plugin' });
            });
            result.push({ id: 'logs', label: 'Logs', icon: 'logs' });
            return result;
        },
        sectionIds() {
            return this.sections.map((section) => section.id);
        },
        isDirty() {
            return JSON.stringify(this.form) !== JSON.stringify(this.baselineForm);
        },
        captchaSummary() {
            if (!Array.isArray(this.captchaOptions) || this.captchaOptions.length === 0) {
                return 'No providers detected';
            }

            const labels = this.captchaOptions
                .filter((option) => option && option.value !== 'none')
                .map((option) => option.label)
                .filter(Boolean);

            return labels.length ? `${labels.length} provider${labels.length === 1 ? '' : 's'} available` : 'Providers ready';
        },
        resolvedAddonPromoUrl() {
            return 'admin.php?page=koalaforms-help#addons';
        },
        saveStatusText() {
            if (this.isSaving) {
                return 'Saving changes';
            }

            if (this.isDirty) {
                return 'Unsaved changes';
            }

            return 'Ready to save';
        },
    },
    mounted() {
        this.setupObserver();

        const form = this.$el.closest('form');
        if (form) {
            this.formEl = form;
            this.submitHandler = () => {
                this.isSaving = true;
            };
            form.addEventListener('submit', this.submitHandler);
        }

        this.beforeUnloadHandler = (event) => {
            if (!this.isDirty || this.isSaving) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        };

        window.addEventListener('beforeunload', this.beforeUnloadHandler);
    },
    beforeUnmount() {
        if (this.sectionObserver) {
            this.sectionObserver.disconnect();
            this.sectionObserver = null;
        }

        if (this.formEl && this.submitHandler) {
            this.formEl.removeEventListener('submit', this.submitHandler);
        }

        if (this.beforeUnloadHandler) {
            window.removeEventListener('beforeunload', this.beforeUnloadHandler);
        }
    },
    methods: {
        normalizeSettings(settings) {
            return {
                form_styling: settings.form_styling || 'classic',
                email_template: this.normalizeTemplateValue(settings.email_template || 'classic'),
                ip_logging: Boolean(settings.ip_logging),
                enable_gdpr_consent: Boolean(settings.enable_gdpr_consent),
                google_recaptcha_type: settings.google_recaptcha_type || 'checkbox',
                google_recaptcha_site_key: settings.google_recaptcha_site_key || '',
                google_recaptcha_secret_key: settings.google_recaptcha_secret_key || '',
                hcaptcha_type: settings.hcaptcha_type || 'checkbox',
                hcaptcha_site_key: settings.hcaptcha_site_key || '',
                hcaptcha_secret_key: settings.hcaptcha_secret_key || '',
                hcaptcha_threshold: settings.hcaptcha_threshold || '0.5',
                cloudfare_site_key: settings.cloudfare_site_key || '',
                cloudfare_secret_key: settings.cloudfare_secret_key || '',
                logging_enabled: settings.logging_enabled !== undefined ? Boolean(settings.logging_enabled) : true,
                log_level: settings.log_level || 'all',
                log_retention_days: settings.log_retention_days || '30',
            };
        },
        buildProForm(proSections) {
            const form = {};
            (proSections || []).forEach((section) => {
                (section.cards || []).forEach((card) => {
                    (card.fields || []).forEach((field) => {
                        form[field.key] = field.value || '';
                    });
                });
            });
            return form;
        },
        setSectionRef(id, el) {
            if (el) {
                this.proSectionRefs[id] = el;
            } else {
                delete this.proSectionRefs[id];
            }
        },
        normalizeTemplateValue(value) {
            return String(value || 'classic').toLowerCase();
        },
        setupObserver() {
            if (!window.IntersectionObserver) {
                return;
            }

            const rootMargin = '-35% 0px -50% 0px';
            this.sectionObserver = new IntersectionObserver((entries) => {
                const visibleEntry = entries.find((entry) => entry.isIntersecting);

                if (visibleEntry?.target?.id) {
                    this.activeSection = visibleEntry.target.id;
                }
            }, {
                root: null,
                rootMargin,
                threshold: 0.15,
            });

            this.$nextTick(() => {
                this.sectionIds.forEach((id) => {
                    const section = this.$refs[id] || this.proSectionRefs[id];

                    if (section?.$el) {
                        this.sectionObserver.observe(section.$el);
                    }
                });
            });
        },
        scrollToSection(sectionId) {
            this.activeSection = sectionId;
            const section = this.$refs[sectionId] || this.proSectionRefs[sectionId];

            if (section?.$el) {
                section.$el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }
        },
    },
};
</script>

<style scoped>
.kf-settings-dashboard {
    display: grid;
    grid-template-columns: minmax(220px, 252px) minmax(0, 1fr);
    min-height: calc(100vh - 32px);
    --kf-settings-text: var(--kf-brand-ink, #0f172a);
    --kf-settings-text-muted: #5b6b82;
    --kf-settings-surface: #ffffff;
    --kf-settings-surface-alt: #f8fafc;
    --kf-settings-border: rgba(15, 23, 42, 0.08);
    --kf-settings-border-soft: rgba(15, 23, 42, 0.04);
    background:
        radial-gradient(circle at top left, rgba(56, 88, 233, 0.1), transparent 28%),
        linear-gradient(180deg, #f7f8fb 0%, #f5f7fb 100%);
}

.kf-settings-sidebar {
    background: var(--kf-settings-surface);
    border-right: 1px solid var(--kf-settings-border-soft);
    box-shadow: 4px 0 12px rgba(15, 23, 42, 0.015);
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 20px 14px 18px;
    position: sticky;
    top: 32px;
    height: calc(100vh - 32px);
}

.kf-settings-sidebar-brand {
    align-items: center;
    display: flex;
    gap: 10px;
    padding: 2px 2px 4px;
}

.kf-settings-brand-mark {
    align-items: center;
    background: linear-gradient(135deg, var(--kf-brand-primary, #3858e9), var(--kf-brand-secondary, #5670ff));
    border-radius: 12px;
    box-shadow: 0 8px 14px rgba(56, 88, 233, 0.16);
    color: #ffffff;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    height: 40px;
    justify-content: center;
    width: 40px;
}

.kf-settings-kicker {
    color: var(--kf-brand-primary, #3858e9);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.14em;
    margin: 0 0 4px;
    text-transform: uppercase;
}

.kf-settings-brand-title {
    color: var(--kf-settings-text);
    font-size: 14px;
    line-height: 1.2;
}

.kf-settings-nav {
    display: grid;
    gap: 6px;
}

.kf-settings-nav-item {
    background: transparent;
    border: 0;
    border-radius: 14px;
    color: var(--kf-settings-text-muted);
    display: flex;
    align-items: center;
    gap: 14px;
    min-height: 54px;
    padding: 10px 12px;
    text-align: left;
    transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
    width: 100%;
}

.kf-settings-nav-item:hover {
    background: rgba(56, 88, 233, 0.05);
    color: var(--kf-settings-text);
}

.kf-settings-nav-item:focus-visible {
    box-shadow: 0 0 0 3px rgba(56, 88, 233, 0.18);
    outline: none;
}

.kf-settings-nav-item.is-active {
    background: var(--kf-brand-soft, rgba(56, 88, 233, 0.12));
    box-shadow: inset 0 0 0 1px rgba(56, 88, 233, 0.10);
    color: var(--kf-settings-text);
}

.kf-settings-nav-item-icon {
    align-items: center;
    background: transparent;
    border-radius: 0;
    color: var(--kf-settings-text-muted);
    display: inline-flex;
    flex: 0 0 22px;
    height: 22px;
    justify-content: center;
    width: 22px;
}

.kf-settings-nav-item-icon svg {
    height: 22px;
    width: 22px;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.7;
}

.kf-settings-nav-item-label {
    font-size: 15px;
    line-height: 1.15;
    font-weight: 700;
    color: inherit;
}

.kf-settings-nav-item.is-active .kf-settings-nav-item-icon {
    color: var(--kf-brand-primary, #3858e9);
}

.kf-settings-sidebar-note {
    background: transparent;
    border-top: 1px solid rgba(15, 23, 42, 0.07);
    border-radius: 0;
    color: var(--kf-settings-text-muted);
    display: grid;
    gap: 4px;
    padding: 10px 4px 0;
    margin-top: auto;
    align-self: start;
}

.kf-settings-sidebar-note strong {
    color: var(--kf-settings-text);
    font-size: 10px;
}

.kf-settings-sidebar-note p {
    color: var(--kf-settings-text-muted);
    font-size: 9px;
    line-height: 1.5;
    margin: 0;
}

.kf-settings-content {
    min-width: 0;
    display: grid;
}

.kf-settings-topbar {
    align-items: flex-end;
    background:
        linear-gradient(135deg, rgba(56, 88, 233, 0.04), rgba(86, 112, 255, 0.02)),
        rgba(255, 255, 255, 0.92);
    border-bottom: 1px solid var(--kf-settings-border);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
    backdrop-filter: blur(18px);
    display: flex;
    gap: 16px;
    justify-content: space-between;
    padding: 20px 24px 18px;
    position: sticky;
    top: 32px;
    z-index: 1;
}

.kf-settings-topbar-tag {
    color: var(--kf-brand-primary, #3858e9);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.16em;
    margin: 0 0 8px;
    text-transform: uppercase;
}

.kf-settings-topbar-title {
    color: var(--kf-settings-text);
    font-size: 24px;
    line-height: 1.1;
    margin: 0 0 8px;
}

.kf-settings-topbar-description {
    color: var(--kf-settings-text-muted);
    font-size: 11px;
    line-height: 1.6;
    margin: 0;
    max-width: 760px;
}

.kf-settings-topbar-actions {
    align-items: center;
    display: flex;
    gap: 8px;
}

.kf-settings-status-chip {
    align-items: center;
    background: var(--kf-settings-surface);
    border: 1px solid var(--kf-settings-border);
    border-radius: 999px;
    color: var(--kf-settings-text-muted);
    display: inline-flex;
    font-size: 10px;
    font-weight: 600;
    gap: 8px;
    padding: 7px 10px;
}

.kf-settings-status-dot {
    background: #16a34a;
    border-radius: 999px;
    display: inline-block;
    height: 10px;
    width: 10px;
}

.kf-settings-save-button {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    min-height: 40px;
    padding-inline: 16px;
    background: linear-gradient(135deg, var(--kf-brand-primary, #3858e9), var(--kf-brand-secondary, #5670ff)) !important;
    border-color: transparent !important;
    box-shadow: 0 10px 20px rgba(56, 88, 233, 0.22);
}

.kf-settings-save-button:disabled {
    cursor: wait;
    opacity: 0.72;
}

.kf-settings-save-button:focus-visible {
    box-shadow: 0 0 0 3px rgba(56, 88, 233, 0.18), 0 10px 20px rgba(56, 88, 233, 0.22);
    outline: none;
}

.kf-settings-hidden-fields {
    height: 0;
    overflow: hidden;
    position: absolute;
    width: 0;
}

.kf-settings-body {
    display: grid;
    gap: 16px;
    padding: 18px 24px 34px;
}

.kf-settings-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.kf-settings-section-stack {
    display: grid;
    gap: 12px;
}

.kf-settings-card {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, var(--kf-settings-surface-alt) 100%);
    border: 1px solid var(--kf-settings-border);
    border-radius: 16px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    position: relative;
    padding: 14px;
}

.kf-settings-card-head {
    align-items: flex-start;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 10px;
}

.kf-settings-card-title {
    color: var(--kf-settings-text);
    font-size: 13px;
    margin: 0 0 3px;
}

.kf-settings-card-description {
    color: var(--kf-settings-text-muted);
    font-size: 11px;
    line-height: 1.6;
    margin: 0;
}

.kf-settings-card-content {
    min-width: 0;
}

.kf-settings-chip {
    align-items: center;
    background: var(--kf-brand-soft, rgba(56, 88, 233, 0.12));
    border-radius: 999px;
    color: var(--kf-brand-primary, #3858e9);
    display: inline-flex;
    font-size: 10px;
    font-weight: 600;
    padding: 5px 9px;
}

.kf-settings-chip--connected {
    background: rgba(22, 163, 74, 0.1);
    color: #16a34a;
}

.kf-settings-addon-banner {
    align-items: center;
    background:
        linear-gradient(135deg, rgba(139, 92, 246, 0.10), rgba(168, 85, 247, 0.04)),
        var(--kf-settings-surface);
    border: 1px solid rgba(139, 92, 246, 0.18);
    border-radius: 16px;
    display: flex;
    gap: 18px;
    justify-content: space-between;
    margin-top: 2px;
    padding: 16px 18px;
}

.kf-settings-addon-banner-copy {
    display: grid;
    gap: 8px;
    max-width: 760px;
}

.kf-settings-addon-eyebrow {
    color: #8b5cf6;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 0.16em;
    margin: 0;
    text-transform: uppercase;
}

.kf-settings-addon-title {
    color: var(--kf-settings-text);
    font-size: 15px;
    line-height: 1.3;
    margin: 0;
}

.kf-settings-addon-description {
    color: var(--kf-settings-text-muted);
    font-size: 11px;
    line-height: 1.65;
    margin: 0;
}

.kf-settings-addon-actions {
    align-items: flex-end;
    display: grid;
    gap: 8px;
    justify-items: end;
    min-width: 220px;
}

.kf-settings-addon-button {
    background: linear-gradient(135deg, var(--kf-brand-primary, #3858e9), var(--kf-brand-secondary, #5670ff)) !important;
    border-color: transparent !important;
    border-radius: 999px;
    box-shadow: 0 10px 20px rgba(139, 92, 246, 0.20);
    min-height: 40px;
    padding-inline: 16px;
}

.kf-settings-addon-note {
    color: var(--kf-settings-text-muted);
    font-size: 10px;
    line-height: 1.5;
    margin: 0;
    max-width: 220px;
    text-align: right;
}

.kf-settings-notice {
    align-items: flex-start;
    border-radius: 12px;
    border: 1px solid;
    display: flex;
    gap: 10px;
    padding: 12px 14px;
}

.kf-settings-notice svg {
    flex: 0 0 16px;
    height: 16px;
    margin-top: 1px;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.8;
    width: 16px;
}

.kf-settings-notice p {
    color: inherit;
    font-size: 11px;
    line-height: 1.6;
    margin: 0;
}

.kf-settings-notice--warn {
    background: rgba(234, 179, 8, 0.07);
    border-color: rgba(234, 179, 8, 0.28);
    color: #92400e;
}

@media (max-width: 1280px) {
    .kf-settings-dashboard {
        grid-template-columns: 1fr;
    }

    .kf-settings-sidebar {
        height: auto;
        position: static;
    }
}

@media (max-width: 960px) {
    .kf-settings-sidebar {
        padding: 20px 18px;
    }

    .kf-settings-sidebar,
    .kf-settings-topbar {
        top: 0;
    }

    .kf-settings-topbar {
        align-items: flex-start;
        flex-direction: column;
    }

    .kf-settings-topbar-actions {
        width: 100%;
    }

    .kf-settings-grid,
    .kf-settings-scope-grid {
        grid-template-columns: 1fr;
    }

    .kf-settings-addon-banner {
        flex-direction: column;
    }

    .kf-settings-addon-actions {
        justify-items: start;
        min-width: 0;
    }

    .kf-settings-addon-note {
        text-align: left;
    }

    .kf-settings-body,
    .kf-settings-topbar {
        padding-left: 18px;
        padding-right: 18px;
    }
}
</style>
