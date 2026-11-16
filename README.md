# GRID-Arendal WordPress plugins

Two independently installed and updated products share this source repository:

| Product | Source | Version | Release ZIP |
|---|---|---|---|
| GRID Accessibility Widget | repository root | 3.0.0 | grid-accessibility-toolbar-3.0.0.zip |
| GRID Editorial Tools | editorial/ | 1.0.0 | grid-editorial-tools-1.0.0.zip |

Neither runtime depends on the other. Install either or both. Third-party WordPress dependencies remain separate.

Read [INTEGRATION-PLAN.md](INTEGRATION-PLAN.md) before migrating an existing combined 2.x site. Install Editorial Tools first, then replace the combined plugin with the widget. Existing settings, field keys/values, workspace URLs and permissions are preserved. The legacy updates.json feed is frozen at 2.0.4; new products use widget-updates.json and editorial-updates.json.

Release a product explicitly using release/widget/x.y.z or release/editorial/x.y.z branches, or the product-selecting workflow. Install release ZIPs, not GitHub source archives. See each product's readme and release notes. No automated WCAG compliance claim is made.
