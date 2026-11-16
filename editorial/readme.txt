=== GRID Editorial Tools ===
Contributors: grid-arendal
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Independent editorial tools for GRID-Arendal websites. No accessibility widget dependency.

== Description ==
Optional front-end workspace, ACF groups and names, native permission checks, review, imports, notifications, protected files, coordinates and LearnPress connections. ACF, Beaver Builder/Themer, WooCommerce and LearnPress remain separate plugins. No permissions, mail recipients or site-specific integration paths are invented. Labels default on; other modules default off on a fresh site. Legacy settings and stable keys are retained.

== Installation ==
Upload and activate this ZIP. On an existing combined 2.x installation, install this plugin first; it waits safely until the combined plugin is replaced with the standalone widget 3.x. Then verify the existing workspace, roles and fields. Deactivating either split plugin leaves the other running and never deletes data.

== Changelog ==
= 1.0.0 =
* Extract editorial tools into their own plugin with independent settings, admin menu, runtime, version and update feed.
* Preserve existing workspace pages, ACF keys/values, label overrides, exclusions, review records and native permissions.
