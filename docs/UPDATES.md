# Updates without website credentials

Repository: https://github.com/grid-arendal/grid-accessibility-toolbar

Install the current **grid-accessibility-toolbar-x.y.z.zip** release once on each website. It includes the updater. No GitHub account, access token, server credential or registration is needed by the websites. They retrieve a small public update feed from GitHub's CDN and download published release ZIPs. This avoids per-website configuration and GitHub API authentication/rate limits.

## Publish a new version

1. Update and test source. Keep the PHP Version header, VERSION constant and readme Stable tag equal.
2. In **GitHub → Actions → Publish tested plugin release → Run workflow**, enter that version.
3. The workflow checks PHP syntax and unit tests, validates/builds the runtime ZIP and publishes a stable release with SHA256.
4. Only after verifying the uploaded stable asset does it update `updates.json` on `main`. Ordinary source edits do not advertise an update. Never hand-edit the feed to point to untested code.
5. Websites check periodically (normally about 12 hours), or administrators choose **Check for updates** in Installed Plugins. Install through WordPress as usual, or enable WordPress's normal automatic updates per site.

The repository and release packages are public. Anyone can read the plugin code, but only authorized maintainers can publish changes. No website settings, visitor data, account passwords or credentials belong in this repository. The code is GPL-compatible; bundled fonts and GRID-Arendal brand assets retain their stated licenses/trademark restrictions.

## Scope and safeguards

The updater accepts only a stable x.y.z version with the exact release download URL belonging to this repository/version. It rejects branch/source archives, other repositories, malformed metadata, query strings and mismatched versions. GitHub's protected maintainer account and HTTPS protect distribution; the SHA256 file permits independent package verification. SHA256 is not a separate cryptographic signing service.

The updater contacts GitHub server-to-server for public metadata and packages. It sends no visitor reading preferences or page content and uses no GitHub credential. A blocked outbound request or GitHub outage can delay updates without disabling the widget. No remote update is an instantaneous push to every website; WordPress controls scheduling and installation.

To disable update checks, the hosting administrator can set `GRID_A11Y_ENABLE_UPDATES` to false in wp-config.php. Automatic installation is not forced. Keep normal site backups and test a release on staging before wider rollout. To roll back, install the previous known-good ZIP and temporarily disable automatic updates; publish a new corrective version rather than replacing an existing release asset.

Plugin Update Checker 6.0 is bundled unchanged under MIT. See vendor/plugin-update-checker/SOURCE.txt and license.txt.
