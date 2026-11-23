=== GRID Cookie Consent ===
Contributors: grid-arendal
Requires at least: 6.4
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later

Self-hosted, customisable cookie consent, separate Google Analytics/Clarity purposes and local receipts. Supports compliance; does not certify it.

== Description ==
Equal Accept/Reject controls; granular external content, analytics, session recording and declared marketing purposes; editable colours/layout/language; English and Norwegian Bokmål plus local custom translations; persistent Cookie settings; local consent records and policy snapshots; policy-link selection and draft generation. No framework, remote CMP, CDN font, subscription, geo-IP lookup or per-site GitHub credential.

Google Consent Mode v2 is configurable. New installations select advanced mode: limited cookieless Google measurements may be sent before a choice and after rejection, while analytics cookies require consent. Basic mode blocks Google entirely until analytics consent. Existing saved modes are retained; legacy installations without a saved mode retain basic mode. Review and connected IDs are required before managed measurement runs. Google advertising signals stay denied. Clarity loads only for session-recording consent, uses consentv2 with ad storage denied and masks page text. Returning optional consent is verified locally before a loader runs. Withdrawal clears accessible first-party tracking cookies and reloads to stop runtimes. GPC blocks Google entirely and keeps consent-based optional services off worldwide. Cookies expire within at most 180 days; material policy/configuration changes require a new choice.

Known competing GA/GTM, Clarity and recognised advertising snippets are removed from buffered HTML before parsing. GTranslate, external fonts and frames can be consent-gated. Unknown dynamic/server-side trackers, CSS pixels, proxies, browser extensions and arbitrary containers need separate audit/integration. This is not a universal scanner, a Google-certified CMP, IAB TCF implementation, or a guarantee of Norwegian/GDPR/global legal compliance.

== Installation ==
Upload the release ZIP and activate. Setup checks Site Kit by Google and Microsoft Clarity, offering native install/activate actions when missing. First activation imports valid connected IDs only into empty fields; an administrator can repeat that import. Account authorisation must be completed in the official plugins. The banner remains available if integrations are missing. Open GRID Tools → Cookie consent. Supply the controller/contact, GA4/Clarity IDs and policy URL; or choose a published policy page / generate an editable draft. Drafts are never automatically published. Review site services, notices, vendor contracts, international transfers, child/sensitive-data restrictions and all page integrations before enabling optional services. Unreviewed setups remain blocked.

Recognised Accessibility/Editorial settings are grouped under one GRID Tools menu using reversible UI aliases. Their files, options, callbacks and capabilities are retained; unknown menu extensions keep their original parents. Deactivation restores the original menus. The plugin is independent of the other GRID plugins. Updates use WordPress Plugins/Updates and published GitHub packages, with a dedicated consent update feed and SHA-256 verification.

Use [grid_cookie_settings] for another preferences link; [grid_cookie_policy] for a dynamic declaration. Generated drafts contain editable policy text so the notice is not just a runtime-only shortcode. Remove the draft warning only after review. Retest and clear caches when changing settings. Deactivation does not delete records or policies; disable other tracking integrations before disabling the consent blocker.

== Privacy ==
One necessary choice cookie is written only after a decision. A cross-tab sync entry may be used after a choice. No IP, browser agent, account identity or browsing URL is written to local consent receipts. Server logs and other plugins are outside that receipt store. The controller manages retention (at least the choice lifetime, at most 365 days) and applicable rights requests. Receipt IDs are pseudonymous, not anonymous identity proof. Denials still work if receipt recording is temporarily unavailable; grants fail closed. At most 100,000 receipts are retained; older records are pruned by WP-Cron according to retention.

== Changelog ==
= 1.0.3 =
* Live appearance/position preview, true full-width layout and branded, reversible GRID menu grouping.

= 1.0.0 =
* Independent local consent UI, managed GA4/Clarity, server-side known-snippet blocking, proof records, policy drafts, safe GRID menu grouping and native WordPress updates.
