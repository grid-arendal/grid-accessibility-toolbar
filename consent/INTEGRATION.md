## Google measurement modes

New installations select advanced Consent Mode by default. After service IDs and setup review, Google receives cookieless measurements before a choice and after rejection. Analytics cookies require consent. Basic mode blocks Google until consent. Existing saved modes and legacy installations without a mode retain their prior basic behaviour. GPC blocks Google entirely in either mode. Clarity and custom analytics scripts remain opt-in.

Advanced mode is a technical setting, not an exemption from Norwegian/EU rules. The controller must assess its lawful basis, transparency, necessity, vendor terms and international transfers. Publish the matching notice before enabling. Google’s measurement may include page/referrer/device details and expose IP addresses even without cookies. Remove personal data from page URLs and titles. Previously documented “zero external requests before consent” results apply to basic mode only.

Setup checks the official Site Kit by Google and Microsoft Clarity plugins, with WordPress-native install/activate actions. Account connection remains with the site owner. Valid connected IDs are imported into empty GRID fields at first activation, or via the explicit import action. Existing IDs/settings in all plugins are preserved. The GRID blocker continues to suppress competing frontend snippets; the managed loaders control measurement. Do not enable unrelated Site Kit/Clarity marketing or server-side collection without a separate review.

# GRID Cookie Consent: deployment and limits

This is a separate WordPress plugin, independent of GRID Accessibility Widget and GRID Editorial Tools. All runtime code, styles and consent records are local to the site. There is no external CMP account, geolocation lookup, framework or UI font download. The source is GPL-2.0-or-later; the GRID flower is supplied by GRID-Arendal for its branding. CookieYes, CookieHub, Klaro and CookieConsent were used as product/architecture references; proprietary code was not reproduced.

## Setup on each website

1. Inventory every tracking path: theme snippets, Site Kit, GTM, Clarity, header/footer injectors, embeds, marketing pixels, CSS/background URLs, server-side collection, proxies, caches and delayed-JS optimisers. Remove duplicate injectors or verify they are suppressed. Do not run a second CMP at the same time.
2. Install the release ZIP. Known GA/GTM, Clarity and recognised advertising snippets in buffered public HTML are blocked immediately. Optional managed services remain off while setup is unreviewed.
3. Open **GRID Tools → Cookie consent**. Set the controller/contact, GA4 measurement ID and Clarity project ID. The plugin becomes the only owner of their frontend loaders; vendor dashboards/plugins remain installed and their data/settings are not changed.
4. Link an existing policy URL, select a published WordPress page, or create an editable **draft**. Nothing is automatically published. The draft is a starting point, not legal approval. Add the site's actual processors, contact/forms/accounts/payment processing, hosting/security log retention, international transfer safeguards, sensitive/child-data rules, and applicable rights. Review and publish it in WordPress; then select its public URL.
5. Review every configured purpose and provider. Google Analytics and Clarity are separate choices; Google advertising and Clarity ad storage stay denied. GTranslate, externally hosted Google Fonts and rendered external frames can be gated as External content. For a strictly necessary, user-requested iframe (for example an audited payment step), a developer may add `data-grid-cookie-essential="true"`; this is not appropriate for tracking. Known tracking frames are still removed. Unknown dynamic embeds need their own integration.
6. Only enable optional services after the controller has completed the review. Test an anonymous browser before consent, after rejecting, each purpose individually, accepting, withdrawing, returning, expiry, notice changes and GPC. Then clear only that site's hosting/page/CDN caches. Re-test checkout, login, forms, the editor and embeds.
7. Keep a permanent preferences link using `[grid_cookie_settings]` if desired; the plugin also supplies one. Declared custom HTTPS services require unfiltered_html permission, provider/purpose/category and storage names. Explicit `data-grid-consent="analytics|experience|functional|marketing"` script tags are held inert and replayed only for a permitted, configured purpose.

## Consent behaviour

Necessary storage is not an optional toggle. All optional choices start off. Accept and Reject use the same size/style and are on the first layer. Neither scrolling, continued browsing nor closing the preferences dialog grants consent. The banner does not block access to ordinary content. The preferences dialog is keyboard-operable and restores focus.

GA4 uses basic Consent Mode v2: the Google tag is not loaded before consent, including no denied/cookieless pings from this loader. Google signals and advertising personalisation are disabled. Clarity is not loaded before its separate purpose is granted; consentv2 receives analytics_Storage=granted and ad_Storage=denied, with page text masked. Clarity is not suitable for sites targeting under-18s; sensitive pages/forms still need exclusions in the project/site.

Returning optional consent requires a same-origin, indexed receipt lookup before loaders run. New grants fail closed if recording fails. Denials still work if recording is unavailable. Withdrawal first stores the reduced choice, disables GA, clears accessible first-party tracking cookies and reloads immediately to stop already loaded runtimes; an in-flight same-origin request records it. A short-lived pending receipt can be recovered only if the server recorded the matching decision. Third-party/HttpOnly cookies and data already sent to providers cannot be erased by this plugin.

Choices expire after at most 180 days, and changed purposes, providers, copy or linked local policy text require a new choice. GPC keeps all optional services off worldwide. This conservative approach avoids geo-IP data collection; it does not implement every international law, age/parental-consent regime, AdSense certification or IAB TCF. Advertising publishers needing a certified CMP should use the relevant certified solution.

## Local consent evidence

Receipts contain a random decision ID, UTC time, purposes, language, GPC flag, notice revision and server HMAC. Policy snapshots are retained separately. No visitor IP, user agent, account identity or browsing URL is stored in these tables. Receipt IDs are pseudonymous; they do not prove a person's identity or that every unknown site integration obeyed the choice. Administrators can export records and notice snapshots from Consent records. Retention is configurable up to 365 days and never shorter than consent lifetime; WP-Cron prunes old receipts. A 100,000-record cap fails closed rather than growing indefinitely. Review capacity and retention before a high-traffic rollout. Hosting logs and vendor retention are separate obligations.

Same-origin POST checks and strict schemas limit receipt input; the public endpoint is not a bot-authentication boundary. Use normal hosting/WAF controls for abusive traffic. The cookie is host-prefixed on HTTPS root sites. Notice revision changes and signature validation prevent edited cookie values from enabling a new purpose.

## Styling and live preview

Choose floating left/right or an edge-to-edge full-width banner. Position, button/panel colours, corners and font preview immediately without saving. Preview does not store consent or run trackers. Colours are validated on save for 4.5:1 text contrast; the stylesheet respects reduced motion and supplies 44px+ targets. English and Norwegian Bokmål are included; additional local translations use the advanced JSON fields. No consent-UI content is sent to a translation service. Stable CSS variables/classes support further scoped theme styling.

## Safe GRID menu grouping

Cookie Consent owns a reversible admin presentation layer. Known GRID Accessibility/Editorial pages are grouped under one branded GRID Tools menu, using their original callbacks, page slugs and capabilities. No other plugin file or option is rewritten; native WordPress update integration is unchanged. Unknown submenu extensions retain their original parent rather than being stranded. Turn off grouping to restore original menus without disabling consent protection. Deactivation restores menus too, but disable other tracking injectors before deactivating consent, otherwise they may resume unconsented tracking.

## Performance and compatibility

The UI has no framework, no remote CMP and no font/image fetches. Frontend JS/CSS gzip sizes are recorded by the test suite. No database operation is needed before a choice; a repeat optional choice uses one indexed local verification request. Recording happens on choices only. There is no visitor-triggered sitewide scanner.

The compatibility blocker buffers public HTML and rewrites known snippets before browser parsing. This trades streaming/memory overhead for reliable handling of known pre-existing markup. Its overhead depends on page size/hosting; it is not a promise of zero slowdown. JSON/REST/feed/admin responses are excluded. Dynamic injections by unmarked scripts, CSS pixels, server-side tracking, response-header preloads, first-party proxies and browser extensions cannot be universally controlled by HTML rewriting. Audit every site's network traffic. Essential cookies from login/checkout are not deleted. For strict CSP, supply a nonce with `grid_cookie_csp_nonce` and permit the local assets; exclude consent runtime/config/defaults from delayed-JS rewriting. Blocking remains conservative if JavaScript fails, but the settings UI then needs JavaScript.

## Updates and rollout

Independent source: `consent/` in the GRID public repository. Releases use `consent-vX.Y.Z`, their own ZIP and `consent-updates.json`. The native WordPress Plugins/Updates screens receive updates; there is no separate update settings page and no per-site credential. Package URLs are restricted to the exact product release; the downloader verifies SHA-256 before installation. Stage each site's update, test integrations, then roll out. No other websites are modified by the access.grida.no pilot.

## GTranslate loader ordering (1.0.5)

GTranslate settings and loaders are consent-gated together, including locally hosted scripts and native data-gt-widget-id metadata. This prevents a local loader from running before its held settings and failing to initialise the selector after consent. Original URL/domain/widget attributes are retained for the official loader. Rejection, withdrawal and GPC keep the loader off. No GTranslate vendor files need modification.
