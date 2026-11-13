# GRID Accessibility Toolbar 1.11.0

## Installation or update

1. Sign in to WordPress as an administrator.
2. Open **Plugins → Add New Plugin → Upload Plugin**.
3. Upload **grid-accessibility-toolbar-1.11.0.zip** without extracting it.
4. Choose **Install Now**, then **Activate Plugin**. For an update, choose **Replace current with uploaded**.
5. Open **GRID-Arendal → Accessibility Widget**. The toolbar is enabled sitewide by default. Choose controls, launcher position/color, preference storage and an optional accessibility statement URL, then save.
6. Clear site, hosting and CDN caches, then check a public page while signed out.

No UserWay account, API key or build step is required. Reuse this ZIP on other GRID-Arendal WordPress sites. Requires WordPress 6.4+, PHP 7.4+ and a modern browser. See TEST-REPORT.md for tested versions and limits.

## Compact controls

Seventeen preferences appear in fourteen tiles when all controls are enabled. Each cycling button announces and displays its current mode:

- **Font style:** OpenDyslexic → Arial → Site default. OpenDyslexic is a distinctive optional font; Arial is a conventional sans-serif choice. On sites already using Helvetica/Arial the latter change can be subtle. Text size and spacing remain independent. If only one font option is enabled by the administrator, it remains a simple toggle.
- **Reading aids:** Large pointer → Reading mask → Reading guide → Off. Disabled administrator choices are skipped. The aids are mutually exclusive.
- **Reading mask:** dimmed regions above/below a clear reading window. Window height is adjustable from 80–240 pixels and background dimming from 40–85%. It follows pointer/touch position and keyboard focus outside the toolbar, stays within the viewport, and never blocks page clicks. Sliders appear while the mask is on. Defaults are 140 pixels and 70%.

Other controls include text size, spacing, dark/light contrast, highlighted links, reduced motion, line height, text alignment, media saturation, image hiding, author-text tooltips, page structure and strong focus. Reset clears reading preferences, including mask settings.

Profiles offer the eight requested presets with visible explanations. They replace current preferences, respect administrator-disabled controls and allow individual overrides. Only concrete settings are stored, never the disability/profile label. Blind offers navigation aids, not a screen reader. Reduced motion pauses CSS effects and native HTML video; embedded media, GIFs, canvas and script-driven motion can remain. No preset guarantees clinical benefit or seizure safety.

## GRID-Arendal identity

The compact panel header uses the supplied official lavender flower icon, Accessibility Widget title and a linked “Brought to you by GRID-Arendal” credit, alongside locally hosted Geologica Regular/SemiBold and the 2025 manual's lavender palette. Darker approved shades are used for readable small text and controls. The flower SVG is unchanged. Existing custom launcher colors are preserved; the old default teal migrates to lavender. See BRANDING.md for asset provenance and licenses.

## GTranslate inside the widget

After activation, an administrator notice checks the translation integration. If GTranslate is missing, choose **Install GTranslate**, then **Activate Plugin** on WordPress’s installation screen. If installed but inactive, choose **Activate GTranslate** in the notice. No third-party plugin is silently installed. When GTranslate is working, the notice disappears. If you want only reading controls, disable **Embed GTranslate selector when GTranslate is active** and the prompt disappears. Core controls work without GTranslate. Accounts without installation rights are directed to their site/network administrator. Network administrators use the network installation screen.

Install **GTranslate** separately. The test site uses GTranslate 5.0.2 with all 104 available languages, English source/default, browser auto-detection off, native language names, Dropdown, wrapper `.gtranslate_wrapper`, floating selector off and no menu placement.

**Embed GTranslate selector when GTranslate is active** is on by default in GRID Accessibility settings. The official dropdown appears inside the panel, with a label and loading/failure feedback. Without GTranslate, the language section is absent and core controls still work. A chosen language may persist separately through GTranslate; choose English to restore original content. Reset reading preferences does not reset translation.

When this embedded integration is available on a public page, the plugin automatically suppresses the duplicate official GTranslate floater and the legacy Google Language Translator yellow **Translate »** button. Their saved settings are preserved and take effect again when this integration or our toolbar is disabled. The test site's legacy translator remains installed; script ordering prevents it overwriting the embedded widget configuration.

Translation uses external GTranslate/Google services and can send page text and connection information to providers. Review the relevant service terms, privacy/consent and CSP requirements for each site. No paid subscription or multilingual SEO is included. The toolbar interface stays in the site's interface language; no translated label catalogs are bundled.

## Beaver Builder and other editors

The toolbar applies temporary browser preferences; it never saves page content, theme files or builder layouts. Assets/markup are omitted in detected Beaver Builder editing sessions, WordPress administration, Customizer, previews, feeds and embeds. Client-side detection also removes effects if editing starts after load. Other editors are handled defensively but have not all been tested.

For a custom editor, exclude its path in settings or use `grid_a11y_should_display`. Developers can wrap widgets in `data-ga-a11y-ignore` to exclude them from adjustments. Editable areas, code and known icon fonts are skipped by text scaling. Inherited theme styles can still cross some boundaries.

## Multisite

Network Activate makes the plugin available across the network, with lazy defaults for existing/new sites. Configure each site's GRID-Arendal → Accessibility Widget separately. There is no shared network settings panel or cross-site preference sharing. Network activation has not been exhaustively browser-tested.

## Privacy and scope

Core preferences use localStorage isolated by site URL and send no telemetry or page content. Storage restrictions do not prevent use. Turning remembering off clears the saved key on the next eligible visit. Reset removes it. The optional translation service has its own separate data handling.

The panel is a labelled nonmodal dialog: Tab can move between it and the page, Enter/Space opens it, Escape closes it and returns focus. It does not override global assistive-technology shortcuts. The plugin offers presentation preferences, not automated WCAG certification, legal compliance, semantic repairs, alternative-text generation, auditing, voice navigation, dictionary or a screen reader. Contrast palettes do not automatically validate or repair contrast. Tooltips use existing author text; the outline reflects existing headings/landmarks. OpenDyslexic is an optional preference, not a guaranteed benefit.

## Before wider rollout

Check representative menus, forms, cookie banners, maps, media, mobile widths, 200–400% zoom and front-end editing in your theme. Include manual keyboard/screen-reader testing. A clean toolbar scan does not establish site conformance. Avoid competing accessibility overlays. If optimization delays scripts incorrectly, exclude the `grid-a11y` handle and plugin assets. Standard WordPress header/footer hooks are required.

## Rollback

Deactivate GRID Accessibility Toolbar, clear caches and reload. Effects disappear on the next load. Settings are retained for reinstall; remove the `grid_a11y_settings` option explicitly if needed. Visitor preferences remain until Reset or clearing site data. Page content and builder layouts are not rewritten.

## References

The original category and installation references were UserWay's widget, WordPress installation guide and WordPress.org listing. This is independent original code with no proprietary UserWay code, branding or service integration. WordPress Settings API and Beaver Builder editor documentation informed the integration. See FEATURE-PLAN.md for feature boundaries and remaining optional integrations.

## Test-site feature guide

[How to use the Accessibility Widget](https://marco2mrv.eu/?page_id=28) explains every control and profile, keyboard use, translation, privacy and reset. It links to open research and clearly distinguished W3C guidance. The guide is separate WordPress page content, not automatically created on each plugin installation. Reusable source: guide/widget-guide.html.

## Touch-only devices (1.11.0)

Cursor, reading mask, reading guide and hover tooltips are omitted on touch-only phones/tablets. Other controls remain available. Profiles and restored browser preferences cannot activate hidden controls. Detection uses coarse/fine pointer and hover capabilities, not viewport width, so small desktop windows and mouse-equipped hybrids keep their pointer controls. Detection runs on page load; reload after connecting a mouse/trackpad if needed.

## Administrator menu (1.11.0)

Open the dedicated **GRID-Arendal → Accessibility Widget** menu, identified by the official lavender flower. It is no longer a Settings submenu. Existing settings-page bookmarks redirect automatically. The normal administrator permission requirement is unchanged. GTranslate has its own separate settings page; use only one translation engine. The test site’s redundant Google Language Translator is deactivated, with its saved settings retained.

## Illustrated guide

The test-site guide now uses guide/widget-guide-designed.html, generated from the original text by guide/build-guide.py and guide/screenshots/media.json. It includes 22 actual screenshots with alternative text and responsive feature cards. On touch-only screens the introductory desktop overview is hidden; screenshots beside individual explanations remain available and pointer-specific examples are labelled. Screenshot media belongs to the test site and is separate from the reusable plugin ZIP.


## Public release updates (1.11.0)

No per-site credentials are needed. Install this ZIP once, then use the normal WordPress Plugins/Updates screens. Open GRID-Arendal → Plugin updates for status. Automatic installation is optional. See UPDATES.md for the maintainer publishing workflow.
