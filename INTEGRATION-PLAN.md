# GRID website plugin integration plan

## Two products, no runtime dependency between them

| | GRID Accessibility Widget 3.0.0 | GRID Editorial Tools 1.0.0 |
|---|---|---|
| Audience | Website visitors | Authorised content staff |
| Owns | Reading preferences, fonts, profiles, translation UI and widget branding | ACF starter groups/labels, workspace, login icon, review, imports, notifications, private files, maps and LMS adapters |
| Install directory | `grid-accessibility-toolbar` (unchanged) | `grid-editorial-tools` |
| Settings | `grid_a11y_settings`; visitor storage keys unchanged | `grid_arendal_tools`, `grid_arendal_labels` and existing editorial metadata unchanged |
| Admin | GRID Accessibility | GRID Editorial |
| Optional dependencies | GTranslate for translation only | ACF for fields/workspace; other modules declare their own dependencies |
| Fresh-site defaults | Widget enabled, preferences remain visitor opt-ins | Labels on; workspace/review/imports/notifications/files/maps/LMS off |
| Independent updates | `widget-updates.json`, `v3.x.x` releases | `editorial-updates.json`, `editorial-v1.x.x` releases |

Either plugin can run alone. Neither plugin loads the other's PHP, scripts, styles, fonts, vendor tree or version constant. Each bundles its own small licensed runtime dependencies. Sharing a public GitHub repository is source organisation, not a WordPress dependency: each has its own ZIP, version, update channel and release selection. A future repository move can be made through its updater without renaming the installed folder.

ACF, Beaver Builder/Themer, GTranslate, WooCommerce and LearnPress stay separately installed and maintained. These plugins do not bundle vendor plugins, turn off unknown site snippets, change user roles, create mail recipients, migrate unrelated content or force a shared header/footer onto websites.

## Existing combined 2.x websites: staged migration

1. Back up the database and installed plugin files; record the existing widget settings, workspace page/URL, selected sections/groups, excluded system pages, label overrides, review queue, recipients, file-storage path and menu selection. Use a staging copy for each different theme/editor configuration.
2. Install and activate **GRID Editorial Tools 1.0.0 first**. Beside the combined 2.x plugin it deliberately waits, so the existing editor continues serving requests. This works regardless of plugin loading order.
3. Upload **GRID Accessibility Widget 3.0.0** using WordPress's Replace current with uploaded action. Its installed directory stays unchanged. The staged Editorial Tools now takes ownership of the preserved editorial settings and shortcode; the widget owns only its visitor features.
4. Verify both admin menus, the original workspace URL, ACF groups/values, role restrictions, guest login return, system-page exclusions, actual draft saving, Beaver Builder entry, mobile navigation and visitor preferences. Clear only that site's caches.
5. Test each plugin deactivated on staging: editorial work must continue with the widget off, and the widget must work with Editorial Tools off. Restore the intended activation state.
6. Roll out to other websites in small batches only after recording the site's result. Keep the backup and combined 2.0.4 ZIP available during the first editorial cycle.

**The old combined `updates.json` feed remains pinned at 2.0.4.** Existing websites do not receive a destructive automatic split. Migration is a one-time explicit two-ZIP operation. After migration, normal WordPress updates are separate and require no GitHub/site credentials. A future legacy bridge must not be published without an activation-state migration check; the frozen channel must not be repurposed for widget-only releases.

If the widget is replaced before Editorial Tools is installed, an administrator warning identifies retained editorial settings and the missing plugin. Install Editorial Tools to resume them. No migration path deletes settings, ACF definitions, content, review records or files.

For rollback, replace the widget with the backed-up combined 2.0.4 ZIP while Editorial Tools remains installed: Editorial Tools detects the legacy runtime and waits again. Restore the database backup only if testing changed content; plugin replacement itself does not require a data rewrite. Re-check activation state, settings and caches.

## Fresh websites

- Accessibility only: install the widget; choose controls/branding and optional translation. It does not install ACF, create field groups or add an editor/login menu item.
- Editorial only: install Editorial Tools, activate ACF if fields/workspace are needed, and select only required modules. Standard editable Page/Article groups appear; Resource/Team groups appear only for their installed content types.
- Enabling the workspace for the first time creates a private shortcode page and default Page/Article sections if none exist. It does not grant permissions or expose system pages intentionally excluded by the administrator. Existing configuration and deliberate deletions are retained.
- Review, notifications, imports, protected files, maps and LMS remain deliberate choices. Review recipients, private storage directories, coordinate field keys and project-specific post types must be configured per site.
- Select the intended classic WordPress/Beaver menu for the login icon. Automatic selection chooses the first main menu, skipping footer/social/secondary menus. Block Navigation menus need a separate adapter; they are not silently rewritten.
- Public ACF fields must be connected to the site's own templates. Creating a field group does not modify a Beaver layout or invent field values.

## Per-site integration checklist

Record the site owner, environment, WordPress/PHP versions, active theme, builder mode, field-group keys, actual content types, role capabilities, menu implementation, translation provider, caching/CDN, commerce/account routes, notification recipients and any legacy custom editor. Preserve stable keys and URLs; use labels for naming differences. Do not copy private file paths, recipients, API keys or account routes from another project.

Use one representative existing record and one disposable draft per configured section. Check author/editor/admin/read-only/guest behaviour; published changes and review rules; field values and relationships; editor conflicts; mobile keyboard/touch; and cached guest versus authenticated responses. Delete only test fixtures after verification. Optional private files, maps or LMS require their own acceptance checks before activation on each site.

## Development and release rules

Develop against the relevant product directory. Keep separate changelogs, version constants, runtime ZIPs and stable metadata feeds. Run product input/update-policy tests plus the coexistence/migration matrix. The builder accepts a product explicitly and packages only its runtime. The release workflow publishes a product feed only after that product's verified release asset exists; it cannot write the frozen legacy feed.

No automatic WCAG compliance, disability treatment or universal theme compatibility is claimed. The widget complements accessible content and templates. Editorial integrations preserve native WordPress permissions and fail closed for unsupported field or workflow types.
