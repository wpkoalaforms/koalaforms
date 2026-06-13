<template>
  <div class="wpflexi kf-dashboard-page">
    <div class="kf-dashboard-shell">
      <header class="kf-dashboard-hero">
        <div class="kf-dashboard-hero-copy">
          <p class="kf-dashboard-kicker">{{ data.introKicker }}</p>
          <h1 class="kf-dashboard-title">{{ data.heading }}</h1>
          <p class="kf-dashboard-description">{{ data.intro }}</p>

          <div v-if="heroActions.length" class="kf-dashboard-hero-actions">
            <a
              v-for="action in heroActions"
              :key="action.label"
              :class="[
                'button',
                action.type === 'primary' ? 'button-primary kf-dashboard-primary-button' : 'button-secondary kf-dashboard-secondary-button',
              ]"
              :href="action.url"
            >
              {{ action.label }}
            </a>
          </div>

          <div class="kf-dashboard-hero-foot">
            <div class="kf-dashboard-hero-foot-item">
              <span>Overview</span>
              <strong>{{ data.pageSubtitle }}</strong>
            </div>
            <div class="kf-dashboard-hero-foot-item">
              <span>Focus</span>
              <strong>{{ heroBadge }}</strong>
            </div>
          </div>
        </div>

        <aside class="kf-dashboard-hero-panel">
          <div class="kf-dashboard-hero-panel-head">
            <span :class="['kf-dashboard-status', `kf-dashboard-status-${heroTone}`]">{{ heroBadge }}</span>
            <h2>{{ data.hero.title }}</h2>
          </div>
          <p class="kf-dashboard-hero-panel-copy">{{ data.hero.copy }}</p>

          <div class="kf-dashboard-hero-stats">
            <article v-for="point in heroPoints" :key="point.label" class="kf-dashboard-hero-stat">
              <span>{{ point.label }}</span>
              <strong>{{ point.value }}</strong>
            </article>
          </div>

          <div class="kf-dashboard-hero-track" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </aside>
      </header>

      <section class="kf-dashboard-summary-grid">
        <article v-for="card in summaryCards" :key="card.label" class="kf-dashboard-summary-card">
          <span class="kf-dashboard-summary-label">{{ card.label }}</span>
          <strong class="kf-dashboard-summary-value">{{ card.value }}</strong>
          <span class="kf-dashboard-summary-note">{{ card.note }}</span>
        </article>
      </section>

      <section class="kf-dashboard-layout">
        <div class="kf-dashboard-main-column">
          <article class="kf-dashboard-card kf-dashboard-card-overview">
            <div class="kf-dashboard-card-head">
              <div>
                <p class="kf-dashboard-card-kicker">{{ data.pageSubtitle }}</p>
                <h2 class="kf-dashboard-card-title">{{ data.overview.title }}</h2>
              </div>
            </div>

            <div class="kf-dashboard-overview-stats">
              <article v-for="stat in data.overview.stats" :key="stat.label" class="kf-dashboard-overview-stat">
                <span>{{ stat.label }}</span>
                <strong>{{ stat.value }}</strong>
              </article>
            </div>

            <div class="kf-dashboard-overview-spotlight">
              <div class="kf-dashboard-overview-spotlight-copy">
                <strong>{{ data.overview.spotlightTitle }}</strong>
                <p>{{ data.overview.spotlightCopy }}</p>
              </div>
            </div>

            <div class="kf-dashboard-overview-block">
              <div class="kf-dashboard-overview-block-head">
                <h3>Recent forms</h3>
              </div>

              <div v-if="recentForms.length" class="kf-dashboard-form-list">
                <div v-for="form in recentForms" :key="form.id" class="kf-dashboard-form-row">
                  <div class="kf-dashboard-form-main">
                    <div class="kf-dashboard-form-title-row">
                      <span class="kf-dashboard-form-title">{{ form.title }}</span>
                      <span :class="['kf-dashboard-status', `kf-dashboard-status-${form.statusClass}`]">{{ form.statusLabel }}</span>
                    </div>
                    <div class="kf-dashboard-form-meta">
                      <span>{{ form.date }}</span>
                      <span>{{ form.entries }} entries</span>
                      <span>{{ form.shortcode }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="kf-dashboard-empty">
                <h3>No forms yet</h3>
                <p>Create a form to start collecting submissions and see it appear here.</p>
              </div>
            </div>

            <div class="kf-dashboard-overview-block">
              <div class="kf-dashboard-overview-block-head">
                <h3>Latest submissions</h3>
              </div>

              <div v-if="recentSubmissions.length" class="kf-dashboard-submission-list">
                <div v-for="submission in recentSubmissions" :key="submission.id" class="kf-dashboard-submission-row">
                  <div class="kf-dashboard-submission-main">
                    <strong class="kf-dashboard-submission-title">#{{ submission.id }} · {{ submission.formName }}</strong>
                    <div class="kf-dashboard-submission-meta">
                      <span>{{ submission.userName }}</span>
                      <span>{{ submission.browser }}</span>
                    </div>
                  </div>
                  <div class="kf-dashboard-submission-side">
                    <span>{{ submission.date }}</span>
                  </div>
                </div>
              </div>

              <div v-else class="kf-dashboard-empty">
                <h3>No submissions yet</h3>
                <p>Once your forms start collecting entries, the latest responses will appear here.</p>
              </div>
            </div>
          </article>
        </div>

        <aside class="kf-dashboard-side-column">
          <article class="kf-dashboard-card kf-dashboard-card-quick">
            <div class="kf-dashboard-card-head">
              <div>
                <p class="kf-dashboard-card-kicker">Next steps</p>
                <h2 class="kf-dashboard-card-title">{{ statusCard.title }}</h2>
              </div>
            </div>

            <p class="kf-dashboard-support-copy">{{ statusCard.copy }}</p>

            <div class="kf-dashboard-steps">
              <div v-for="(point, index) in statusCard.points" :key="point.title" class="kf-dashboard-step">
                <span class="kf-dashboard-step-index">{{ index + 1 }}</span>
                <div class="kf-dashboard-step-copy">
                  <strong>{{ point.title }}</strong>
                  <span>{{ point.copy }}</span>
                </div>
              </div>
            </div>
          </article>

          <article class="kf-dashboard-card kf-dashboard-card-extend">
            <div class="kf-dashboard-card-head">
              <div>
                <p class="kf-dashboard-card-kicker">Dashboard principles</p>
                <h2 class="kf-dashboard-card-title">What this screen should help you do</h2>
              </div>
            </div>

            <div class="kf-dashboard-addon-grid">
              <div v-for="note in sidebarNotes" :key="note.title" class="kf-dashboard-addon-card">
                <strong>{{ note.title }}</strong>
                <p>{{ note.description }}</p>
              </div>
            </div>

            <div class="kf-dashboard-hero-mini-footer">
              <div class="kf-dashboard-hero-mini-footer-item">
                <span>Total forms</span>
                <strong>{{ summaryCards[0] ? summaryCards[0].value : '0' }}</strong>
              </div>
              <div class="kf-dashboard-hero-mini-footer-item">
                <span>Active forms</span>
                <strong>{{ summaryCards[1] ? summaryCards[1].value : '0' }}</strong>
              </div>
            </div>
          </article>
        </aside>
      </section>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DashboardPage',
  props: {
    data: {
      type: Object,
      default: () => ({}),
    },
  },
  computed: {
    heroActions() {
      return this.data && this.data.hero && Array.isArray(this.data.hero.actions) ? this.data.hero.actions : [];
    },
    heroPoints() {
      return this.data && this.data.hero && Array.isArray(this.data.hero.points) ? this.data.hero.points : [];
    },
    heroBadge() {
      return this.data && this.data.hero && this.data.hero.badge ? this.data.hero.badge : 'Status';
    },
    heroTone() {
      return this.data && this.data.hero && this.data.hero.tone ? this.data.hero.tone : 'healthy';
    },
    summaryCards() {
      return this.data && Array.isArray(this.data.summaryCards) ? this.data.summaryCards : [];
    },
    statusCard() {
      return this.data && this.data.statusCard ? this.data.statusCard : { title: '', copy: '', points: [] };
    },
    recentForms() {
      return this.data && Array.isArray(this.data.recentForms) ? this.data.recentForms : [];
    },
    recentSubmissions() {
      return this.data && Array.isArray(this.data.recentSubmissions) ? this.data.recentSubmissions : [];
    },
    sidebarNotes() {
      return this.data && Array.isArray(this.data.sidebarNotes) ? this.data.sidebarNotes : [];
    },
  },
};
</script>
