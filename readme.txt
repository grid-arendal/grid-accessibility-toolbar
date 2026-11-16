=== GRID Accessibility Widget ===
Contributors: grid-arendal
Requires at least: 6.4
Requires PHP: 7.4
Stable tag: 3.0.1
License: GPLv2 or later

GRID-Arendal visitor reading preferences and optional GTranslate integration.

== Description ==
Accessible, keyboard-operable preference panel with text sizing, spacing, readable/OpenDyslexic fonts, contrast, highlighted links, reduced motion, reading aids, image hiding, page structure and transparent preference profiles. Stores optional visitor preferences locally; includes GRID-Arendal flower branding and an optional embedded GTranslate language selector. Core controls work without third-party services.

This plugin is independent of GRID Editorial Tools. It does not create ACF groups, editors, login icons, review queues, imports, maps or LMS connections. Install Editorial Tools separately for those features. GTranslate is separately installed for translation; it is not bundled.

Preferences do not repair inaccessible templates/content or certify WCAG compliance. Reduced motion cannot pause every embedded/canvas/script animation. Fonts/profiles are optional reading preferences, not treatment or safety guarantees. Browser zoom and assistive technology remain available. Beaver Builder and detected layout editors are excluded.

== Installation ==
Upload the release ZIP and activate. Open GRID Accessibility → Accessibility Widget. Configure controls, branding, translation and excluded paths. Clear site caches after changes.

Existing combined 2.x sites: install and activate GRID Editorial Tools first, then use Replace current with uploaded for this ZIP. The installed folder stays grid-accessibility-toolbar. Widget settings, visitor preference keys and asset URLs are retained; editorial settings/content remain owned by Editorial Tools. Deactivation never deletes data. See INTEGRATION-PLAN.md in the source repository for the rollout/rollback checklist.

== Updates ==
Standalone releases use widget-updates.json and v3.x.x release assets. The legacy combined updates.json feed remains frozen at 2.0.4 to prevent destructive automatic migrations. No per-site GitHub credentials are needed. WordPress automatic installation remains the administrator's choice.

== Changelog ==
= 3.0.1 =
* Remove the custom Plugin updates settings page. Update checks and installation remain available through WordPress Plugins and Updates.

= 3.0.0 =
* Split visitor accessibility into its own runtime, settings/admin surface and independent release/update channel.
* Preserve existing widget settings, visitor preference keys and installed directory.
* Remove editorial PHP, scripts, styles and Leaflet from this ZIP; add migration guidance when retained editorial settings need Editorial Tools.
