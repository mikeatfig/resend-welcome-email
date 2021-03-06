=== Resend Welcome Email ===
Contributors: adbox, ramiy, jazbek, afragen, titus, tbnv, mikeatfig
Donate link: hudson.atwell@gmail.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: users, welcome-email, user-management, support
Requires at least: 4.7.0
Tested up to: 5.6.1
Stable Tag: 1.2.0
Requires PHP: 5.3

Quickly send a new welcome email and password reset link for a user through the user's profile edit area.

== Description ==

This tool was developed to quickly send a user a new password reset link via email when they are having trouble logging in.

= Developers & Designers =

This extension has a public GitHub page where users can contribute fixes and improvements.

[Follow Development on GitHub](https://github.com/atwellpub/resend-welcome-email "Follow & Contribute to core development on GitHub")

[Follow Developer on Twitter](https://twitter.com/atwellpub "Follow the developer on Twitter")

= Contributors =

[Tibor Repček](https://github.com/tiborepcek/ "Tibor Repček on GitHub") - translation into slovak language (slovenčina)
[Thibaut Ninove](https://twitter.com/tbnv "Thibaut Ninove on Twitter") - French translation

== Installation ==

1. Upload `resend-welcome-email` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Do you provide support for this plugin =

Please open a issue on GitHub if you run into an issue. I am accepting pull requests too, so fire away.

== Changelog ==

= 1.2.0 =
* Added bulk action

= 1.1.9 =
* Adding fr_FR translations (shoutout to Thibaut Ninove)
* Fixing i18n support

= 1.1.8 =
* Improving labels
* Removing commented code

= 1.1.3 =
* Updating 'Tested up to' in readme.txt

= 1.1.2 =
* Adding language files sk_SK.po, sk_SK.mo

= 1.1.1 =
* Adding resend welcome email to user row action link.
* Converting edit_user to edit_users to fix soft error.

= 1.1.0 =
* Security: Escape translated strings.
* Refactor.
* Fix: Logic in notice.
* Add: Multisite compatibility.

= 1.0.3 =
* Security: Prevent direct access to php files.
* Security: Prevent direct access to directories.
* i18n: Use [translate.wordpress.org](https://translate.wordpress.org/) to translate the plugin to other languages.

= 1.0.2 =

* wp_new_user_notification() stopped sending passwords via email, and instead it sends a reset password link.

= 1.0.1 =

* Initial release.
