# Research and implementation sources

Reviewed 23 November 2026. This is a technical implementation record, not legal certification. The product implements a conservative opt-in model worldwide; international legal applicability and all controller obligations still require site-specific review.

## Norwegian/EEA basis

- [Datatilsynet: cookies and the 2025 Norwegian changes](https://www.datatilsynet.no/personvern-pa-ulike-omrader/internett-og-apper/cookies/). Norwegian device storage/access consent must meet GDPR consent standards. Equal attention to refusal, active choices, documentation and easy withdrawal informed the design.
- [Nkom: cookies](https://nkom.no/internett/informasjonskapsler-cookies). Technology-neutral storage/access rules and narrow necessary-service exceptions inform the scope; necessary exemptions must be assessed per site.
- [EDPB Cookie Banner Taskforce report](https://www.edpb.europa.eu/system/files/documents/2023-01/edpb_20230118_report_cookie_banner_taskforce_en.pdf). First-layer rejection, non-preselected optional purposes and non-deceptive presentation informed the UI. No copied banner implementation.
- [UK ICO: cookies](https://ico.org.uk/for-organisations/direct-marketing-and-privacy-and-electronic-communications/guide-to-pecr/cookies-and-similar-technologies/). Review current UK guidance and changing exemptions separately; the plugin does not claim every national exemption or law.
- [California Attorney General: CCPA](https://oag.ca.gov/privacy/ccpa). GPC is a relevant opt-out mechanism where applicable. The implementation conservatively holds all optional services off when GPC is present, worldwide. It does not implement all access/deletion/notice/minor-rights obligations of CCPA or other laws.

## Vendor interfaces

- [Google Consent Mode overview](https://developers.google.com/tag-platform/security/concepts/consent-mode) and [implementation guide](https://developers.google.com/tag-platform/security/guides/consent). Basic rather than advanced mode is used: the Google tag stays unloaded until its purpose is granted. All four relevant Google consent signals are explicit; advertising permissions remain denied.
- [Google cookie configuration](https://developers.google.com/tag-platform/security/guides/customize-cookies). GA cookie lifetime is bounded by the configured consent duration and cookie_update is disabled.
- [Clarity consentv2](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-consent-api-v2). Clarity gets a separate session-recording choice, analytics_Storage granted only for that choice, and ad_Storage denied. Its denied/no-cookie mode can still measure; this plugin instead avoids loading it at all before consent.
- [Clarity client API](https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-api). Text masking and the under-18 restriction are reflected in integration guidance. No custom visitor identity is passed.
- [Clarity cookie list](https://learn.microsoft.com/clarity/cookie-list). First-party and possible provider-domain identifiers must be reviewed against the actual project, consent mode and browser behaviour. First-party cleanup cannot erase third-party/HttpOnly cookies.

## Product/open-source references

- [CookieYes consent product](https://www.cookieyes.com/product/cookie-consent/) and [CookieHub](https://www.cookiehub.com/): configuration, granular controls, records and integration/audit workflow were considered. Proprietary code, certification and subscription claims were not reproduced.
- [CookieConsent](https://github.com/orestbida/cookieconsent) and [Klaro](https://github.com/kiprotect/klaro): open-source product architecture references. Neither runtime is bundled; this plugin's code is original GPL-2.0-or-later.
- [WordPress settings](https://developer.wordpress.org/plugins/settings/settings-api/), [REST permissions](https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/), and native admin menus/update filters inform platform integration.

## GRID policy reference

[GRID-Arendal disclaimer/privacy information](https://www.grida.no/about/disclaimer) was the organisation/context reference requested by the user. Its older continued-use cookie wording was not copied into the new notice: explicit opt-in and withdrawal are used instead. The generated draft is based on the actual configured services and must be completed/reviewed by the controller; this plugin does not silently publish or declare a policy approved. The pilot notice deliberately identifies pending site-owner review and keeps optional tracking disabled until that review is confirmed.
