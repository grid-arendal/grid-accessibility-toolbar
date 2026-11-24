# GRID-Arendal WordPress plugins

Three independently installed and updated products share this source repository:

| Product | Source | Version | Release ZIP |
|---|---|---|---|
| GRID Accessibility Widget | repository root | 3.1.1 | grid-accessibility-toolbar-3.1.1.zip |
| GRID Editorial Tools | editorial/ | 1.0.1 | grid-editorial-tools-1.0.1.zip |
| GRID Cookie Consent | consent/ | 1.0.3 | grid-cookie-consent-1.0.3.zip |

The runtimes have no dependency on one another. Install any combination. Third-party WordPress dependencies remain separate.

Read [INTEGRATION-PLAN.md](INTEGRATION-PLAN.md) before migrating an existing combined 2.x site. Install Editorial Tools first, then replace the combined plugin with the widget. Existing settings, field keys/values, workspace URLs and permissions are preserved. The legacy updates.json feed is frozen at 2.0.4; new products use widget-updates.json and editorial-updates.json.

Release a product explicitly using release/widget/x.y.z or release/editorial/x.y.z branches, or the product-selecting workflow. Install release ZIPs, not GitHub source archives. See each product's readme and release notes. No automated WCAG compliance claim is made.

Cookie Consent has its own consent-updates.json feed, consent-vx.y.z tags and release/consent/x.y.z workflow. Read [its integration guidance](consent/INTEGRATION.md) before enabling optional services. It supports consent compliance but cannot certify Norwegian/GDPR/global compliance.

For every GRID site, build widget changes from the shared main branch and distribute the same numbered widget release through widget-updates.json. Test on Access first. Configure project-specific branding in WordPress. Publish the next version above every distributed widget version, including pilots.
