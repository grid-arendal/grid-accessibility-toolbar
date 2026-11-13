=== GRID Accessibility Toolbar ===
Contributors: grid-arendal
Tags: accessibility, reading, toolbar
Requires at least: 6.4
Requires PHP: 7.4
Stable tag: 1.10.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Independent visitor reading preferences. Core controls run locally without an account or analytics; optional GTranslate integration uses external translation services. No automated compliance claims.

== Description ==

Seventeen optional preferences, shown in fourteen compact control tiles: text size (100/115/130/150%), text spacing, Arial/Helvetica readable font, locally hosted OpenDyslexic (Dyslexia Friendly), dark/light contrast, highlighted links, reduced CSS motion, large pointer, a reading guide, an adjustable reading mask, independent line height, text alignment, media saturation, image hiding, author-text tooltips, a page outline and stronger focus indicators.

Keyboard-operable disclosure with a labelled nonmodal dialog, Escape dismissal, focus return, native selects, pressed-state buttons and polite status messages. The panel uses Shadow DOM to isolate its styling from themes and page preferences.

Administrators can enable or disable the toolbar, choose controls, set the launcher side, bottom offset and color, edit the panel title and launcher label, link an accessibility statement, exclude page paths and disable preference storage.

Works on public pages that call standard WordPress wp_head/wp_footer hooks. Hidden in WordPress administration, previews, feeds, embeds, Customizer and detected front-end editing sessions. Beaver Builder public layouts are supported without changing saved layouts. Beaver Builder's editor is deliberately excluded.

This is original code, independent of UserWay. It does not reproduce UserWay services, remediate underlying markup, supply a screen reader or generate alternative text or certify WCAG/legal compliance. A toolbar cannot replace accessible design, content remediation and manual testing.

== Installation ==

1. In WordPress, choose Plugins > Add New Plugin > Upload Plugin.
2. Upload grid-accessibility-toolbar-1.10.0.zip and click Install Now.
3. Activate GRID Accessibility Toolbar.
4. Open GRID-Arendal > Accessibility Widget and save your preferred options.
5. Clear site/CDN caches and test a public page while signed out.

No signup or API key is required. Repeat on each site. Multisite network activation is supported with per-site settings and lazy defaults for new sites; there is no shared network settings screen.

== Private updates ==

An optional server-held GRID_A11Y_GITHUB_TOKEN credential enables updates from the private grid-arendal/grid-accessibility-toolbar repository. It is never included in the plugin ZIP or stored by this plugin in its settings. Only published stable x.y.z releases with an installable plugin ZIP are considered. Drafts, prereleases, tags without releases and branches are excluded. WordPress checks periodically; automatic installation is an administrator choice. Without a credential the widget still works, with no GitHub requests from this integration. See the separately supplied PRIVATE-UPDATES.md.

== Privacy ==

With remembering enabled, the plugin stores only preference values in localStorage under a site-specific grid-a11y-v1-* key. The core reading controls use no cookies, telemetry, third-party font services or external runtime requests. When GTranslate is installed and its selector is enabled, GTranslate loads its external widget and translation engine and sends page text to its translation service when used. GTranslate can remember a visitor-selected language separately from these reading preferences. Reset preferences removes the key. Disabling remembering removes the key on the next eligible page visit. Storage restrictions are handled gracefully. Configured private update checks contact GitHub server-to-server to retrieve release metadata, source headers/readme and update packages. Credentials are sent as request headers to the repository API, not in browser links; cross-host download redirects remove those headers. No visitor page content or reading preferences are sent by the updater. Other site plugins may collect data independently.

== Limitations ==

Modern browsers with Shadow DOM, CSS custom properties and :where/:not support are required. Internet Explorer is not supported. JavaScript is required; without it, the original page remains available and the launcher stays absent.

Page preferences can change layout and may expose theme overflow problems. Contrast modes replace CSS background images, including meaningful ones; they are optional presentation modes, not a contrast audit. Readable font uses Arial/Helvetica; Dyslexia Friendly uses OpenDyslexic, with regular, bold, italic and bold-italic styles. These are optional preferences, not a treatment or a guaranteed benefit for every reader. The combined Font style control cycles OpenDyslexic, Arial and Site default; text size and spacing remain independent. CSS motion reduction also pauses native HTML video while enabled. It does not universally pause GIFs, canvas, JavaScript-driven animations or embedded third-party media, and it does not force video to resume when disabled. Pointer customization depends on browser/device support. The reading guide follows a mouse/pen and is decorative; on touch it stays at mid-screen. Text scaling does not change iframe/canvas/SVG text, code, known icon fonts, editable regions or explicit opt-outs.

Theme-specific testing and manual keyboard/screen-reader checks remain necessary. The plugin does not make underlying menus, PDFs, forms, captions or content accessible.

== Uninstall and rollback ==

Deactivate to remove the toolbar and its page effects on the next load. Clear caches. Delete the plugin through WordPress if no longer needed. Administrator settings are deliberately retained in the grid_a11y_settings option for reinstall; browser preferences are retained until Reset or clearing site data. Removing this plugin does not rewrite posts, builder layouts, media or theme files.

== Preference profiles ==

Eight transparent presets: Motor Impaired, Blind, Color Blind, Dyslexia, Low vision, Cognitive & Learning, Seizure & Epileptic, and ADHD. Each explains its effects and limits. The Blind profile opens navigation aids; it does not replace a screen reader. The Seizure & Epileptic profile is a reduced-motion preference, not a seizure-safety guarantee. Only concrete preferences are saved, not the selected profile label. Profiles respect site-disabled controls.

== Bundled fonts ==

OpenDyslexic is by Abbie Gonzalez. The unmodified font binaries are distributed under SIL OFL 1.1; see assets/fonts/OFL.txt and THIRD-PARTY-NOTICES.txt. OpenDyslexic is fetched from your own site only when selected. The interface uses locally hosted Geologica Regular and SemiBold, also under SIL OFL 1.1; see assets/fonts/Geologica-OFL.txt.

== Changelog ==

= 1.10.0 =
* Optional private GitHub release updates through the native WordPress update interface.
* Server-held read-only credentials; stable release assets only, with no branch/source-archive fallback.
* New GRID-Arendal > Plugin updates setup/status page.

= 1.9.0 =
* Dedicated GRID-Arendal administration menu with the official flower icon and Accessibility Widget submenu.
* Previous settings-page bookmarks redirect to the new menu.

= 1.8.1 =
* Touch-only devices omit cursor, reading mask/guide and hover tooltips. Profiles and saved settings cannot activate these hidden controls.
* Detection uses input capabilities rather than screen width; desktop and mouse-equipped hybrids retain controls.

= 1.8.0 =
* Compact official flower icon with a linked GRID-Arendal credit.
* Administrator dependency prompts offer GTranslate installation or activation when translation is enabled. Core controls remain independent.

= 1.7.0 =
* Reading aids cycles through large cursor, adjustable reading mask, reading guide and Off.
* Mask height and dimming are adjustable; pointer/touch and keyboard focus tracking preserve page interaction.

= 1.6.0 =
* Combined font control cycles through OpenDyslexic, Arial and the site default.
* GRID-Arendal 2025 lavender identity, official logo and locally hosted Geologica interface fonts.

= 1.4.1 =
* Automatically suppresses duplicate GTranslate/Google Language Translator floating controls where the embedded selector is available. Original settings are preserved for deactivation.

= 1.4.0 =
* Added optional official GTranslate dropdown inside the accessibility panel using a light-DOM slot.
* Included loading/failure feedback, accessible labeling and an administrator integration switch.
* GTranslate remains a separately installed plugin and provides page translation; toolbar labels follow the site interface language.

= 1.3.0 =
* Added seven local controls and eight transparent, reversible profiles.
* Profiles apply only site-enabled preferences and do not store a disability label.
* Reduced motion also pauses native HTML video; embedded and script motion may remain.

= 1.2.0 =
* Added Dyslexia Friendly with four locally hosted OpenDyslexic font styles and their license.
* Preserved saved settings and added a persistent administrator opt-out.
* Font controls switch independently of text size and spacing.

= 1.1.0 =
* Original SVG icons, circular launcher, branded header and responsive control tiles.
* Clear on/off indicators and expandable preference information.

= 1.0.0 =
* Initial independent toolbar, site settings, local preferences and editor safeguards.
