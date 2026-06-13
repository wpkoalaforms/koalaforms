=== Koala Forms ===
Contributors:      wpkoalaforms
Tags:              form, contact form, gutenberg, block, form builder
Requires at least: 6.4
Tested up to:      7.0
Stable tag:        1.0.0
Requires PHP:      7.4
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Build contact forms, multi-step forms, and registration forms using Gutenberg blocks.

== Description ==

**Koala Forms** is a form builder plugin for WordPress that works within the Gutenberg block editor. Each form field is a block. Forms are embedded in pages and posts using a shortcode and submissions are stored in the WordPress database.

= Supported Fields =

* Text Field (with input masking)
* Email Field
* Textarea / Paragraph
* Number Field
* URL Field
* Dropdown (Select)
* Multi-Select
* Radio Buttons
* Checkboxes
* Date Field
* Address Field
* Disclosure / Consent
* Multi-Step Forms

= Features =

* **Gutenberg-blocks** — every field is a WordPress block, configured in the block editor
* **Multi-step forms** — built-in step navigation with configurable steps
* **Input masking** — format fields like phone numbers as users type
* **Form submissions** — stored in the WordPress database, viewable in the admin panel
* **CAPTCHA support** — integrates with Google reCAPTCHA.
* **Email notifications** — send confirmation and notification emails on submission

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/koalaforms/`, or install via the WordPress plugin screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Koala Forms → All Forms** in the WordPress admin menu.
4. Click **Add New Form**, enter a name, and click **Create Form**.
5. The form opens in the Gutenberg block editor — use the block inserter to add fields.
6. Publish the form, then copy the shortcode (e.g. `[KoalaForms form_id="123"]`) and paste it into any page or post.

== Frequently Asked Questions ==

= Do I need to know how to code? =

No. Forms are built using the Gutenberg block editor — add fields, configure settings, and publish.

= Can I create multi-step forms? =

Yes. Multi-step forms with step navigation are supported. Use the Step block to divide your form into sections.

= Where are form submissions stored? =

Submissions are stored in your WordPress database and are accessible from **Koala Forms → Submissions** in the admin menu.

= How do I display a form on a page? =

After publishing a form, copy its shortcode from the Forms list (e.g. `[KoalaForms form_id="123"]`) and paste it into any page or post.

= Is it compatible with my theme? =

Koala Forms works with standard WordPress themes. The form output uses minimal markup and can be styled with CSS.


== License ==

Koala Forms is licensed under the GNU General Public License v2.0 or later.
License URI: https://www.gnu.org/licenses/gpl-2.0.html

All bundled third-party libraries are GPL-compatible:

* Vue.js — MIT License — https://github.com/vuejs/core
* vue-the-mask — MIT License — https://github.com/vuejs-tips/vue-the-mask
* vue-recaptcha — MIT License — https://github.com/DanSnow/vue-recaptcha
* DOMPurify — MPL-2.0 / Apache-2.0 — https://github.com/cure53/DOMPurify
* uuid — MIT License — https://github.com/uuidjs/uuid

== Development ==

This plugin uses a build process for its JavaScript assets. The minified files in the `build/` and `assets/` directories are compiled from source.

The full human-readable source code is available on GitHub:
https://github.com/wpkoalaforms/koalaforms

To build from source:

1. Clone the repository: `git clone https://github.com/wpkoalaforms/koalaforms.git`
2. Run `npm install`
3. Run `npm run build`
4. Run `npm run build-vue`

== External Services ==

= Google reCAPTCHA =

This plugin optionally integrates with Google reCAPTCHA to help prevent spam form submissions. This feature is disabled by default and only active when a reCAPTCHA site key and secret key are configured in the plugin settings.

**What it does:** Verifies that a form submission was made by a human and not an automated bot.

**What data is sent and when:**
* When a visitor loads a page containing a Koala Forms form with reCAPTCHA enabled, Google's reCAPTCHA script is loaded from `https://www.google.com/recaptcha/api.js`. This may send the visitor's IP address and browser information to Google.
* When a visitor submits a form, the reCAPTCHA response token is sent from your WordPress server to `https://www.google.com/recaptcha/api/siteverify` to verify the submission. No personal form field data is included in this request.

**This data is sent to Google only when reCAPTCHA is enabled in the plugin settings.**

* Service provider: Google LLC
* Terms of Service: https://policies.google.com/terms
* Privacy Policy: https://policies.google.com/privacy


== Screenshots ==

1. The Koala Forms dashboard — manage all your forms in one place.
2. Building a form in the Gutenberg block editor — drag, drop, and publish.
3. Frontend form rendering — clean and responsive on any theme.

== Changelog ==

= 1.0.0 =
* Initial release
