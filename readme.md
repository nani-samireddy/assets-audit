# Assets Audit

**Contributors:** yourname  
**Tags:** assets, scripts, styles, dependencies, duplicate assets, developer, debugging, WordPress  
**Requires at least:** 5.0  
**Tested up to:** 6.x  
**Requires PHP:** 7.0  
**Stable tag:** 2.2  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

## Description

**Assets Audit** is a developer-focused WordPress plugin that visualizes all enqueued scripts and styles on the current page, along with their dependencies and possible duplications. Instantly open a clean, interactive overlay from the admin toolbar – on any page – to inspect what assets are loaded, see dependency trees, and find duplicates with a quick click.

### Key Features

- View dependency trees for all scripts and styles on the current page.
- Instantly highlight and filter only duplicate assets that share the same source URL.
- Click any duplicate asset handle to see a panel with all handles using the same asset (per tree).
- Open the audit overlay from the admin toolbar on both frontend and backend pages.
- Minimal, distraction-free wireframe-inspired UI that keeps the focus on debugging.
- 100% accurate: page data is generated and localized for every context.
- Responsive design, with scrollable and accessible modal overlay.

## Installation

1. Upload the `script-style-mapper` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Log in as an administrator.
4. Use the **Assets Audit** button in the admin toolbar on any page to open the overlay and inspect assets.

## Frequently Asked Questions

### Who can use this plugin?

Developers and site administrators looking to debug or optimize WordPress asset loading.

### What does this plugin audit?

It visualizes all scripts and styles enqueued on the current page you are viewing. (Not a site-wide crawler.)

### Does this slow down my WordPress site?

No. Assets Audit only loads for logged-in administrators and does not affect public users.

## Screenshots

1. Admin toolbar button opening the overlay  
2. Overlay modal showing trees for scripts and styles  
3. Duplicate assets highlighted and filter checkbox enabled  
4. Slide-in panel inside cards listing all handles for a duplicate asset

## Changelog

### 1.0
- Initial release

## License

GPLv2 or later.  
See [GNU GPL v2](https://www.gnu.org/licenses/gpl-2.0.html) for details.


