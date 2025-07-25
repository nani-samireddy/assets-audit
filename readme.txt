=== Assets Audit ===
Contributors: yourname
Tags: assets, scripts, styles, dependencies, duplicates, developer, debugging, WordPress
Requires at least: 5.0
Tested up to: 6.x
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Assets Audit visualizes all scripts and styles enqueued on the current page with a full dependency tree. Duplicate assets loaded under distinct handles are highlighted. Open an overlay from the admin toolbar on any frontend or backend page for instant interactive auditing.

== Installation ==

Upload the 'script-style-mapper' folder to the '/wp-content/plugins/' directory.

Activate the plugin via the 'Plugins' menu in WordPress.

Log in as an administrator.

Click 'Assets Audit' in the admin toolbar on any page.

== Frequently Asked Questions ==

= Who is this for? =
Primarily for developers and admins who need to audit or debug WordPress asset loading.

= Does this impact non-admin users? =
No – only loads for logged-in users with administrator rights.

= Does this audit all assets everywhere? =
No – it shows all assets loaded/enqueued on the current page context.

== Screenshots ==

Admin toolbar button

Overlay showing asset dependency trees

Duplicates highlighted and filtered

Duplicate asset handles in a card inside tree

== Changelog ==

= 1.0 =

Initial release as "Assets Audit"

== License ==
GNU General Public License version 2 or later

== Upgrade Notice ==
None

