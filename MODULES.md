# GRID-Arendal 2.0 — modules and migration

The existing accessibility plugin is now named **GRID-Arendal**. Its directory, settings keys and public update feed remain unchanged so existing websites can update normally.

The shared tools were written as configurable modules, informed by the CAMCA, BCK Hub, NAMCOB and LMS work. They are not blind copies of site-specific snippets. Project-specific post IDs, field keys, account routes, hard-coded mail recipients and server paths are not transferred between sites.

## Modules

| Module | What it provides |
| --- | --- |
| Accessibility Widget | Existing visitor controls, profiles, branded interface and embedded GTranslate selector. |
| Section and field names | ACF group, field and tab labels; plural/singular content-type labels. Storage keys, relationships, values and URL slugs stay unchanged. |
| Front-end content workspace | Private, permission-based lists; search before pagination; sortable columns; ACF forms; rich text; contextual edit links; native Beaver Builder links for builder content. |
| Editorial review and activity | Published content stays live until approval. Pending changes are stored separately. Database advisory locks and version checks reject concurrent/stale saves. A bounded activity history records editorial actions. |
| CSV resource imports | Administrator-only, validated preview and explicit confirmation; at most 200 UTF-8 rows / 1 MB; new drafts only; replay protection. |
| Editorial notifications | Explicitly selected users with current edit/publish permission receive review links. No default recipients. |
| Private resource files | Per-record edit permission and signed downloads; files outside the web root; no public media URL; PDF, common office/image/text formats and ZIP; 10 MB per file. |
| Map editing | Locally hosted Leaflet coordinate picker for configured ACF latitude/longitude keys, with direct keyboard-editable coordinate fields and server bounds validation. No coordinates are invented. |
| LMS connections | Published LearnPress course cards via `[grid_courses ids="123,456" limit="6"]`, with native editor links for authorised staff. It does not edit courses, lessons, enrolments, payments or learner progress. |

Only **Accessibility Widget** and **Section and field names** default on. Label overrides start empty. Existing accessibility preferences are retained. No roles/capabilities are added, no legacy plugins/snippets are disabled, and no content is automatically migrated.

## Configure a website

1. Open **GRID-Arendal → Modules & workspace**. Select the required modules. ACF is required for the workspace and field labels; LearnPress is required for LMS connections. Beaver Builder/Themer, ACF, GTranslate, WooCommerce and LearnPress remain separately maintained plugins.
2. Standard editable ACF groups are created automatically when ACF and their content types are available: Page details, Article details, Resource details (WooCommerce products) and Team details (AWSM Team). Open **GRID-Arendal → Field groups → Edit fields** to change their structure in ACF. Display-name overrides remain available under Section & field names. Existing edits and intentional group deletions are preserved; a later-installed content type receives its starter group then. These groups do not automatically change public Beaver templates.
3. Create a normal WordPress page containing `[grid_workspace]`; select its page ID in the module settings. The workspace sends private/no-store/noindex headers and requires sign-in and existing editing rights.
4. Include only the content types needed on that site. Choose their editable ACF groups. Unselected fields and metadata are not accepted from form submissions.
5. Use **Section & field names** for display-name overrides. Empty values restore the originals. Custom post-type keys, existing slugs and stored ACF keys are never renamed.
6. Optionally enable review and choose notification recipients. Reviewers must still have native per-record editing and publishing permission. WordPress authors/editors keep their existing authority; legacy RMS/NMS-only roles need a deliberate permission review before migration.
7. For private files, create a writable directory **outside** the web root and supply its absolute path. The plugin refuses public-root/ancestor directories. Private-file readers must be able to edit the owning item; this is not a general staff document-sharing permission.
8. For maps, enter the configured ACF coordinate **field keys**. Leaflet is bundled locally; opening the map contacts OpenStreetMap for tiles, with attribution. Existing Google Map fields still require their own ACF/provider configuration.

## Migration boundaries

Migrate one configured section on staging first. Keep the original editor available until its fields, permissions and review paths have been compared. Existing custom capabilities are not automatically translated into broader WordPress rights. Do not leave duplicate editor/notification snippets active for the same workflow after a deliberate switchover.

The common workspace edits titles, summaries, descriptions and explicitly selected ACF fields. Beaver Builder layout data and unrendered metadata are preserved. WooCommerce prices, inventory, orders and product-type setup remain in native WooCommerce tools; new imported products are drafts for native configuration before publication. Supported ACF inputs include common text/number/choice/date/link/media/relationship/taxonomy/user/map fields and validated groups/repeaters/flexible layouts. Unknown plugin field types fail closed and need their native editor. Clone fields and specialised third-party controls are not converted automatically.

Specialised legacy grievance workflows, protected-gallery migrations, existing region-drawing/geocoding tools, account synchronisation, remote course synchronisation, certificates and project-specific page migrations are not silently replaced by the common modules. Preserve those integrations until their separate migration and data checks are complete. Other GRID websites have not been modified by this deployment.

The header/footer on access.grida.no is a Beaver Themer site layout, not forced onto websites installing this plugin. Its five current social links exclude X/Twitter.

## Workspace navigation and system pages

A WordPress menu item pointing to the configured workspace is automatically hidden from accounts without native permission to read that page and edit a configured section. Use a private workspace page to restrict access to editors and administrators. Descendant menu items are also removed. Excluded page IDs protect system pages from workspace listings and direct edit requests; the workspace page itself is always excluded. Authenticated editor responses are private and must not be cached as anonymous HTML.
