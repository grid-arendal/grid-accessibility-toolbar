# Installing the independent GRID plugins

## Choose what the website needs

- **GRID Accessibility Widget 3.0.1** (`grid-accessibility-toolbar-3.0.1.zip`): visitor reading preferences, profiles, branding and optional GTranslate integration. Admin: **GRID Accessibility → Accessibility Widget**.
- **GRID Editorial Tools 1.0.1** (`grid-editorial-tools-1.0.1.zip`): ACF groups/names and optional staff workspace, account icon, review, imports, files, maps and LMS connections. Admin: **GRID Editorial → Modules & workspace / Field groups / Section & field names**.

Either plugin works without the other. Install release ZIPs via **Plugins → Add New → Upload Plugin**. Do not use GitHub source archives. WordPress 6.4+, PHP 7.4+ and a modern browser are required.

## Existing combined 2.x installation

1. Back up the database and installed plugin files. Record the current settings and workspace URL. Test a staging copy first for each different site configuration.
2. Install and activate **Editorial Tools first**. It waits safely while the combined plugin still runs the existing editor.
3. Upload the standalone widget ZIP and choose **Replace current with uploaded**. The directory remains `grid-accessibility-toolbar`; editorial settings and the existing shortcode page are adopted by Editorial Tools without rewriting content or ACF keys.
4. Clear only that site's hosting/page/CDN caches. Check visitor preferences, login return, role permissions, ACF values, actual draft saving, Beaver Builder links and mobile navigation.
5. Enable automatic updates separately for either plugin only if the site's owner wants them. No GitHub credentials are needed.

The old combined update feed remains pinned at 2.0.4. Websites must be deliberately migrated once before following the independent product feeds. No other websites were migrated by the access.grida.no pilot.

## New site

Install either or both products. GTranslate is optional for visitor translation. ACF is needed for editorial field/workspace features; Beaver Builder/Themer, WooCommerce and LearnPress remain separate dependencies for the features that use them. No vendor plugin is silently installed.

The widget defaults on; its preferences remain visitor opt-ins. Editorial labels default on; other editorial modules default off. Enabling the workspace provisions a private page and Page/Article sections when unconfigured. Starter groups are editable through Field groups. Choose the intended classic WordPress/Beaver menu for the account icon. Block Navigation needs a separate adapter. Configure special routes, recipients, file paths and coordinate keys per site.

See **INTEGRATION-PLAN.md** for feature ownership, per-site acceptance checks, rollback and release policies. Deactivation never deletes existing settings, groups, content or files. The combined 2.0.4 release remains available for rollback.

Updates are managed through WordPress **Plugins** and **Dashboard → Updates**. Neither GRID plugin adds a separate Plugin updates settings page.
