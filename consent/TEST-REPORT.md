# Consent plugin test report

The plugin is a compliance-support tool, not legal certification. Test fixtures intercept vendor requests; they do not send testing data to Google/Microsoft.

## Isolated PHP

The security/input suite runs on PHP 7.4 and 8.5. It covers bounded consent/retention, readable colours, identifier and service validation, privilege checks, changed notices, known-script/frame/pixel/preconnect blocking, necessary-frame exceptions, same-origin checks, immutable local receipts, signature/tamper/revision/GPC checks, fail-closed storage, verified release URLs and safe GRID menu aliases (including top-level hook recursion and unknown extensions). Runtime syntax checks cover all six PHP files.

## Browser fixtures

The browser suite uses the production PHP blocker and locale definitions, production JS/CSS and a local proof server. It verifies zero vendor requests before consent and after rejection; GA-only and Clarity-only choices; denied ad signals; returning verified choices; withdrawal and cookie removal; GPC; Norwegian labels; modal Escape/focus; policy renewal; unreviewed fail-closed setup; edge-to-edge 1280/390 banners; instant unsaved position/colour preview; and no preview storage/consent requests. Axe checks run on banner/dialog views at 1280, 820, 390 and 320 pixels. Main-thread JS/CSS sizes and gzip totals are recorded in test-results/browser.json.

## Native access.grida.no pilot

Pre-install network inspection found GA4, GTM, Clarity, GTranslate, Google Fonts and an advertising bootstrap. Managed IDs were taken from actual site output, not invented. Existing vendor plugins were not deactivated or edited. The consent blocker removes known competing snippets; the pilot remains unreviewed, so optional tracking stays off.

The plugin generated an editable WordPress policy draft from the configured services. A site-specific testing notice was published at https://access.grida.no/cookies-and-privacy-choices/ and linked through the normal setup fields. It is explicitly pending site-owner review and does not copy the reference policy's old continued-use consent rule.

Every recognised GRID settings route opened successfully under the single menu. Turning grouping off restored both original GRID menus; re-enabling regrouped them without disabling consent. Existing plugin files, options and capabilities were not rewritten. The branded flower was measured at exactly 20×20px. The native preview updates position/colour without saving, and full width is selected on the pilot.

Public home, policy and shop checks at desktop/tablet/mobile sizes found zero external requests before choice and after rejection, no GA/Clarity cookies, zero tested axe violations, and no page overflow. Real WordPress receipts were signed and verified; modified purpose values were rejected with HTTP 403. Detailed evidence remains in pilot-2026-11-23 locally; raw site/browser data is not published to GitHub.

## Boundaries

Unrecognised dynamic/server/proxy/header tracking and provider-domain/HttpOnly cookie removal are outside automatic guarantees. All pages, caches, optimisers, essential checkout/form integrations, vendor agreements, transfers, minors/sensitive-data restrictions and the final notice need controller review before enabling optional services or deploying to other websites. Browser automation and axe are not exhaustive accessibility or legal audits.
