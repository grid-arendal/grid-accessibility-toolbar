# GRID-Arendal

A reusable WordPress plugin combining visitor accessibility controls and configurable shared editorial tools. Install the **release ZIP**, not GitHub's source-code archive.

Accessibility and empty section-label overrides default on. Front-end editing, review, CSV imports, notifications, private files, coordinate editing and LearnPress connections are optional. Existing data keys, URL slugs, user permissions and accessibility settings are preserved. Other plugins and project-specific workflows are not automatically migrated or disabled.

When ACF is available, standard database-backed Page, Article, Resource and Team field groups are created for installed content types. Edit their fields through GRID-Arendal → Field groups; existing customisation and deliberate deletions are preserved. Workspace menu links follow native page and editorial permissions.

See [MODULES.md](MODULES.md) for module setup, dependencies and migration boundaries. ACF, Beaver Builder/Themer, GTranslate, WooCommerce and LearnPress remain separate plugins. No automated WCAG compliance claim is made.

## Updates and releases

Websites use the public update feed and published stable ZIPs without GitHub credentials. Updates appear in the normal WordPress update interface; automatic installation remains a site administrator's WordPress preference.

Set the matching stable version in the main PHP header/constant and readme, update release notes, review the source and either dispatch the release workflow or create a deliberate `release/x.y.z` branch from the reviewed commit. CI validates PHP, JavaScript, configuration/input tests, updater policy and the installable ZIP. Update metadata is published only after the release asset exists.

The directory remains `grid-accessibility-toolbar` for upgrade compatibility.
