# Private GitHub updates

Repository: https://github.com/grid-arendal/grid-accessibility-toolbar (private).

## How releases reach websites

1. Update and test the source. Keep the main PHP Version header, VERSION constant and readme Stable tag identical.
2. In GitHub Actions, run **Publish tested plugin release** and supply that version. The workflow checks PHP syntax and updater policy, builds a runtime-only ZIP, verifies its contents, computes SHA256 and publishes a private release at the exact source commit. Existing release names are not overwritten.
3. Configured websites check approximately every 12 hours, or an administrator chooses **Check for updates** on the plugin's Installed Plugins entry.
4. Install through WordPress's normal Updates interface. Automatic updates remain opt-in per website. First install the updater-enabled ZIP once on each existing site.

Only published stable x.y.z releases with a matching grid-accessibility-toolbar-x.y.z.zip asset are used. Drafts, prereleases, development branches and tags without releases are excluded. No source-archive fallback is allowed. Downgrades are not offered by the normal version comparison.

## One-time setup on each website

The GitHub connection used in this chat publishes source/releases. It does not grant your WordPress servers permission to download private assets.

The hosting administrator must configure a separate, repository-scoped, read-only GitHub credential. In GitHub, create a fine-grained personal access token restricted to **grid-arendal/grid-accessibility-toolbar** with **Contents: Read-only** and the required automatic Metadata read permission. Choose an expiry and arrange renewal. Prefer a distinct token per website so an individual site's access can be revoked without affecting the rest. Never use your publishing credential or a classic token with access to every repository.

Set the real value directly in the server environment as GRID_A11Y_GITHUB_TOKEN, or in wp-config.php before WordPress loads:

```php
define( 'GRID_A11Y_GITHUB_TOKEN', 'READ_ONLY_TOKEN_SET_DIRECTLY_ON_THIS_SERVER' );
```

The line above is an example, not a usable credential. Do not put real credentials in the plugin source, GitHub repository, ZIP, browser URL, screenshots or chat. The updater reads the server setting but does not save it to the plugin's WordPress option or display it. Full server configuration/backups still need appropriate access control. To disable this integration explicitly:

```php
define( 'GRID_A11Y_ENABLE_PRIVATE_UPDATES', false );
```

Visit **GRID-Arendal → Plugin updates** for setup status. A configured value is not proof that GitHub has accepted it: verify with **Check for updates** and a real private-release download. An expired/revoked credential, GitHub rate limit, blocked outbound connection or missing ZIP can prevent updates; the reading widget continues to work.

For long-lived organization-wide deployments, a GitHub App with short-lived installation tokens is a future alternative. This release supports a server-supplied token; it does not operate a token-broker service or refresh expiring personal tokens automatically.

## Privacy, downloads and rollback

The update integration contacts the fixed GitHub repository server-to-server for release metadata, source headers/readme and ZIP downloads. It sends no visitor reading preferences or page content. Credentials are scoped to repository API requests in headers; the pinned update library removes authentication on cross-host download redirects. Package URLs stored by the update checker do not contain credentials.

The GitHub repository must remain private. A private GitHub repository protects GitHub access; it does not add licensing or copy protection to a ZIP already installed on your servers. The plugin code remains GPL-compatible and bundled fonts retain their licenses.

Keep the last tested ZIP and a normal website backup. To roll back, upload that ZIP in WordPress and replace the installed version, then disable automatic updates while investigating. Never overwrite an existing published asset with different bytes under the same version. Publish a new version for a correction.

Updater library: unmodified Plugin Update Checker 6.0, MIT license, pinned to upstream tag v6.0. See vendor/plugin-update-checker/SOURCE.txt and license.txt.
